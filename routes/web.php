<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\TransfersController;
use App\Http\Controllers\UserWalletController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\CustomerWalletController;
use App\Http\Controllers\SimpleCashController;
use App\Http\Controllers\SimpleAccountingController;

use App\Models\SystemConfig;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Main routes (accessible from main domain)
Route::get('/', [MainController::class, 'index'])->name('main.index');
Route::get('/select-tenant', [MainController::class, 'selectTenant'])->name('main.select-tenant');
Route::post('/redirect-to-tenant', [MainController::class, 'redirectToTenant'])->name('main.redirect-to-tenant');
Route::get('/tenant-info', [MainController::class, 'showTenantBySubdomain'])->name('main.tenant-info');
Route::get('/admin', [MainController::class, 'admin'])->name('main.admin');
Route::get('/dashboard', [MainController::class, 'dashboard'])->name('main.dashboard');

// Database info route (standalone)
Route::get('admin/tenants/database-info', function() {
    try {
        $info = [];
        
        // Central database info
        $centralConnection = DB::connection();
        $info['central'] = [
            'name' => $centralConnection->getDatabaseName(),
            'host' => $centralConnection->getConfig('host'),
            'port' => $centralConnection->getConfig('port'),
            'driver' => $centralConnection->getConfig('driver'),
            'connection_active' => true
        ];
        
        // Get all tenants and their database info
        $tenants = \App\Models\Tenant::with('domains')->get();
        $info['tenants'] = [];
        
        foreach ($tenants as $tenant) {
            try {
                // Initialize tenancy for this tenant
                tenancy()->initialize($tenant);
                
                $tenantConnection = DB::connection();
                $info['tenants'][] = [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'domains' => $tenant->domains->pluck('domain'),
                    'database' => [
                        'name' => $tenantConnection->getDatabaseName(),
                        'host' => $tenantConnection->getConfig('host'),
                        'port' => $tenantConnection->getConfig('port'),
                        'driver' => $tenantConnection->getConfig('driver'),
                        'connection_active' => true
                    ]
                ];
            } catch (\Exception $e) {
                $info['tenants'][] = [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'domains' => $tenant->domains->pluck('domain'),
                    'database' => [
                        'name' => 'car_tenant_' . $tenant->id,
                        'connection_active' => false,
                        'error' => $e->getMessage()
                    ]
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => $info
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'خطأ في الحصول على معلومات قاعدة البيانات: ' . $e->getMessage()
        ], 500);
    }
})->name('tenants.database-info');

// Direct admin access routes (without central middleware)
Route::group(['prefix' => 'admin'], function () {
    Route::get('/', function () {
        return redirect()->route('tenants.index');
    })->name('admin.index');
    
    Route::get('tenants', function() {
        $tenants = \App\Models\Tenant::with('domains')->paginate(10);
        return view('tenants.index', compact('tenants'));
    })->name('tenants.index');
    
    Route::get('tenants/create', function() {
        return view('tenants.create');
    })->name('tenants.create');
    
    Route::post('tenants', function(\Illuminate\Http\Request $request) {
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
    })->name('tenants.store');
    
    Route::get('tenants/{id}', function($id) {
        $tenant = \App\Models\Tenant::with(['domains', 'databaseConfigs'])->findOrFail($id);
        return view('tenants.show', compact('tenant'));
    })->name('tenants.show');
    
    Route::get('tenants/{id}/database-configs', [App\Http\Controllers\TenantController::class, 'databaseConfigs'])->name('tenants.database-configs');
    Route::get('tenants/{id}/database-configs/create', [App\Http\Controllers\TenantController::class, 'createDatabaseConfig'])->name('tenants.create-database-config');
    Route::post('tenants/{id}/database-configs', [App\Http\Controllers\TenantController::class, 'storeDatabaseConfig'])->name('tenants.store-database-config');
    
    Route::get('tenants/{id}/edit', function($id) {
        $tenant = \App\Models\Tenant::with('domains')->findOrFail($id);
        return view('tenants.edit', compact('tenant'));
    })->name('tenants.edit');
    
    Route::put('tenants/{id}', function(\Illuminate\Http\Request $request, $id) {
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
    })->name('tenants.update');
    
    Route::delete('tenants/{id}', function($id) {
        $tenant = \App\Models\Tenant::findOrFail($id);
        
        // Clear cache
        foreach ($tenant->domains as $domain) {
            \App\Helpers\SubdomainHelper::clearTenantCache($tenant->id, $domain->domain);
        }
        
        $tenant->delete();

        return redirect()->route('tenants.index')
            ->with('success', 'تم حذف المستأجر بنجاح');
    })->name('tenants.destroy');
    
    Route::post('tenants/{id}/suspend', function($id) {
        $tenant = \App\Models\Tenant::findOrFail($id);
        $tenant->update(['status' => 'suspended']);
        
        // Clear cache
        \App\Helpers\SubdomainHelper::clearTenantCache($tenant->id);
        
        return redirect()->route('tenants.index')
            ->with('success', 'تم تعليق المستأجر بنجاح');
    })->name('tenants.suspend');
    
    Route::post('tenants/{id}/activate', function($id) {
        $tenant = \App\Models\Tenant::findOrFail($id);
        $tenant->update(['status' => 'active']);
        
        // Clear cache
        \App\Helpers\SubdomainHelper::clearTenantCache($tenant->id);
        
        return redirect()->route('tenants.index')
            ->with('success', 'تم تفعيل المستأجر بنجاح');
    })->name('tenants.activate');
    
    Route::post('tenants/{id}/domains', function(\Illuminate\Http\Request $request, $id) {
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
    })->name('tenants.domains.add');
    
    Route::delete('tenants/{id}/domains/{domainId}', function($id, $domainId) {
        $tenant = \App\Models\Tenant::findOrFail($id);
        $domain = \Stancl\Tenancy\Database\Models\Domain::findOrFail($domainId);
        
        // Clear cache
        \App\Helpers\SubdomainHelper::clearTenantCache($tenant->id, $domain->domain);
        
        $domain->delete();

        return redirect()->route('tenants.show', $tenant->id)
            ->with('success', 'تم حذف الدومين بنجاح');
    })->name('tenants.domains.remove');
    
    Route::put('tenants/{id}/domains/{domainId}', function(\Illuminate\Http\Request $request, $id, $domainId) {
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
    })->name('tenants.domains.update');
    
    Route::post('tenants/{id}/clear-cache', function($id) {
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
    })->name('tenants.clear-cache');
    
    Route::post('tenants/clear-all-cache', function() {
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
    })->name('tenants.clear-all-cache');
    
    Route::get('tenant-database-configs', [App\Http\Controllers\TenantDatabaseConfigController::class, 'index'])->name('tenant-database-configs.index');
    Route::get('tenant-database-configs/create', [App\Http\Controllers\TenantDatabaseConfigController::class, 'create'])->name('tenant-database-configs.create');
    Route::post('tenant-database-configs', [App\Http\Controllers\TenantDatabaseConfigController::class, 'store'])->name('tenant-database-configs.store');
    Route::get('tenant-database-configs/{tenantDatabaseConfig}', [App\Http\Controllers\TenantDatabaseConfigController::class, 'show'])->name('tenant-database-configs.show');
    Route::get('tenant-database-configs/{tenantDatabaseConfig}/edit', [App\Http\Controllers\TenantDatabaseConfigController::class, 'edit'])->name('tenant-database-configs.edit');
    Route::put('tenant-database-configs/{tenantDatabaseConfig}', [App\Http\Controllers\TenantDatabaseConfigController::class, 'update'])->name('tenant-database-configs.update');
    Route::delete('tenant-database-configs/{tenantDatabaseConfig}', [App\Http\Controllers\TenantDatabaseConfigController::class, 'destroy'])->name('tenant-database-configs.destroy');
    Route::post('tenant-database-configs/{tenantDatabaseConfig}/test-connection', [App\Http\Controllers\TenantDatabaseConfigController::class, 'testConnection'])->name('tenant-database-configs.test-connection');
    Route::post('tenant-database-configs/{tenantDatabaseConfig}/check-tables', [App\Http\Controllers\TenantDatabaseConfigController::class, 'checkTables'])->name('tenant-database-configs.check-tables');
    Route::post('tenant-database-configs/{tenantDatabaseConfig}/check-admin', [App\Http\Controllers\TenantDatabaseConfigController::class, 'checkAdmin'])->name('tenant-database-configs.check-admin');
    Route::post('tenant-database-configs/{tenantDatabaseConfig}/run-migrations', [App\Http\Controllers\TenantDatabaseConfigController::class, 'runMigrations'])->name('tenant-database-configs.run-migrations');
    Route::post('tenant-database-configs/{tenantDatabaseConfig}/create-admin', [App\Http\Controllers\TenantDatabaseConfigController::class, 'createAdmin'])->name('tenant-database-configs.create-admin');
    Route::get('tenant-database-configs/by-subdomain', [App\Http\Controllers\TenantDatabaseConfigController::class, 'getBySubdomain'])->name('tenant-database-configs.by-subdomain');
    Route::post('tenant-database-configs/use-dynamic-connection', [App\Http\Controllers\TenantDatabaseConfigController::class, 'useDynamicConnection'])->name('tenant-database-configs.use-dynamic-connection');
    
    Route::get('tenants/{id}/check-database', function($id) {
        try {
            $tenant = \App\Models\Tenant::with(['domains', 'databaseConfig'])->findOrFail($id);
            
            // التحقق من وجود إعدادات قاعدة البيانات للمستأجر
            if (!$tenant->databaseConfig) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا توجد إعدادات قاعدة بيانات لهذا المستأجر'
                ], 404);
            }
            
            $dbConfig = $tenant->databaseConfig;
            
            // اختبار الاتصال بقاعدة البيانات المرتبطة بالمستأجر
            $connectionInfo = $dbConfig->getConnectionInfo();
            $connectionName = 'test_connection_' . $dbConfig->id;
            
            // إعداد اتصال مؤقت للاختبار
            config([
                "database.connections.{$connectionName}" => $connectionInfo
            ]);
            
            $connection = DB::connection($connectionName);
            $databaseName = $connection->getDatabaseName();
            $host = $connection->getConfig('host');
            $port = $connection->getConfig('port');
            
            // اختبار الاتصال
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
                    'driver' => $dbConfig->driver,
                    'connection_active' => true,
                    'tables_count' => count($tables),
                    'subdomain' => $dbConfig->subdomain,
                    'is_active' => $dbConfig->is_active
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في الاتصال بقاعدة البيانات: ' . $e->getMessage()
            ], 500);
        }
    })->name('tenants.check-database');
});

