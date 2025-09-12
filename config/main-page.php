<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Main Page Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the main page functionality.
    | It defines settings for tenant display, pagination, and caching.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Tenant Display Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for how tenants are displayed on the main page.
    |
    */
    'tenant_display' => [
        'recent_tenants_limit' => env('MAIN_RECENT_TENANTS_LIMIT', 10),
        'dashboard_pagination' => env('MAIN_DASHBOARD_PAGINATION', 10),
        'show_inactive_tenants' => env('MAIN_SHOW_INACTIVE_TENANTS', false),
        'show_suspended_tenants' => env('MAIN_SHOW_SUSPENDED_TENANTS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for caching main page data.
    |
    */
    'cache' => [
        'enabled' => env('MAIN_CACHE_ENABLED', true),
        'duration' => env('MAIN_CACHE_DURATION', 300), // 5 minutes
        'statistics_duration' => env('MAIN_STATS_CACHE_DURATION', 600), // 10 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Search and Filter Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for search and filtering functionality.
    |
    */
    'search' => [
        'min_search_length' => env('MAIN_MIN_SEARCH_LENGTH', 2),
        'max_results' => env('MAIN_MAX_SEARCH_RESULTS', 50),
        'enable_fuzzy_search' => env('MAIN_FUZZY_SEARCH', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for user interface elements.
    |
    */
    'ui' => [
        'show_tenant_avatars' => env('MAIN_SHOW_TENANT_AVATARS', true),
        'show_tenant_stats' => env('MAIN_SHOW_TENANT_STATS', true),
        'show_domain_count' => env('MAIN_SHOW_DOMAIN_COUNT', true),
        'enable_dark_mode' => env('MAIN_ENABLE_DARK_MODE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for security-related features.
    |
    */
    'security' => [
        'require_auth_for_admin' => env('MAIN_REQUIRE_AUTH_ADMIN', true),
        'rate_limit_admin_access' => env('MAIN_RATE_LIMIT_ADMIN', true),
        'log_admin_access' => env('MAIN_LOG_ADMIN_ACCESS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for performance optimization.
    |
    */
    'performance' => [
        'lazy_load_tenants' => env('MAIN_LAZY_LOAD_TENANTS', true),
        'preload_tenant_data' => env('MAIN_PRELOAD_TENANT_DATA', false),
        'optimize_queries' => env('MAIN_OPTIMIZE_QUERIES', true),
    ],
];
