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

    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        if (SubdomainHelper::isCentralDomain($host)) {
            return $next($request);
        }

        // نفس الطلب فقط (مكرر في web + route) — ليس بين طلبات PHP-FPM
        if ($request->attributes->get('dynamic_database_ready')) {
            return $next($request);
        }

        // ابحث عن التاجر دائماً من القاعدة المركزية
        $central = config('tenancy.database.central_connection', 'mysql');
        $previousDefault = config('database.default');
        config(['database.default' => $central]);

        try {
            $tenantData = SubdomainHelper::getTenantAndDatabaseConfigByDomain($host);
        } finally {
            config(['database.default' => $previousDefault]);
        }

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
            $this->dynamicConnectionName = DynamicDatabaseHelper::setConnection($tenantData['database_config']);
            DB::setDefaultConnection($this->dynamicConnectionName);
            Auth::forgetGuards();

            $request->attributes->set('dynamic_database_ready', true);
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
     * بعد حفظ الجلسة: أغلق الاتصال ونظّف الـ static حتى لا يتسرّب لتاجر آخر
     */
    public function terminate($request, $response): void
    {
        if ($this->cleanupQueued || !$this->dynamicConnectionName) {
            return;
        }

        $this->cleanupQueued = true;

        app()->terminating(function () {
            $this->cleanupTenantConnection();
        });
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
