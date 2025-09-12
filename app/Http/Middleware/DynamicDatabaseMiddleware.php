<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\DynamicDatabaseHelper;
use App\Helpers\SubdomainHelper;
use App\Models\TenantDatabaseConfig;
use Stancl\Tenancy\Tenancy;

class DynamicDatabaseMiddleware
{
    protected $tenancy;
    
    public function __construct(Tenancy $tenancy)
    {
        $this->tenancy = $tenancy;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        
        // التحقق من أن الدومين ليس من الدومينات المركزية
        if (SubdomainHelper::isCentralDomain($host)) {
            return $next($request);
        }
        
        // استخدام نظام الكاش المحسن للحصول على المستأجر وإعدادات قاعدة البيانات
        $tenantData = SubdomainHelper::getTenantAndDatabaseConfigByDomain($host);
        
        if ($tenantData && $tenantData['tenant'] && $tenantData['database_config']) {
            try {
                // تطبيق إعدادات قاعدة البيانات الديناميكية
                DynamicDatabaseHelper::setConnection($tenantData['database_config']);
                
                // تهيئة نظام المستأجرين
                $this->tenancy->initialize($tenantData['tenant']);
                
                // إضافة معلومات إضافية إلى الطلب
                $request->merge([
                    'current_tenant' => $tenantData['tenant'],
                    'current_database_config' => $tenantData['database_config'],
                    'current_subdomain' => $tenantData['subdomain'],
                    'dynamic_connection_active' => true,
                ]);
                
                // تسجيل العملية
                \Log::info('Dynamic Database Connection Established', [
                    'host' => $host,
                    'subdomain' => $tenantData['subdomain'],
                    'tenant_id' => $tenantData['tenant']->id,
                    'tenant_name' => $tenantData['tenant']->name,
                    'database_name' => $tenantData['database_config']->database_name,
                    'url' => $request->url(),
                    'method' => $request->method(),
                ]);
                
            } catch (\Exception $e) {
                \Log::error('Dynamic Database Connection Failed', [
                    'host' => $host,
                    'subdomain' => $tenantData['subdomain'] ?? 'unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                // في حالة فشل الاتصال، يمكن إما إرجاع خطأ أو المتابعة بدون اتصال ديناميكي
                return response()->json([
                    'error' => 'Database connection failed',
                    'message' => 'Unable to connect to tenant database',
                    'subdomain' => $tenantData['subdomain'] ?? 'unknown'
                ], 500);
            }
        } else {
            // لا يوجد مستأجر أو إعدادات قاعدة بيانات لهذا الدومين
            \Log::warning('No tenant or database config found for domain', [
                'host' => $host,
                'subdomain' => SubdomainHelper::extractSubdomain($host),
            ]);
            
            return response()->json([
                'error' => 'Tenant not found',
                'message' => 'No tenant or database configuration found for this domain',
                'host' => $host
            ], 404);
        }
        
        return $next($request);
    }
}