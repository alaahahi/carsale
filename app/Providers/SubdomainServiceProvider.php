<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\SubdomainHelper;
use Illuminate\Support\Facades\Cache;

class SubdomainServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register cache tags for tenant cache
        $this->app->singleton('tenant.cache', function ($app) {
            return Cache::store('redis')->tags(['tenant']);
        });

        // Register subdomain helper as singleton
        $this->app->singleton(SubdomainHelper::class, function ($app) {
            return new SubdomainHelper();
        });

        // Register tenant cache observer
        $this->registerTenantCacheObserver();
    }

    /**
     * Register tenant cache observer
     */
    protected function registerTenantCacheObserver(): void
    {
        // Clear cache when tenant is updated
        \App\Models\Tenant::updated(function ($tenant) {
            SubdomainHelper::clearTenantCache($tenant->id);
        });

        // Clear cache when tenant is deleted
        \App\Models\Tenant::deleted(function ($tenant) {
            SubdomainHelper::clearTenantCache($tenant->id);
        });

        // Clear cache when domain is updated
        \Stancl\Tenancy\Database\Models\Domain::updated(function ($domain) {
            SubdomainHelper::clearTenantCache($domain->tenant_id, $domain->domain);
        });

        // Clear cache when domain is deleted
        \Stancl\Tenancy\Database\Models\Domain::deleted(function ($domain) {
            SubdomainHelper::clearTenantCache($domain->tenant_id, $domain->domain);
        });
    }
}
