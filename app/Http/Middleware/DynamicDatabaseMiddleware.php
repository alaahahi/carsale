<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\DynamicDatabaseHelper;
use App\Helpers\SubdomainHelper;
use App\Helpers\TenantDataHelper;
use Inertia\Inertia;

class DynamicDatabaseMiddleware
{
    protected ?string $dynamicConnectionName = null;

    protected bool $cleanupQueued = false;

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

        if (SubdomainHelper::isCentralDomain($host)) {
            return $next($request);
        }

        // تجنّب تهيئة مزدوجة إن وُضع الميدلوير أكثر من مرة
        if (DynamicDatabaseHelper::getActiveConnectionName()) {
            return $next($request);
        }

        $tenantData = SubdomainHelper::getTenantAndDatabaseConfigByDomain($host);

        if ($tenantData && $tenantData['tenant'] && $tenantData['tenant']->isAccessBlocked()) {
            return $this->blockedTenantResponse($request, $tenantData['tenant']);
        }

        if (!($tenantData && $tenantData['tenant'] && $tenantData['database_config'])) {
            \Log::warning('No tenant or database config found for domain', [
                'host' => $host,
                'subdomain' => SubdomainHelper::extractSubdomain($host),
            ]);

            return response()->json([
                'error' => 'Tenant not found',
                'message' => 'No tenant or database configuration found for this domain',
                'host' => $host,
            ], 404);
        }

        try {
            // اتصال واحد فقط من TenantDatabaseConfig — بدون tenancy()->initialize()
            // لأن DatabaseTenancyBootstrapper يستبدل default بقاعدة Stancl (aindubai_*) ويكسر الجلسة/الدخول
            $this->dynamicConnectionName = DynamicDatabaseHelper::setConnection($tenantData['database_config']);
            DB::setDefaultConnection($this->dynamicConnectionName);

            Auth::forgetGuards();

            $request->merge([
                'current_tenant' => $tenantData['tenant'],
                'current_database_config' => $tenantData['database_config'],
                'current_subdomain' => $tenantData['subdomain'],
                'dynamic_connection_active' => true,
                'dynamic_connection_name' => $this->dynamicConnectionName,
            ]);

            try {
                Inertia::share('systemConfig', TenantDataHelper::getSystemConfig());
            } catch (\Throwable $e) {
                Inertia::share('systemConfig', TenantDataHelper::defaultSystemConfig());
            }

            \Log::info('Dynamic Database Connection Established', [
                'host' => $host,
                'subdomain' => $tenantData['subdomain'],
                'tenant_id' => $tenantData['tenant']->id,
                'tenant_name' => $tenantData['tenant']->name,
                'database_name' => $tenantData['database_config']->database_name,
                'connection_name' => $this->dynamicConnectionName,
                'url' => $request->url(),
                'method' => $request->method(),
            ]);

            return $next($request);
        } catch (\Exception $e) {
            \Log::error('Dynamic Database Connection Failed', [
                'host' => $host,
                'subdomain' => $tenantData['subdomain'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->cleanupTenantConnection();

            return response()->json([
                'error' => 'Database connection failed',
                'message' => 'Unable to connect to tenant database',
                'subdomain' => $tenantData['subdomain'] ?? 'unknown',
            ], 500);
        }
    }

    /**
     * لا نغلق الاتصال هنا — الإغلاق المبكر كان يساهم في فقدان الجلسة وحلقة التحويل.
     * PHP-FPM ينهي العملية بعد الطلب.
     */
    public function terminate($request, $response): void
    {
        // intentionally empty
    }

    private function cleanupTenantConnection(): void
    {
        $connectionName = $this->dynamicConnectionName
            ?: DynamicDatabaseHelper::getActiveConnectionName();

        if ($connectionName) {
            DynamicDatabaseHelper::releaseConnection($connectionName);
            $this->dynamicConnectionName = null;
        }
    }

    private function blockedTenantResponse(Request $request, $tenant)
    {
        $whatsapp = config('tenancy.developer_whatsapp', '+9647511077812');
        $whatsappDigits = preg_replace('/[^0-9]/', '', $whatsapp);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'subscription_blocked',
                'message' => $tenant->getAccessBlockMessage(),
                'reason' => $tenant->getAccessBlockReason(),
                'whatsapp' => $whatsapp,
            ], 403);
        }

        return response()->view('tenant.access-blocked', [
            'tenant' => $tenant,
            'reason' => $tenant->getAccessBlockReason(),
            'whatsapp' => $whatsapp,
            'whatsappLink' => 'https://wa.me/' . $whatsappDigits,
        ], 403);
    }
}
