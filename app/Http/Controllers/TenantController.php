<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TenantController extends Controller
{
 
  
 
 
    public function index()
    {
        $tenants = \App\Models\Tenant::with(['domains', 'databaseConfig'])->paginate(10);
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
            // Database configuration validation
            'database_creation_method' => 'required|in:auto,manual',
            'db_subdomain' => 'required_if:database_creation_method,manual|string|max:255|unique:tenant_database_configs,subdomain',
            'db_driver' => 'required_if:database_creation_method,manual|in:mysql,pgsql,sqlite',
            'db_host' => 'required_if:database_creation_method,manual|string|max:255',
            'db_port' => 'required_if:database_creation_method,manual|integer|min:1|max:65535',
            'db_name' => 'required_if:database_creation_method,manual|string|max:255',
            'db_username' => 'required_if:database_creation_method,manual|string|max:255',
            'db_password' => 'required_if:database_creation_method,manual|string|max:255',
            'db_charset' => 'nullable|string|max:255',
            'db_collation' => 'nullable|string|max:255',
            'run_migrations' => 'boolean',
            'force_migrations' => 'boolean',
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

            // Handle database configuration (one-to-one relationship)
            $dbConfig = null;
            if ($request->database_creation_method === 'manual') {
                // Create custom database configuration
                $dbConfig = \App\Models\TenantDatabaseConfig::create([
                    'tenant_id' => $tenant->id,
                    'subdomain' => $request->db_subdomain,
                    'driver' => $request->db_driver,
                    'host' => $request->db_host,
                    'port' => $request->db_port,
                    'database_name' => $request->db_name,
                    'username' => $request->db_username,
                    'password' => $request->db_password,
                    'charset' => $request->db_charset ?? 'utf8mb4',
                    'collation' => $request->db_collation ?? 'utf8mb4_unicode_ci',
                    'is_active' => true,
                    'description' => "إعداد قاعدة البيانات للمستأجر: {$tenant->name}",
                ]);

                // Test connection
                if (!$dbConfig->testConnection()) {
                    throw new \Exception('فشل في اختبار الاتصال بقاعدة البيانات المخصصة');
                }

                // Create database with custom configuration
                $tenant->createDatabaseWithConfig($dbConfig);
            } else {
                // Use default tenancy configuration
                $tenant->createDatabase();
                
                // Create default database configuration
                $dbConfig = \App\Models\TenantDatabaseConfig::create([
                    'tenant_id' => $tenant->id,
                    'subdomain' => 'aindubai_' . $tenant->id,
                    'driver' => 'mysql',
                    'host' => config('database.connections.mysql.host'),
                    'port' => config('database.connections.mysql.port'),
                    'database_name' => 'aindubai_' . $tenant->id,
                    'username' => config('database.connections.mysql.username'),
                    'password' => config('database.connections.mysql.password'),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'is_active' => true,
                    'description' => "إعداد قاعدة البيانات الافتراضي للمستأجر: {$tenant->name}",
                ]);
            }

            // Run migrations if requested
            if ($request->run_migrations) {
                $force = $request->force_migrations ?? false;
                $tenant->runMigrationsWithConfig($dbConfig, $force);
            }
            
            // Clear cache
            \App\Helpers\SubdomainHelper::clearTenantCache($tenant->id, $request->domain);
        });

        return redirect()->route('tenants.index')
            ->with('success', 'تم إنشاء المستأجر بنجاح مع إعدادات قاعدة البيانات');
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $tenant = \App\Models\Tenant::with(['domains', 'databaseConfig'])->findOrFail($id);
        return view('tenants.show', compact('tenant'));
    }

    /**
     * Show tenant database configurations
     */
    public function databaseConfigs($id)
    {
        $tenant = \App\Models\Tenant::with(['domains', 'databaseConfigs'])->findOrFail($id);
        $configs = $tenant->databaseConfigs()->paginate(10);
        
        return view('tenants.database-configs', compact('tenant', 'configs'));
    }

    /**
     * Create database configuration for tenant
     */
    public function createDatabaseConfig($id)
    {
        $tenant = \App\Models\Tenant::findOrFail($id);
        return view('tenants.create-database-config', compact('tenant'));
    }

    /**
     * Store database configuration for tenant
     */
    public function storeDatabaseConfig(Request $request, $id)
    {
        $tenant = \App\Models\Tenant::with('domains')->findOrFail($id);
        
        // استخراج الـ subdomain تلقائياً من دومين المستأجر
        $subdomain = $this->extractSubdomainFromTenantDomains($tenant);
        
        if (!$subdomain) {
            return redirect()->back()
                ->withErrors(['subdomain' => 'لا يمكن استخراج subdomain من دومينات المستأجر. تأكد من وجود دومينات صحيحة.'])
                ->withInput();
        }
        
        $request->validate([
            'driver' => 'required|string|in:mysql,pgsql,sqlite',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'database_name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'charset' => 'nullable|string|max:255',
            'collation' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        // التحقق من عدم وجود إعدادات قاعدة بيانات أخرى للمستأجر
        $existingConfig = \App\Models\TenantDatabaseConfig::where('tenant_id', $tenant->id)->first();
        if ($existingConfig) {
            return redirect()->back()
                ->withErrors(['tenant' => 'يوجد بالفعل إعدادات قاعدة بيانات لهذا المستأجر. يمكنك تعديل الإعدادات الموجودة.'])
                ->withInput();
        }

        // التحقق من عدم وجود subdomain مكرر
        $existingSubdomain = \App\Models\TenantDatabaseConfig::where('subdomain', $subdomain)->first();
        if ($existingSubdomain) {
            return redirect()->back()
                ->withErrors(['subdomain' => 'هذا الـ subdomain مستخدم بالفعل من قبل مستأجر آخر.'])
                ->withInput();
        }

        $config = \App\Models\TenantDatabaseConfig::create([
            'tenant_id' => $tenant->id,
            'subdomain' => $subdomain,
            'driver' => $request->driver,
            'host' => $request->host,
            'port' => $request->port,
            'database_name' => $request->database_name,
            'username' => $request->username,
            'password' => $request->password,
            'charset' => $request->charset ?? 'utf8mb4',
            'collation' => $request->collation ?? 'utf8mb4_unicode_ci',
            'is_active' => $request->is_active ?? true,
            'description' => $request->description ?? "إعداد قاعدة البيانات للمستأجر: {$tenant->name}",
        ]);

        // مسح الكاش للمستأجر والدومينات
        foreach ($tenant->domains as $domain) {
            \App\Helpers\SubdomainHelper::clearTenantDatabaseConfigCache($tenant->id, $domain->domain, $subdomain);
        }

        // Test connection
        if ($config->testConnection()) {
            return redirect()->route('tenants.database-configs', $tenant->id)
                ->with('success', 'تم إنشاء إعدادات قاعدة البيانات بنجاح وتم اختبار الاتصال');
        } else {
            return redirect()->route('tenants.database-configs', $tenant->id)
                ->with('warning', 'تم إنشاء إعدادات قاعدة البيانات ولكن فشل اختبار الاتصال');
        }
    }
    
    /**
     * استخراج الـ subdomain من دومينات المستأجر
     */
    private function extractSubdomainFromTenantDomains($tenant)
    {
        foreach ($tenant->domains as $domain) {
            $parts = explode('.', $domain->domain);
            if (count($parts) > 2) {
                return $parts[0]; // الجزء الأول هو الـ subdomain
            }
        }
        return null;
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
            $tenant = \App\Models\Tenant::with('domains')->findOrFail($id);
            
            // مسح كاش المستأجر وإعدادات قاعدة البيانات
            foreach ($tenant->domains as $domain) {
                \App\Helpers\SubdomainHelper::clearTenantDatabaseConfigCache($tenant->id, $domain->domain);
            }
            
            // مسح كاش إعدادات قاعدة البيانات إذا كانت موجودة
            $dbConfig = \App\Models\TenantDatabaseConfig::where('tenant_id', $tenant->id)->first();
            if ($dbConfig) {
                \App\Helpers\SubdomainHelper::clearTenantDatabaseConfigCache($tenant->id, null, $dbConfig->subdomain);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'تم مسح كاش المستأجر وإعدادات قاعدة البيانات بنجاح'
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
            \App\Helpers\SubdomainHelper::clearAllTenantDatabaseConfigCache();
            
            return response()->json([
                'success' => true,
                'message' => 'تم مسح جميع كاش المستأجرين وإعدادات قاعدة البيانات بنجاح'
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
