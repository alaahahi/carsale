<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Helpers\SubdomainHelper;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tenants = Tenant::with('domains')->paginate(10);
        return view('tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('tenants.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:domains,domain',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'subscription_plan' => 'required|in:basic,premium,enterprise',
            'subscription_expires_at' => 'nullable|date|after:now',
        ]);

        DB::transaction(function () use ($request) {
            // Create tenant
            $tenant = Tenant::create([
                'id' => Str::uuid(),
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'subscription_plan' => $request->subscription_plan,
                'subscription_expires_at' => $request->subscription_expires_at,
                'status' => 'active',
                'settings' => [],
            ]);

            // Create domain
            Domain::create([
                'domain' => $request->domain,
                'tenant_id' => $tenant->id,
            ]);

            // Create tenant database using artisan command
            \Artisan::call('tenants:run', [
                '--tenants' => $tenant->id,
                '--' => 'migrate',
            ]);
            
            // Clear cache
            SubdomainHelper::clearTenantCache($tenant->id, $request->domain);
        });

        return redirect()->route('tenants.index')
            ->with('success', 'تم إنشاء المستأجر بنجاح');
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $tenant = Tenant::with('domains')->findOrFail($id);
        return view('tenants.show', compact('tenant'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $tenant = Tenant::with('domains')->findOrFail($id);
        return view('tenants.edit', compact('tenant'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:domains,domain,' . $tenant->domains->first()->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive,suspended',
            'subscription_plan' => 'required|in:basic,premium,enterprise',
            'subscription_expires_at' => 'nullable|date',
        ]);

        DB::transaction(function () use ($request, $tenant) {
            // Update tenant
            $tenant->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'status' => $request->status,
                'subscription_plan' => $request->subscription_plan,
                'subscription_expires_at' => $request->subscription_expires_at,
            ]);

            // Update domain
            $domain = $tenant->domains->first();
            $oldDomain = $domain->domain;
            $domain->update(['domain' => $request->domain]);
            
            // Clear cache
            SubdomainHelper::clearTenantCache($tenant->id, $oldDomain);
            SubdomainHelper::clearTenantCache($tenant->id, $request->domain);
        });

        return redirect()->route('tenants.index')
            ->with('success', 'تم تحديث المستأجر بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tenant = Tenant::findOrFail($id);
        
        DB::transaction(function () use ($tenant) {
            // Clear cache before deletion
            SubdomainHelper::clearTenantCache($tenant->id);
            
            // Delete tenant database - the database will be deleted automatically when tenant is deleted
            // No need for separate delete command
            
            // Delete tenant and its domains
            $tenant->delete();
        });

        return redirect()->route('tenants.index')
            ->with('success', 'تم حذف المستأجر بنجاح');
    }

    /**
     * Suspend tenant
     */
    public function suspend($id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update(['status' => 'suspended']);
        
        // Clear cache
        SubdomainHelper::clearTenantCache($tenant->id);
        
        return redirect()->route('tenants.index')
            ->with('success', 'تم تعليق المستأجر بنجاح');
    }

    /**
     * Activate tenant
     */
    public function activate($id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update(['status' => 'active']);
        
        // Clear cache
        SubdomainHelper::clearTenantCache($tenant->id);
        
        return redirect()->route('tenants.index')
            ->with('success', 'تم تفعيل المستأجر بنجاح');
    }
    
    /**
     * Add domain to tenant
     */
    public function addDomain(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);
        
        $request->validate([
            'domain' => 'required|string|max:255|unique:domains,domain',
        ]);
        
        $domain = Domain::create([
            'domain' => $request->domain,
            'tenant_id' => $tenant->id,
        ]);
        
        // Clear cache
        SubdomainHelper::clearTenantCache($tenant->id, $request->domain);
        
        return redirect()->route('tenants.show', $id)
            ->with('success', 'تم إضافة الدومين بنجاح');
    }
    
    /**
     * Remove domain from tenant
     */
    public function removeDomain($id, $domainId)
    {
        $tenant = Tenant::findOrFail($id);
        $domain = Domain::where('id', $domainId)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();
        
        $domainName = $domain->domain;
        $domain->delete();
        
        // Clear cache
        SubdomainHelper::clearTenantCache($tenant->id, $domainName);
        
        return redirect()->route('tenants.show', $id)
            ->with('success', 'تم حذف الدومين بنجاح');
    }
    
    /**
     * Update domain
     */
    public function updateDomain(Request $request, $id, $domainId)
    {
        $tenant = Tenant::findOrFail($id);
        $domain = Domain::where('id', $domainId)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();
        
        $request->validate([
            'domain' => 'required|string|max:255|unique:domains,domain,' . $domainId,
        ]);
        
        $oldDomain = $domain->domain;
        $domain->update(['domain' => $request->domain]);
        
        // Clear cache
        SubdomainHelper::clearTenantCache($tenant->id, $oldDomain);
        SubdomainHelper::clearTenantCache($tenant->id, $request->domain);
        
        return redirect()->route('tenants.show', $id)
            ->with('success', 'تم تحديث الدومين بنجاح');
    }
    
    /**
     * Clear tenant cache
     */
    public function clearCache($id)
    {
        $tenant = Tenant::findOrFail($id);
        SubdomainHelper::clearTenantCache($tenant->id);
        
        return redirect()->route('tenants.show', $id)
            ->with('success', 'تم مسح الكاش بنجاح');
    }
    
    /**
     * Get tenant by subdomain (API)
     */
    public function getBySubdomain(Request $request)
    {
        $subdomain = $request->input('subdomain');
        
        if (!$subdomain) {
            return response()->json(['error' => 'Subdomain is required'], 400);
        }
        
        $tenant = SubdomainHelper::getTenantBySubdomain($subdomain);
        
        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }
        
        return response()->json([
            'tenant' => $tenant,
            'domains' => $tenant->domains,
        ]);
    }
}

