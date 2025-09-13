<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\DynamicDatabaseHelper;
use App\Helpers\SubdomainHelper;
use App\Models\TenantDatabaseConfig;
use App\Models\Tenant;

class TestDynamicDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:dynamic-db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test dynamic database connection system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== اختبار نظام قاعدة البيانات الديناميكية ===');
        $this->newLine();

        // 1. اختبار SubdomainHelper
        $this->info('1. اختبار SubdomainHelper:');
        $testHost = 'test.example.com';
        $subdomain = SubdomainHelper::extractSubdomain($testHost);
        $this->line("   Host: $testHost");
        $this->line("   Subdomain: " . ($subdomain ?? 'null'));
        $this->newLine();

        // 2. اختبار TenantDatabaseConfig
        $this->info('2. اختبار TenantDatabaseConfig:');
        try {
            $configs = TenantDatabaseConfig::getActiveConfigs();
            $this->line("   عدد الإعدادات النشطة: " . $configs->count());
            
            if ($configs->count() > 0) {
                $config = $configs->first();
                $this->line("   أول إعداد:");
                $this->line("   - ID: " . $config->id);
                $this->line("   - Subdomain: " . $config->subdomain);
                $this->line("   - Database: " . $config->database_name);
                $this->line("   - Host: " . $config->host);
                $this->line("   - Driver: " . $config->driver);
            }
        } catch (\Exception $e) {
            $this->error("   خطأ: " . $e->getMessage());
        }

        $this->newLine();

        // 3. اختبار DynamicDatabaseHelper
        $this->info('3. اختبار DynamicDatabaseHelper:');
        try {
            $stats = DynamicDatabaseHelper::getConnectionStats();
            $this->line("   إحصائيات الاتصالات:");
            $this->line("   - إجمالي الإعدادات: " . $stats['total_configs']);
            $this->line("   - الإعدادات النشطة: " . $stats['active_configs']);
            $this->line("   - الإعدادات غير النشطة: " . $stats['inactive_configs']);
        } catch (\Exception $e) {
            $this->error("   خطأ: " . $e->getMessage());
        }

        $this->newLine();

        // 4. اختبار Tenant model
        $this->info('4. اختبار Tenant model:');
        try {
            $tenants = Tenant::with('databaseConfig')->get();
            $this->line("   عدد المستأجرين: " . $tenants->count());
            
            foreach ($tenants as $tenant) {
                $this->line("   - " . $tenant->name . " (ID: " . $tenant->id . ")");
                if ($tenant->databaseConfig) {
                    $this->line("     قاعدة البيانات: " . $tenant->databaseConfig->database_name);
                    $this->line("     Subdomain: " . $tenant->databaseConfig->subdomain);
                } else {
                    $this->line("     لا توجد إعدادات قاعدة بيانات");
                }
            }
        } catch (\Exception $e) {
            $this->error("   خطأ: " . $e->getMessage());
        }

        $this->newLine();
        $this->info('=== انتهى الاختبار ===');
        
        return 0;
    }
}
