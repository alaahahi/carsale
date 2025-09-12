<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;
use App\Helpers\SubdomainHelper;

class MainController extends Controller
{
    /**
     * Show the main page for customer management
     */
    public function index()
    {
        // Get all active tenants for display
        $limit = config('main-page.tenant_display.recent_tenants_limit', 10);
        $tenants = Tenant::where('status', 'active')
            ->with('domains')
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();

        // Get statistics
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'inactive_tenants' => Tenant::where('status', 'inactive')->count(),
            'suspended_tenants' => Tenant::where('status', 'suspended')->count(),
        ];

        return view('main.index', compact('tenants', 'stats'));
    }

    /**
     * Show tenant selection page
     */
    public function selectTenant()
    {
        $tenants = Tenant::where('status', 'active')
            ->with('domains')
            ->orderBy('name')
            ->get();

        return view('main.select-tenant', compact('tenants'));
    }

    /**
     * Redirect to tenant subdomain
     */
    public function redirectToTenant(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
        ]);

        $tenant = Tenant::with('domains')->findOrFail($request->tenant_id);
        $domain = $tenant->domains->first();

        if (!$domain) {
            return redirect()->back()->with('error', 'لا يوجد دومين لهذا المستأجر');
        }

        $url = SubdomainHelper::generateSubdomainUrl(
            SubdomainHelper::extractSubdomain($domain->domain),
            '/'
        );

        return redirect($url);
    }

    /**
     * Show tenant information by subdomain
     */
    public function showTenantBySubdomain(Request $request)
    {
        $subdomain = $request->input('subdomain');
        
        if (!$subdomain) {
            return redirect()->route('main.index')->with('error', 'يرجى تحديد الـ subdomain');
        }

        $tenant = SubdomainHelper::getTenantBySubdomain($subdomain);
        
        if (!$tenant) {
            return redirect()->route('main.index')->with('error', 'المستأجر غير موجود');
        }

        return view('main.tenant-info', compact('tenant'));
    }

    /**
     * Show admin panel
     */
    public function admin()
    {
        return redirect()->route('tenants.index');
    }

    /**
     * Show tenant management dashboard
     */
    public function dashboard()
    {
        $tenants = Tenant::with('domains')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'inactive_tenants' => Tenant::where('status', 'inactive')->count(),
            'suspended_tenants' => Tenant::where('status', 'suspended')->count(),
            'total_domains' => Domain::count(),
        ];

        return view('main.dashboard', compact('tenants', 'stats'));
    }
}
