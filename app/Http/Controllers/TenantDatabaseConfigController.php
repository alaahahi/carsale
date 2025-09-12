<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TenantDatabaseConfig;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

class TenantDatabaseConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $configs = TenantDatabaseConfig::with('tenant')->paginate(10);
        return view('tenant-database-configs.index', compact('configs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tenants = Tenant::all();
        return view('tenant-database-configs.create', compact('tenants'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tenant_id' => 'nullable|exists:tenants,id',
            'subdomain' => 'required|string|max:255|unique:tenant_database_configs,subdomain',
            'driver' => 'required|string|in:mysql,pgsql,sqlite',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'database_name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|max:255',
            'charset' => 'nullable|string|max:255',
            'collation' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $config = TenantDatabaseConfig::create($request->all());

        // اختبار الاتصال
        if ($config->testConnection()) {
            return redirect()->route('tenant-database-configs.index')
                ->with('success', 'تم إنشاء إعدادات قاعدة البيانات بنجاح وتم اختبار الاتصال');
        } else {
            return redirect()->route('tenant-database-configs.index')
                ->with('warning', 'تم إنشاء إعدادات قاعدة البيانات ولكن فشل اختبار الاتصال');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TenantDatabaseConfig $tenantDatabaseConfig)
    {
        $tenantDatabaseConfig->load('tenant');
        return view('tenant-database-configs.show', compact('tenantDatabaseConfig'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TenantDatabaseConfig $tenantDatabaseConfig)
    {
        $tenants = Tenant::all();
        return view('tenant-database-configs.edit', compact('tenantDatabaseConfig', 'tenants'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TenantDatabaseConfig $tenantDatabaseConfig)
    {
        $request->validate([
            'tenant_id' => 'nullable|exists:tenants,id',
            'subdomain' => 'required|string|max:255|unique:tenant_database_configs,subdomain,' . $tenantDatabaseConfig->id,
            'driver' => 'required|string|in:mysql,pgsql,sqlite',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'database_name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|max:255',
            'charset' => 'nullable|string|max:255',
            'collation' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $tenantDatabaseConfig->update($request->all());

        // اختبار الاتصال
        if ($tenantDatabaseConfig->testConnection()) {
            return redirect()->route('tenant-database-configs.index')
                ->with('success', 'تم تحديث إعدادات قاعدة البيانات بنجاح وتم اختبار الاتصال');
        } else {
            return redirect()->route('tenant-database-configs.index')
                ->with('warning', 'تم تحديث إعدادات قاعدة البيانات ولكن فشل اختبار الاتصال');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TenantDatabaseConfig $tenantDatabaseConfig)
    {
        $tenantDatabaseConfig->delete();
        return redirect()->route('tenant-database-configs.index')
            ->with('success', 'تم حذف إعدادات قاعدة البيانات بنجاح');
    }

    /**
     * اختبار الاتصال بقاعدة البيانات
     */
    public function testConnection(TenantDatabaseConfig $tenantDatabaseConfig)
    {
        try {
            \Log::info('Testing database connection', [
                'config_id' => $tenantDatabaseConfig->id,
                'subdomain' => $tenantDatabaseConfig->subdomain,
                'host' => $tenantDatabaseConfig->host,
                'database' => $tenantDatabaseConfig->database_name
            ]);
            
            $isConnected = $tenantDatabaseConfig->testConnection();
            
            if ($isConnected) {
                \Log::info('Database connection test successful', [
                    'config_id' => $tenantDatabaseConfig->id
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'تم الاتصال بقاعدة البيانات بنجاح'
                ]);
            } else {
                \Log::warning('Database connection test failed', [
                    'config_id' => $tenantDatabaseConfig->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'فشل الاتصال بقاعدة البيانات'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Database connection test error', [
                'config_id' => $tenantDatabaseConfig->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'خطأ في الاتصال: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على إعدادات قاعدة البيانات بالـ subdomain
     */
    public function getBySubdomain(Request $request)
    {
        $subdomain = $request->get('subdomain');
        
        if (!$subdomain) {
            return response()->json([
                'success' => false,
                'message' => 'الـ subdomain مطلوب'
            ], 400);
        }

        $config = TenantDatabaseConfig::findBySubdomain($subdomain);
        
        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على إعدادات قاعدة البيانات'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $config->getSafeConnectionInfo()
        ]);
    }

    /**
     * استخدام الاتصال الديناميكي
     */
    public function useDynamicConnection(Request $request)
    {
        $subdomain = $request->get('subdomain');
        
        if (!$subdomain) {
            return response()->json([
                'success' => false,
                'message' => 'الـ subdomain مطلوب'
            ], 400);
        }

        $config = TenantDatabaseConfig::findBySubdomain($subdomain);
        
        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على إعدادات قاعدة البيانات'
            ], 404);
        }

        try {
            $connectionInfo = $config->getConnectionInfo();
            $connectionName = 'dynamic_connection_' . $config->id;
            
            // تحديث إعدادات الاتصال
            config([
                "database.connections.{$connectionName}" => $connectionInfo
            ]);

            // استخدام الاتصال الجديد
            $data = DB::connection($connectionName)
                ->table('profile')
                ->join('results', 'profile.id', '=', 'results.profile_id')
                ->join('doctor_reslts', 'profile.id', '=', 'doctor_reslts.profile_id')
                ->where('profile.no', $request->get('profile_no'))
                ->select('profile.*', 'doctor_reslts.*', 'results.*')
                ->get();

            // تسجيل العملية
            $ipAddress = $request->ip();
            $method = $request->method();
            $uri = $request->path();
            $parameters = $request->all();

            Log::channel('apiprofile')->info('Dynamic Connection Request', [
                'subdomain' => $subdomain,
                'connection_name' => $connectionName,
                'ip_address' => $ipAddress,
                'method' => $method,
                'uri' => $uri,
                'parameters' => $parameters,
                'data_count' => $data->count(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $data,
                'connection_info' => $config->getSafeConnectionInfo()
            ]);

        } catch (\Exception $e) {
            Log::error('Dynamic Connection Error', [
                'subdomain' => $subdomain,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'خطأ في الاتصال بقاعدة البيانات: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * فحص الجداول في قاعدة البيانات
     */
    public function checkTables(TenantDatabaseConfig $tenantDatabaseConfig)
    {
        try {
            $connectionInfo = $tenantDatabaseConfig->getConnectionInfo();
            $connectionName = 'check_tables_' . $tenantDatabaseConfig->id;
            
            config([
                "database.connections.{$connectionName}" => $connectionInfo
            ]);
            
            $connection = DB::connection($connectionName);
            
            // فحص الجداول حسب نوع قاعدة البيانات
            if ($tenantDatabaseConfig->driver === 'mysql') {
                $tables = $connection->select('SHOW TABLES');
                $tableNames = array_map(function($table) {
                    return array_values((array)$table)[0];
                }, $tables);
            } elseif ($tenantDatabaseConfig->driver === 'pgsql') {
                $tables = $connection->select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
                $tableNames = array_map(function($table) {
                    return $table->tablename;
                }, $tables);
            } else {
                $tableNames = [];
            }
            
            // تسجيل الجداول الموجودة للتشخيص
            Log::info('Database Tables Check', [
                'config_id' => $tenantDatabaseConfig->id,
                'subdomain' => $tenantDatabaseConfig->subdomain,
                'database_name' => $tenantDatabaseConfig->database_name,
                'tables_found' => $tableNames,
                'tables_count' => count($tableNames)
            ]);
            
            return response()->json([
                'success' => true,
                'tables' => $tableNames,
                'count' => count($tableNames),
                'database_name' => $tenantDatabaseConfig->database_name
            ]);
            
        } catch (\Exception $e) {
            Log::error('Check Tables Error', [
                'config_id' => $tenantDatabaseConfig->id,
                'subdomain' => $tenantDatabaseConfig->subdomain,
                'database_name' => $tenantDatabaseConfig->database_name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'خطأ في فحص الجداول: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * فحص وجود مستخدم أدمن
     */
    public function checkAdmin(TenantDatabaseConfig $tenantDatabaseConfig)
    {
        try {
            $connectionInfo = $tenantDatabaseConfig->getConnectionInfo();
            $connectionName = 'check_admin_' . $tenantDatabaseConfig->id;
            
            config([
                "database.connections.{$connectionName}" => $connectionInfo
            ]);
            
            $connection = DB::connection($connectionName);
            
            // فحص وجود جدول المستخدمين أولاً
            $tables = $connection->select('SHOW TABLES');
            $tableNames = array_map(function($table) {
                return array_values((array)$table)[0];
            }, $tables);
            
            if (!in_array('users', $tableNames)) {
                return response()->json([
                    'success' => false,
                    'hasAdmin' => false,
                    'message' => 'جدول المستخدمين غير موجود'
                ]);
            }
            
            // فحص وجود أدمن
            $admin = $connection->table('users')
                ->where('email', 'admin@admin.com')
                ->first();
            
            if ($admin) {
                return response()->json([
                    'success' => true,
                    'hasAdmin' => true,
                    'adminEmail' => $admin->email,
                    'adminName' => $admin->name ?? 'مدير النظام'
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'hasAdmin' => false,
                    'message' => 'لا يوجد مستخدم أدمن'
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Check Admin Error', [
                'config_id' => $tenantDatabaseConfig->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'hasAdmin' => false,
                'message' => 'خطأ في فحص الأدمن: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تشغيل المايكريشن
     */
    public function runMigrations(Request $request, TenantDatabaseConfig $tenantDatabaseConfig)
    {
        try {
            $connectionInfo = $tenantDatabaseConfig->getConnectionInfo();
            $connectionName = 'migrations_' . $tenantDatabaseConfig->id;
            
            config([
                "database.connections.{$connectionName}" => $connectionInfo
            ]);
            
            $connection = DB::connection($connectionName);
            
            // تسجيل معلومات قاعدة البيانات قبل البدء
            Log::info('Starting Migrations', [
                'config_id' => $tenantDatabaseConfig->id,
                'subdomain' => $tenantDatabaseConfig->subdomain,
                'database_name' => $tenantDatabaseConfig->database_name,
                'connection_name' => $connectionName
            ]);
            
            // فحص وجود جدول migrations أولاً
            $tables = $connection->select('SHOW TABLES');
            $tableNames = array_map(function($table) {
                return array_values((array)$table)[0];
            }, $tables);
            
            Log::info('Tables found before migration', [
                'config_id' => $tenantDatabaseConfig->id,
                'tables' => $tableNames,
                'count' => count($tableNames)
            ]);
            
            $hasMigrationsTable = in_array('migrations', $tableNames);
            
            if (!$hasMigrationsTable) {
                // إنشاء جدول migrations إذا لم يكن موجوداً
                $connection->statement("
                    CREATE TABLE migrations (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        migration VARCHAR(255) NOT NULL,
                        batch INT NOT NULL
                    )
                ");
                
                Log::info('Created migrations table', [
                    'config_id' => $tenantDatabaseConfig->id,
                    'subdomain' => $tenantDatabaseConfig->subdomain
                ]);
            }
            
            // فحص الجداول الموجودة وإضافة سجلات وهمية في جدول migrations
            $existingTables = [];
            foreach ($tableNames as $tableName) {
                if ($tableName !== 'migrations') {
                    $existingTables[] = $tableName;
                }
            }
            
            // إضافة سجلات وهمية للجداول الموجودة
            if (!empty($existingTables)) {
                $batchNumber = $connection->table('migrations')->max('batch') + 1;
                if (!$batchNumber) $batchNumber = 1;
                
                foreach ($existingTables as $table) {
                    // فحص إذا كان السجل موجود بالفعل بطرق مختلفة
                    $exists = $connection->table('migrations')
                        ->where(function($query) use ($table) {
                            $query->where('migration', 'LIKE', "%{$table}%")
                                  ->orWhere('migration', 'LIKE', "%{$table}_table%")
                                  ->orWhere('migration', 'LIKE', "%create_{$table}%");
                        })
                        ->exists();
                    
                    if (!$exists) {
                        // إضافة السجل مع أسماء مختلفة محتملة
                        $possibleNames = [
                            "create_{$table}_table",
                            "{$table}_table",
                            "create_{$table}",
                            "{$table}"
                        ];
                        
                        foreach ($possibleNames as $migrationName) {
                            $connection->table('migrations')->insert([
                                'migration' => $migrationName,
                                'batch' => $batchNumber
                            ]);
                        }
                        
                        Log::info('Added existing table to migrations', [
                            'config_id' => $tenantDatabaseConfig->id,
                            'table' => $table,
                            'migration_names' => $possibleNames,
                            'batch' => $batchNumber
                        ]);
                    }
                }
                
                Log::info('Added existing tables to migrations', [
                    'config_id' => $tenantDatabaseConfig->id,
                    'tables' => $existingTables,
                    'batch' => $batchNumber
                ]);
            }
            
            // تشغيل المايكريشن باستخدام الاتصال المخصص
            Artisan::call('migrate', [
                '--database' => $connectionName,
                '--force' => true
            ]);
            
            $output = Artisan::output();
            
            // إدراج البيانات الافتراضية
            $adminEmail = $request->input('admin_email', 'admin@admin.com');
            $adminPassword = $request->input('admin_password', '123456789');
            $this->insertDefaultData($connection, $adminEmail, $adminPassword);
            
            Log::info('Migrations Run Successfully', [
                'config_id' => $tenantDatabaseConfig->id,
                'subdomain' => $tenantDatabaseConfig->subdomain,
                'output' => $output
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'تم تشغيل المايكريشن بنجاح وإدراج البيانات الافتراضية',
                'output' => $output,
                'existing_tables' => $existingTables
            ]);
            
        } catch (\Exception $e) {
            Log::error('Run Migrations Error', [
                'config_id' => $tenantDatabaseConfig->id,
                'subdomain' => $tenantDatabaseConfig->subdomain,
                'database_name' => $tenantDatabaseConfig->database_name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تشغيل المايكريشن: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إنشاء مستخدم أدمن
     */
    public function createAdmin(Request $request, TenantDatabaseConfig $tenantDatabaseConfig)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:6',
                'name' => 'required|string'
            ]);
            
            $connectionInfo = $tenantDatabaseConfig->getConnectionInfo();
            $connectionName = 'create_admin_' . $tenantDatabaseConfig->id;
            
            config([
                "database.connections.{$connectionName}" => $connectionInfo
            ]);
            
            $connection = DB::connection($connectionName);
            
            // تشفير كلمة المرور
            $hashedPassword = Hash::make($request->password);
            
            // إنشاء المستخدم
            $userId = $connection->table('users')->insertGetId([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $hashedPassword,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            Log::info('Admin User Created', [
                'config_id' => $tenantDatabaseConfig->id,
                'subdomain' => $tenantDatabaseConfig->subdomain,
                'user_id' => $userId,
                'email' => $request->email
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء مستخدم الأدمن بنجاح',
                'user_id' => $userId,
                'email' => $request->email
            ]);
            
        } catch (\Exception $e) {
            Log::error('Create Admin Error', [
                'config_id' => $tenantDatabaseConfig->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'خطأ في إنشاء مستخدم الأدمن: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إدراج البيانات الافتراضية
     */
    private function insertDefaultData($connection, $adminEmail = 'admin@admin.com', $adminPassword = '123456789')
    {
        try {
            // إدراج أنواع المستخدمين الافتراضية
            $userTypes = [
                ['id' => 1, 'name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 2, 'name' => 'account', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 3, 'name' => 'seles', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 4, 'name' => 'client', 'created_at' => now(), 'updated_at' => now()]
            ];

            foreach ($userTypes as $userType) {
                $exists = $connection->table('user_type')
                    ->where('id', $userType['id'])
                    ->orWhere('name', $userType['name'])
                    ->exists();
                
                if (!$exists) {
                    $connection->table('user_type')->insert($userType);
                }
            }

            // إنشاء المستخدم الافتراضي
            $adminExists = $connection->table('users')
                ->where('email', $adminEmail)
                ->exists();
            
            if (!$adminExists) {
                $connection->table('users')->insert([
                    'name' => 'Admin',
                    'email' => $adminEmail,
                    'password' => Hash::make($adminPassword),
                    'type_id' => 1, // admin
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // إنشاء المستخدمين الافتراضيين من نوع account
            $accountUsers = [
                [
                    'id' => 2,
                    'name' => 'الخزينة',
                    'email' => 'main@account.com',
                    'password' => '',
                    'type_id' => 2, // account
                    'is_band' => 0,
                    'created_at' => '2023-06-10 08:10:05',
                    'updated_at' => '2023-06-10 08:10:05'
                ],
                [
                    'id' => 3,
                    'name' => 'الدخل',
                    'email' => 'in@account.com',
                    'password' => '',
                    'type_id' => 2, // account
                    'is_band' => 0,
                    'created_at' => '2023-06-10 08:24:23',
                    'updated_at' => '2023-06-10 08:24:23'
                ],
                [
                    'id' => 4,
                    'name' => 'الخرج',
                    'email' => 'out@account.com',
                    'password' => '',
                    'type_id' => 2, // account
                    'is_band' => 0,
                    'created_at' => '2023-06-10 08:24:53',
                    'updated_at' => '2023-06-10 08:24:53'
                ],
                [
                    'id' => 5,
                    'name' => 'دين',
                    'email' => 'debt@account.com',
                    'password' => '',
                    'type_id' => 2, // account
                    'is_band' => 0,
                    'created_at' => '2023-06-10 08:29:06',
                    'updated_at' => '2023-06-10 08:29:06'
                ],
                [
                    'id' => 6,
                    'name' => 'الحولات',
                    'email' => 'transfers@account.com',
                    'password' => '',
                    'type_id' => 2, // account
                    'is_band' => 0,
                    'created_at' => '2023-06-10 08:31:27',
                    'updated_at' => '2023-06-10 08:31:27'
                ],
                [
                    'id' => 7,
                    'name' => 'مدفوعات المورد',
                    'email' => 'supplier-out@account.com',
                    'password' => '',
                    'type_id' => 2, // account
                    'is_band' => 0,
                    'created_at' => '2023-06-12 07:52:02',
                    'updated_at' => '2023-06-12 07:52:02'
                ],
                [
                    'id' => 8,
                    'name' => 'دين المورد',
                    'email' => 'supplier-debt@account.com',
                    'password' => '',
                    'type_id' => 2, // account
                    'is_band' => 0,
                    'created_at' => '2023-06-12 07:53:02',
                    'updated_at' => '2023-06-12 07:54:43'
                ]
            ];

            // إدراج المستخدمين الافتراضيين
            foreach ($accountUsers as $user) {
                $userExists = $connection->table('users')
                    ->where('id', $user['id'])
                    ->orWhere('email', $user['email'])
                    ->exists();
                
                if (!$userExists) {
                    $connection->table('users')->insert($user);
                }
            }

            // إنشاء محافظ لجميع المستخدمين
            $allUsers = $connection->table('users')->get();
            foreach ($allUsers as $user) {
                $walletExists = $connection->table('wallets')
                    ->where('user_id', $user->id)
                    ->exists();
                
                if (!$walletExists) {
                    $connection->table('wallets')->insert([
                        'user_id' => $user->id,
                        'balance' => 0.00,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            Log::info('Default data inserted successfully', [
                'user_types_count' => count($userTypes),
                'admin_user_created' => !$adminExists,
                'account_users_created' => count($accountUsers),
                'wallets_created' => count($allUsers),
                'admin_email' => $adminEmail
            ]);

        } catch (\Exception $e) {
            Log::error('Error inserting default data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}