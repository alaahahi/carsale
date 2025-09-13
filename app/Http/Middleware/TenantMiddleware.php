<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Stancl\Tenancy\Facades\Tenancy;
use Illuminate\Support\Facades\Cache;
use Stancl\Tenancy\Database\Models\Domain;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Extract subdomain from request
        $host = $request->getHost();
        $subdomain = $this->extractSubdomain($host);
        
        // Check if this is a central domain
        $centralDomains = config('tenancy.central_domains', []);
        if (in_array($host, $centralDomains)) {
            return $next($request);
        }
        
        // Check cache for tenant info
        $cacheKey = "tenant_domain_{$host}";
        $tenantInfo = Cache::remember($cacheKey, 3600, function () use ($host) {
            return $this->getTenantByDomain($host);
        });
        
        if (!$tenantInfo) {
            abort(404, 'Tenant not found');
        }
        
        // Initialize tenancy using the middleware directly
        return app(InitializeTenancyByDomain::class)->handle($request, $next);
    }
    
    /**
     * Extract subdomain from host
     */
    private function extractSubdomain($host)
    {
        $parts = explode('.', $host);
        if (count($parts) > 2) {
            return $parts[0];
        }
        return null;
    }
    
    /**
     * Get tenant by domain with caching
     */
    private function getTenantByDomain($domain)
    {
        return Domain::where('domain', $domain)->with('tenant')->first();
    }
}