// Central routes (admin panel) - for subdomain access
Route::group(['middleware' => ['central'], 'prefix' => 'central-admin'], function () {
    Route::get('tenants', function() {
        $tenants = \App\Models\Tenant::with('domains')->paginate(10);
        return view('tenants.index', compact('tenants'));
    })->name('central.tenants.index');
    
    Route::get('tenants/create', function() {
        return view('tenants.create');
    })->name('central.tenants.create');
    
    Route::post('tenants', function(\Illuminate\Http\Request $request) {
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

        return redirect()->route('central.tenants.index')
            ->with('success', 'تم إنشاء المستأجر بنجاح');
    })->name('central.tenants.store');
    
    Route::get('tenants/{id}', function($id) {
        $tenant = \App\Models\Tenant::with(['domains', 'databaseConfigs'])->findOrFail($id);
        return view('tenants.show', compact('tenant'));
    })->name('central.tenants.show');
    
    Route::get('tenants/{id}/database-configs', [App\Http\Controllers\TenantController::class, 'databaseConfigs'])->name('central.tenants.database-configs');
    Route::get('tenants/{id}/database-configs/create', [App\Http\Controllers\TenantController::class, 'createDatabaseConfig'])->name('central.tenants.create-database-config');
    Route::post('tenants/{id}/database-configs', [App\Http\Controllers\TenantController::class, 'storeDatabaseConfig'])->name('central.tenants.store-database-config');
    
    Route::get('tenants/{id}/edit', function($id) {
        $tenant = \App\Models\Tenant::with('domains')->findOrFail($id);
        return view('tenants.edit', compact('tenant'));
    })->name('central.tenants.edit');
    
    Route::put('tenants/{id}', function(\Illuminate\Http\Request $request, $id) {
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

        return redirect()->route('central.tenants.index')
            ->with('success', 'تم تحديث المستأجر بنجاح');
    })->name('central.tenants.update');
    
    Route::delete('tenants/{id}', function($id) {
        $tenant = \App\Models\Tenant::findOrFail($id);
        
        // Clear cache
        foreach ($tenant->domains as $domain) {
            \App\Helpers\SubdomainHelper::clearTenantCache($tenant->id, $domain->domain);
        }
        
        $tenant->delete();

        return redirect()->route('central.tenants.index')
            ->with('success', 'تم حذف المستأجر بنجاح');
    })->name('central.tenants.destroy');
    
    Route::post('tenants/{id}/suspend', function($id) {
        $tenant = \App\Models\Tenant::findOrFail($id);
        $tenant->update(['status' => 'suspended']);
        
        // Clear cache
        \App\Helpers\SubdomainHelper::clearTenantCache($tenant->id);
        
        return redirect()->route('central.tenants.index')
            ->with('success', 'تم تعليق المستأجر بنجاح');
    })->name('central.tenants.suspend');
    
    Route::post('tenants/{id}/activate', function($id) {
        $tenant = \App\Models\Tenant::findOrFail($id);
        $tenant->update(['status' => 'active']);
        
        // Clear cache
        \App\Helpers\SubdomainHelper::clearTenantCache($tenant->id);
        
        return redirect()->route('central.tenants.index')
            ->with('success', 'تم تفعيل المستأجر بنجاح');
    })->name('central.tenants.activate');
    
    Route::post('tenants/{id}/domains', function(\Illuminate\Http\Request $request, $id) {
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

        return redirect()->route('central.tenants.show', $tenant->id)
            ->with('success', 'تم إضافة الدومين بنجاح');
    })->name('central.tenants.domains.add');
    
    Route::delete('tenants/{id}/domains/{domainId}', function($id, $domainId) {
        $tenant = \App\Models\Tenant::findOrFail($id);
        $domain = \Stancl\Tenancy\Database\Models\Domain::findOrFail($domainId);
        
        // Clear cache
        \App\Helpers\SubdomainHelper::clearTenantCache($tenant->id, $domain->domain);
        
        $domain->delete();

        return redirect()->route('central.tenants.show', $tenant->id)
            ->with('success', 'تم حذف الدومين بنجاح');
    })->name('central.tenants.domains.remove');
    
    Route::put('tenants/{id}/domains/{domainId}', function(\Illuminate\Http\Request $request, $id, $domainId) {
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

        return redirect()->route('central.tenants.show', $tenant->id)
            ->with('success', 'تم تحديث الدومين بنجاح');
    })->name('central.tenants.domains.update');
    
    Route::post('tenants/{id}/clear-cache', function($id) {
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
    })->name('central.tenants.clear-cache');
    
    Route::post('tenants/clear-all-cache', function() {
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
    })->name('central.tenants.clear-all-cache');
    
    Route::get('tenants/{id}/check-database', function($id) {
        try {
            $tenant = \App\Models\Tenant::with(['domains', 'databaseConfig'])->findOrFail($id);
            
            // التحقق من وجود إعدادات قاعدة البيانات للمستأجر
            if (!$tenant->databaseConfig) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا توجد إعدادات قاعدة بيانات لهذا المستأجر'
                ], 404);
            }
            
            $dbConfig = $tenant->databaseConfig;
            
            // اختبار الاتصال بقاعدة البيانات المرتبطة بالمستأجر
            $connectionInfo = $dbConfig->getConnectionInfo();
            $connectionName = 'test_connection_' . $dbConfig->id;
            
            // إعداد اتصال مؤقت للاختبار
            config([
                "database.connections.{$connectionName}" => $connectionInfo
            ]);
            
            $connection = DB::connection($connectionName);
            $databaseName = $connection->getDatabaseName();
            $host = $connection->getConfig('host');
            $port = $connection->getConfig('port');
            
            // اختبار الاتصال
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
                    'driver' => $dbConfig->driver,
                    'connection_active' => true,
                    'tables_count' => count($tables),
                    'subdomain' => $dbConfig->subdomain,
                    'is_active' => $dbConfig->is_active
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في الاتصال بقاعدة البيانات: ' . $e->getMessage()
            ], 500);
        }
    })->name('central.tenants.check-database');
    
    Route::get('tenant-database-configs', [App\Http\Controllers\TenantDatabaseConfigController::class, 'index'])->name('central.tenant-database-configs.index');
    Route::get('tenant-database-configs/create', [App\Http\Controllers\TenantDatabaseConfigController::class, 'create'])->name('central.tenant-database-configs.create');
    Route::post('tenant-database-configs', [App\Http\Controllers\TenantDatabaseConfigController::class, 'store'])->name('central.tenant-database-configs.store');
    Route::get('tenant-database-configs/{tenantDatabaseConfig}', [App\Http\Controllers\TenantDatabaseConfigController::class, 'show'])->name('central.tenant-database-configs.show');
    Route::get('tenant-database-configs/{tenantDatabaseConfig}/edit', [App\Http\Controllers\TenantDatabaseConfigController::class, 'edit'])->name('central.tenant-database-configs.edit');
    Route::put('tenant-database-configs/{tenantDatabaseConfig}', [App\Http\Controllers\TenantDatabaseConfigController::class, 'update'])->name('central.tenant-database-configs.update');
    Route::delete('tenant-database-configs/{tenantDatabaseConfig}', [App\Http\Controllers\TenantDatabaseConfigController::class, 'destroy'])->name('central.tenant-database-configs.destroy');
    Route::post('tenant-database-configs/{tenantDatabaseConfig}/test-connection', [App\Http\Controllers\TenantDatabaseConfigController::class, 'testConnection'])->name('central.tenant-database-configs.test-connection');
    Route::post('tenant-database-configs/{tenantDatabaseConfig}/check-tables', [App\Http\Controllers\TenantDatabaseConfigController::class, 'checkTables'])->name('central.tenant-database-configs.check-tables');
    Route::post('tenant-database-configs/{tenantDatabaseConfig}/check-admin', [App\Http\Controllers\TenantDatabaseConfigController::class, 'checkAdmin'])->name('central.tenant-database-configs.check-admin');
    Route::post('tenant-database-configs/{tenantDatabaseConfig}/run-migrations', [App\Http\Controllers\TenantDatabaseConfigController::class, 'runMigrations'])->name('central.tenant-database-configs.run-migrations');
    Route::post('tenant-database-configs/{tenantDatabaseConfig}/create-admin', [App\Http\Controllers\TenantDatabaseConfigController::class, 'createAdmin'])->name('central.tenant-database-configs.create-admin');
    Route::get('tenant-database-configs/by-subdomain', [App\Http\Controllers\TenantDatabaseConfigController::class, 'getBySubdomain'])->name('central.tenant-database-configs.by-subdomain');
    Route::post('tenant-database-configs/use-dynamic-connection', [App\Http\Controllers\TenantDatabaseConfigController::class, 'useDynamicConnection'])->name('central.tenant-database-configs.use-dynamic-connection');
});

