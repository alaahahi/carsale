<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enable/Disable Tenant Cache
    |--------------------------------------------------------------------------
    |
    | Global switch to enable or disable tenant-specific caching. When disabled,
    | helper methods should bypass caching to avoid any cross-tenant mixing.
    |
    */
    'enabled' => env('TENANT_CACHE_ENABLED', false),
    /*
    |--------------------------------------------------------------------------
    | Tenant Cache Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for tenant-specific caching.
    | It defines cache settings, tags, and expiration times for tenant data.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Cache Store
    |--------------------------------------------------------------------------
    |
    | The cache store to use for tenant data. This should be a store that
    | supports tags for better cache management.
    |
    */
    'store' => env('TENANT_CACHE_STORE', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Cache Duration
    |--------------------------------------------------------------------------
    |
    | The default cache duration in seconds for tenant data.
    | Default is 1 hour (3600 seconds).
    |
    */
    'duration' => env('TENANT_CACHE_DURATION', 3600),

    /*
    |--------------------------------------------------------------------------
    | Cache Tags
    |--------------------------------------------------------------------------
    |
    | Cache tags used for tenant data. These tags help in clearing
    | related cache entries efficiently.
    |
    */
    'tags' => [
        'tenant' => 'tenant',
        'domain' => 'domain',
        'subdomain' => 'subdomain',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Keys
    |--------------------------------------------------------------------------
    |
    | Template for cache keys used for different types of tenant data.
    |
    */
    'keys' => [
        'tenant_by_id' => 'tenant_id_{id}',
        'tenant_by_domain' => 'tenant_domain_{domain}',
        'tenant_by_subdomain' => 'tenant_subdomain_{subdomain}',
        'all_tenants' => 'all_tenants',
        'all_domains' => 'all_tenant_domains',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Warming
    |--------------------------------------------------------------------------
    |
    | Configuration for cache warming strategies.
    |
    */
    'warming' => [
        'enabled' => env('TENANT_CACHE_WARMING', false),
        'batch_size' => env('TENANT_CACHE_WARMING_BATCH_SIZE', 10),
        'delay_between_batches' => env('TENANT_CACHE_WARMING_DELAY', 100), // milliseconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Invalidation
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic cache invalidation.
    |
    */
    'invalidation' => [
        'enabled' => env('TENANT_CACHE_INVALIDATION', true),
        'on_tenant_update' => true,
        'on_tenant_delete' => true,
        'on_domain_update' => true,
        'on_domain_delete' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Configuration for monitoring cache performance.
    |
    */
    'monitoring' => [
        'enabled' => env('TENANT_CACHE_MONITORING', false),
        'log_hits' => true,
        'log_misses' => true,
        'log_invalidations' => true,
    ],
];
