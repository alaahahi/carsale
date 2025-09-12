<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tenants = \App\Models\Tenant::with('domains')->paginate(10);
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
            $tenant = \App\Models\Tenant::create([
                'id' => \Illuminate\Support\Str::uuid(),
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
            \Stancl\Tenancy\Database\Models\Domain::create([
                'domain' => $request->domain,
                'tenant_id' => $tenant->id,
            ]);

            // Create tenant database using artisan command
            \Artisan::call('tenants:migrate', [
                '--tenants' => $tenant->id,
            ]);
            
            // Clear cache
            \App\Helpers\SubdomainHelper::clearTenantCache($tenant->id, $request->domain);
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
        $tenant = \App\Models\Tenant::with('domains')->findOrFail($id);
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
        $tenant = \App\Models\Tenant::with('domains')->findOrFail($id);
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
        $tenant = \App\Models\Tenant::findOrFail($id);
        
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
            if ($domain) {
                $oldDomain = $domain->domain;
                $domain->update(['domain' => $request->domain]);
                
                // Clear cache for old and new domains
                \App\Helpers\SubdomainHelper::clearTenantCache($tenant->id, $oldDomain);
                \App\Helpers\SubdomainHelper::clearTenantCache($tenant->id, $request->domain);
            }
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
        $tenant = \App\Models\Tenant::findOrFail($id);
        
        // Clear cache
        foreach ($tenant->domains as $domain) {
            \App\Helpers\SubdomainHelper::clearTenantCache($tenant->id, $domain->domain);
        }
        
        $tenant->delete();

        return redirect()->route('tenants.index')
            ->with('success', 'تم حذف المستأجر بنجاح');
    }

    /**
     * Suspend tenant
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function suspend($id)
    {
        $tenant = \App\Models\Tenant::findOrFail($id);
        $tenant->update(['status' => 'suspended']);
        
        // Clear cache
        \App\Helpers\SubdomainHelper::clearTenantCache($tenant->id);
        
        return redirect()->route('tenants.index')
            ->with('success', 'تم تعليق المستأجر بنجاح');
    }

    /**
     * Activate tenant
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function activate($id)
    {
        $tenant = \App\Models\Tenant::findOrFail($id);
        $tenant->update(['status' => 'active']);
        
        // Clear cache
        \App\Helpers\SubdomainHelper::clearTenantCache($tenant->id);
        
        return redirect()->route('tenants.index')
            ->with('success', 'تم تفعيل المستأجر بنجاح');
    }

    /**
     * Add domain to tenant
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function addDomain(Request $request, $id)
    {
        $tenant = \App\Models\Tenant::findOrFail($id);
        
        $request->validate([
            'domain' => 'required|string|max:255|unique:domains,domain',
        ]);

        \Stancl\Tenancy\Database\Models\Domain::create([
            'domain' => $request->domain,
            'tenant_id' => $tenant->id,
        ]);

        // Clear cache
        \App\Helpers\SubdomainHelper::clearTenantCache($tenant->id, $request->domain);

        return redirect()->route('tenants.show', $tenant->id)
            ->with('success', 'تم إضافة الدومين بنجاح');
    }

    /**
     * Remove domain from tenant
     *
     * @param  string  $id
     * @param  string  $domainId
     * @return \Illuminate\Http\Response
     */
    public function removeDomain($id, $domainId)
    {
        $tenant = \App\Models\Tenant::findOrFail($id);
        $domain = \Stancl\Tenancy\Database\Models\Domain::findOrFail($domainId);
        
        // Clear cache
        \App\Helpers\SubdomainHelper::clearTenantCache($tenant->id, $domain->domain);
        
        $domain->delete();

        return redirect()->route('tenants.show', $tenant->id)
            ->with('success', 'تم حذف الدومين بنجاح');
    }

    /**
     * Update domain
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @param  string  $domainId
     * @return \Illuminate\Http\Response
     */
    public function updateDomain(Request $request, $id, $domainId)
    {
        $tenant = \App\Models\Tenant::findOrFail($id);
        $domain = \Stancl\Tenancy\Database\Models\Domain::findOrFail($domainId);
        
        $request->validate([
            'domain' => 'required|string|max:255|unique:domains,domain,' . $domainId,
        ]);

        $oldDomain = $domain->domain;
        $domain->update(['domain' => $request->domain]);

        // Clear cache for old and new domains
        \App\Helpers\SubdomainHelper::clearTenantCache($tenant->id, $oldDomain);
        \App\Helpers\SubdomainHelper::clearTenantCache($tenant->id, $request->domain);

        return redirect()->route('tenants.show', $tenant->id)
            ->with('success', 'تم تحديث الدومين بنجاح');
    }

    /**
     * Clear tenant cache
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCache($id)
    {
        try {
            $tenant = \App\Models\Tenant::findOrFail($id);
            
            foreach ($tenant->domains as $domain) {
                \App\Helpers\SubdomainHelper::clearTenantCache($tenant->id, $domain->domain);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'تم مسح كاش المستأجر بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء مسح الكاش: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear all tenant cache
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearAllCache()
    {
        try {
            \App\Helpers\SubdomainHelper::clearAllTenantCache();
            
            return response()->json([
                'success' => true,
                'message' => 'تم مسح جميع الكاش بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء مسح الكاش: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check database connection for a specific tenant
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkDatabaseConnection($id)
    {
        try {
            $tenant = \App\Models\Tenant::findOrFail($id);
            
            // Initialize tenancy for this tenant
            tenancy()->initialize($tenant);
            
            // Get current database connection info
            $connection = DB::connection();
            $databaseName = $connection->getDatabaseName();
            $host = $connection->getConfig('host');
            $port = $connection->getConfig('port');
            
            // Test the connection
            $pdo = $connection->getPdo();
            $tables = $connection->select('SHOW TABLES');
            
            return response()->json([
                'success' => true,
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'domains' => $tenant->domains->pluck('domain')
                ],
                'database' => [
                    'name' => $databaseName,
                    'host' => $host,
                    'port' => $port,
                    'connection_active' => true,
                    'tables_count' => count($tables)
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في الاتصال بقاعدة البيانات: ' . $e->getMessage()
            ], 500);
        }
    }
}
