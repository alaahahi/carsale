<?php

namespace App\Http\Middleware;

use App\Helpers\SubdomainHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * قبل StartSession: كوكي جلسة مستقل لكل تاجر (host-only)
 * يمنع تضارب laravel_session بين sarwan / kamo / ... وفقدان تسجيل الدخول
 */
class TenantSessionCookie
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        // دائماً host-only — لا تشارك الجلسة بين السب دومينات
        config([
            'session.domain' => null,
            'session.same_site' => 'lax',
            'session.secure' => $request->isSecure() || str_starts_with($request->getScheme(), 'https'),
            'session.http_only' => true,
        ]);

        if (!SubdomainHelper::isCentralDomain($host)) {
            $subdomain = SubdomainHelper::extractSubdomain($host) ?: Str::slug($host, '_');
            $cookie = 'sess_' . Str::slug($subdomain, '_');

            config([
                'session.cookie' => $cookie,
            ]);
        }

        return $next($request);
    }
}
