<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Stancl\Tenancy\Database\Models\Domain;
use App\Models\Tenant;
use App\Models\TenantDatabaseConfig;

class SubdomainHelper
{
    /**
     * Cache duration in seconds (1 hour)
     */
    const CACHE_DURATION = 3600;
    
    /**
     * Get cache duration from config
     */
    public static function getCacheDuration()
    {
        return config('tenant-cache.duration', self::CACHE_DURATION);
    }
    
    /**
     * Get cache store from config
     */
    public static function getCacheStore()
    {
        return config('tenant-cache.store', 'default');
    }
    
    /**
     * Get tenant by subdomain with caching
     */
    public static function getTenantBySubdomain($subdomain)
    {
        $cacheKey = "tenant_subdomain_{$subdomain}";
        
        return Cache::store(self::getCacheStore())->remember($cacheKey, self::getCacheDuration(), function () use ($subdomain) {
            $domain = Domain::where('domain', $subdomain)->with('tenant')->first();
            return $domain ? $domain->tenant : null;
        });
    }
    
    /**
     * Get tenant by full domain with caching
     */
    public static function getTenantByDomain($domain)
    {
        $cacheKey = "tenant_domain_{$domain}";
        
        return Cache::store(self::getCacheStore())->remember($cacheKey, self::getCacheDuration(), function () use ($domain) {
            $domainModel = Domain::where('domain', $domain)->with('tenant')->first();
            return $domainModel ? $domainModel->tenant : null;
        });
    }
    
    /**
     * Extract subdomain from host
     */
    public static function extractSubdomain($host)
    {
        $parts = explode('.', $host);
        if (count($parts) > 2) {
            return $parts[0];
        }
        return null;
    }
    
    /**
     * Check if domain is central domain
     */
    public static function isCentralDomain($host)
    {
        $centralDomains = config('tenancy.central_domains', []);
        return in_array($host, $centralDomains);
    }
    
    /**
     * Clear tenant cache
     */
    public static function clearTenantCache($tenantId = null, $domain = null)
    {
        if ($tenantId) {
            Cache::store(self::getCacheStore())->forget("tenant_id_{$tenantId}");
        }
        
        if ($domain) {
            Cache::store(self::getCacheStore())->forget("tenant_domain_{$domain}");
            $subdomain = self::extractSubdomain($domain);
            if ($subdomain) {
                Cache::store(self::getCacheStore())->forget("tenant_subdomain_{$subdomain}");
            }
        }
    }
    
    /**
     * Generate subdomain URL
     */
    public static function generateSubdomainUrl($subdomain, $path = '/')
    {
        $baseDomain = config('app.url');
        $parsedUrl = parse_url($baseDomain);
        $host = $parsedUrl['host'] ?? 'localhost';
        
        // Replace www with subdomain or add subdomain
        if (strpos($host, 'www.') === 0) {
            $host = str_replace('www.', $subdomain . '.', $host);
        } else {
            $host = $subdomain . '.' . $host;
        }
        
        return ($parsedUrl['scheme'] ?? 'http') . '://' . $host . $path;
    }
    