// Tenant routes
Route::group(['middleware' => ['tenant']], function () {
    Route::resource('/users', UserController::class)->middleware(['auth', 'verified']);

    Route::get('/', function () {
        return Inertia::render('Welcome', [
            'config' => SystemConfig::first(),
            'canLogin' => Route::has('login'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    });

    Route::group(['middleware' => ['auth','verified']], function () {
    
    // استيراد السيارات من Excel
    Route::get('/import-cars', [InfoController::class, 'showUploadForm'])->name('car.import.form');
    Route::post('/import-cars', [InfoController::class, 'import'])->name('car.import');
    
    Route::get('dashboard',[DashboardController::class,'index'])->middleware(['auth', 'verified'])->name('dashboard');
    
    Route::get('getIndex',[UserController::class, 'getIndex'])->name("getIndex");
    Route::get('ban/{id}',[UserController::class, 'ban'])->name("ban");
    Route::get('clients',[UserController::class, 'clients'])->name('clients');
    Route::get('getIndexClients',[UserController::class, 'getIndexClients'])->name("getIndexClients");
    Route::get('addClients',[UserController::class, 'addClients'])->name('addClients');
    Route::post('clientsStore',[UserController::class, 'clientsStore'])->name('clientsStore');

    
    Route::get('unban/{id}',[UserController::class, 'unban'])->name("unban");
    Route::get('/userLocation/{id}',[UserController::class, 'userLocation'])->name("userLocation");
    
 

    
    Route::get('/getcount', [DashboardController::class, 'getcountComp'])->name('getcount');
    
    Route::get('/addUserCard/{card_id}/{card}/{user_id}', [UserController::class, 'addUserCard'])->name('addUserCard');
    
    Route::get('/receiveCard', [AccountingController::class, 'receiveCard'])->name('receiveCard');
    Route::get('/paySelse/{id}', [AccountingController::class, 'paySelse'])->name('paySelse');


    Route::get('addTransfers',[TransfersController::class, 'addTransfers'])->name('addTransfers');
    Route::get('transfers',[TransfersController::class, 'index'])->name('transfers');
    
    // Simple Pages Routes
Route::get('customer-wallet',[CustomerWalletController::class, 'index'])->name('customer-wallet');
Route::get('simple-cash',[SimpleCashController::class, 'index'])->name('simple-cash');
Route::get('simple-accounting',[SimpleAccountingController::class, 'index'])->name('simple-accounting');
    
    Route::get('getIndexAccountsSelas',[TransfersController::class, 'getIndexAccountsSelas'])->name('getIndexAccountsSelas');
    Route::get('getCarsNeedingInvestmentCompletion',[TransfersController::class, 'getCarsNeedingInvestmentCompletion'])->name('getCarsNeedingInvestmentCompletion');
 
    Route::get('addCar',[DashboardController::class, 'addCar'])->name('addCar');
    Route::get('payCar',[DashboardController::class, 'payCar'])->name('payCar');
    Route::get('getIndexCar',[DashboardController::class, 'getIndexCar'])->name('getIndexCar');
    Route::get('getIndexCarSearch',[DashboardController::class, 'getIndexCarSearch'])->name('getIndexCarSearch');

    Route::get('GenExpenses',[DashboardController::class, 'GenExpenses'])->name('GenExpenses');
    Route::get('addExpenses',[DashboardController::class, 'addExpenses'])->name('addExpenses');
    Route::get('addPaymentCar',[DashboardController::class, 'addPaymentCar'])->name('addPaymentCar');

    
    Route::get('user-wallet', [UserWalletController::class, 'index'])->name('user-wallet');
    Route::get('user-wallet/{userId}', [UserWalletController::class, 'show'])->name('user-wallet.show');
    Route::get('cars-needing-completion-investment', [UserWalletController::class, 'getCarsNeedingCompletionInvestment'])
        ->name('cars-needing-completion-investment');
   


    
     
    
    

    });
});

require __DIR__.'/auth.php';
