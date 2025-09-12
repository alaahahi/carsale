<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class CentralMiddleware
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
        // Allow access from central domains to admin routes
        $host = $request->getHost();
        $centralDomains = config('tenancy.central_domains', []);
        
        if (in_array($host, $centralDomains)) {
            return $next($request);
        }
        
        // For non-central domains, prevent access
        $preventAccess = new PreventAccessFromCentralDomains();
        return $preventAccess->handle($request, $next);
    }
}