    /**
     * Validate subdomain format
     */
    public static function validateSubdomain($subdomain)
    {
        // Subdomain should be 3-63 characters, alphanumeric and hyphens only
        return preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]$/', $subdomain);
    }
    
    /**
     * Get all tenant domains
     */
    public static function getAllTenantDomains()
    {
        $cacheKey = 'all_tenant_domains';
        
        return Cache::store(self::getCacheStore())->remember($cacheKey, self::getCacheDuration(), function () {
            return Domain::with('tenant')->get();
        });
    }
    
    /**
     * Clear all tenant cache
     */
    public static function clearAllTenantCache()
    {
        Cache::store(self::getCacheStore())->forget('all_tenant_domains');
        
        // Clear individual tenant caches
        $domains = Domain::all();
        foreach ($domains as $domain) {
            self::clearTenantCache(null, $domain->domain);
        }
    }
    
    /**
     * Get tenant and database config by domain with enhanced caching
     */
    public static function getTenantAndDatabaseConfigByDomain($host)
    {
        $cacheKey = "tenant_db_config_domain_{$host}";
        
        return Cache::store(self::getCacheStore())->remember($cacheKey, self::getCacheDuration(), function () use ($host) {
            // Extract subdomain from host
            $subdomain = self::extractSubdomain($host);
            
            if (!$subdomain) {
                return null;
            }
            
            // Get tenant by subdomain
            $tenant = self::getTenantBySubdomain($subdomain);
            
            if (!$tenant) {
                return null;
            }
            
            // Get database config for this tenant
            $dbConfig = TenantDatabaseConfig::where('tenant_id', $tenant->id)
                ->where('is_active', true)
                ->first();
            
            return [
                'tenant' => $tenant,
                'database_config' => $dbConfig,
                'subdomain' => $subdomain,
                'host' => $host
            ];
        });
    }
    
    /**
     * Get tenant and database config by subdomain with enhanced caching
     */
    public static function getTenantAndDatabaseConfigBySubdomain($subdomain)
    {
        $cacheKey = "tenant_db_config_subdomain_{$subdomain}";
        
        return Cache::store(self::getCacheStore())->remember($cacheKey, self::getCacheDuration(), function () use ($subdomain) {
            // Get tenant by subdomain
            $tenant = self::getTenantBySubdomain($subdomain);
            
            if (!$tenant) {
                return null;
            }
            
            // Get database config for this tenant
            $dbConfig = TenantDatabaseConfig::where('tenant_id', $tenant->id)
                ->where('is_active', true)
                ->first();
            
            return [
                'tenant' => $tenant,
                'database_config' => $dbConfig,
                'subdomain' => $subdomain
            ];
        });
    }
    
    /**
     * Clear tenant and database config cache
     */
    public static function clearTenantDatabaseConfigCache($tenantId = null, $domain = null, $subdomain = null)
    {
        if ($tenantId) {
            Cache::store(self::getCacheStore())->forget("tenant_id_{$tenantId}");
        }
        
        if ($domain) {
            Cache::store(self::getCacheStore())->forget("tenant_domain_{$domain}");
            Cache::store(self::getCacheStore())->forget("tenant_db_config_domain_{$domain}");
            
            $extractedSubdomain = self::extractSubdomain($domain);
            if ($extractedSubdomain) {
                Cache::store(self::getCacheStore())->forget("tenant_subdomain_{$extractedSubdomain}");
                Cache::store(self::getCacheStore())->forget("tenant_db_config_subdomain_{$extractedSubdomain}");
            }
        }
        
        if ($subdomain) {
            Cache::store(self::getCacheStore())->forget("tenant_subdomain_{$subdomain}");
            Cache::store(self::getCacheStore())->forget("tenant_db_config_subdomain_{$subdomain}");
        }
    }
    
    /**
     * Clear all tenant and database config cache
     */
    public static function clearAllTenantDatabaseConfigCache()
    {
        Cache::store(self::getCacheStore())->forget('all_tenant_domains');
        
        // Clear individual tenant and database config caches
        $domains = Domain::all();
        foreach ($domains as $domain) {
            self::clearTenantDatabaseConfigCache(null, $domain->domain);
        }
        
        // Clear subdomain caches
        $dbConfigs = TenantDatabaseConfig::all();
        foreach ($dbConfigs as $config) {
            self::clearTenantDatabaseConfigCache(null, null, $config->subdomain);
        }
    }
    
    /**
     * Check if domain has valid tenant and database config
     */
    public static function isValidTenantDomain($host)
    {
        $data = self::getTenantAndDatabaseConfigByDomain($host);
        return $data && $data['tenant'] && $data['database_config'];
    }
    
    /**
     * Get database connection info for domain
     */
    public static function getDatabaseConnectionInfo($host)
    {
        $data = self::getTenantAndDatabaseConfigByDomain($host);
        
        if (!$data || !$data['database_config']) {
            return null;
        }
        
        return $data['database_config']->getConnectionInfo();
    }
}
