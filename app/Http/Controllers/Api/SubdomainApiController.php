<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\SubdomainHelper;
use App\Models\Tenant;

class SubdomainApiController extends Controller
{
    /**
     * Get tenant by subdomain
     */
    public function getTenantBySubdomain(Request $request)
    {
        $subdomain = $request->input('subdomain');
        
        if (!$subdomain) {
            return response()->json([
                'success' => false,
                'message' => 'Subdomain is required'
            ], 400);
        }
        
        $tenant = SubdomainHelper::getTenantBySubdomain($subdomain);
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'tenant' => $tenant,
                'domains' => $tenant->domains,
                'is_active' => $tenant->isActive(),
                'has_valid_subscription' => $tenant->hasValidSubscription(),
            ]
        ]);
    }
    
    /**
     * Get tenant by domain
     */
    public function getTenantByDomain(Request $request)
    {
        $domain = $request->input('domain');
        
        if (!$domain) {
            return response()->json([
                'success' => false,
                'message' => 'Domain is required'
            ], 400);
        }
        
        $tenant = SubdomainHelper::getTenantByDomain($domain);
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'tenant' => $tenant,
                'domains' => $tenant->domains,
                'is_active' => $tenant->isActive(),
                'has_valid_subscription' => $tenant->hasValidSubscription(),
            ]
        ]);
    }
    
    /**
     * Generate subdomain URL
     */
    public function generateSubdomainUrl(Request $request)
    {
        $subdomain = $request->input('subdomain');
        $path = $request->input('path', '/');
        
        if (!$subdomain) {
            return response()->json([
                'success' => false,
                'message' => 'Subdomain is required'
            ], 400);
        }
        
        $url = SubdomainHelper::generateSubdomainUrl($subdomain, $path);
        
        return response()->json([
            'success' => true,
            'data' => [
                'url' => $url,
                'subdomain' => $subdomain,
                'path' => $path
            ]
        ]);
    }
    
    /**
     * Validate subdomain
     */
    public function validateSubdomain(Request $request)
    {
        $subdomain = $request->input('subdomain');
        
        if (!$subdomain) {
            return response()->json([
                'success' => false,
                'message' => 'Subdomain is required'
            ], 400);
        }
        
        $isValid = SubdomainHelper::validateSubdomain($subdomain);
        
        return response()->json([
            'success' => true,
            'data' => [
                'subdomain' => $subdomain,
                'is_valid' => $isValid
            ]
        ]);
    }
    
    /**
     * Get all tenant domains
     */
    public function getAllTenantDomains()
    {
        $domains = SubdomainHelper::getAllTenantDomains();
        
        return response()->json([
            'success' => true,
            'data' => $domains
        ]);
    }
    
    /**
     * Clear tenant cache
     */
    public function clearTenantCache(Request $request)
    {
        $tenantId = $request->input('tenant_id');
        $domain = $request->input('domain');
        
        if (!$tenantId && !$domain) {
            return response()->json([
                'success' => false,
                'message' => 'Either tenant_id or domain is required'
            ], 400);
        }
        
        SubdomainHelper::clearTenantCache($tenantId, $domain);
        
        return response()->json([
            'success' => true,
            'message' => 'Cache cleared successfully'
        ]);
    }
    
    /**
     * Clear all tenant cache
     */
    public function clearAllTenantCache()
    {
        SubdomainHelper::clearAllTenantCache();
        
        return response()->json([
            'success' => true,
            'message' => 'All tenant cache cleared successfully'
        ]);
    }
}
