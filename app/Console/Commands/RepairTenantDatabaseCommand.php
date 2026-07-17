<?php

namespace App\Console\Commands;

use App\Helpers\EnsureTenantAdmin;
use App\Models\TenantDatabaseConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RepairTenantDatabaseCommand extends Command
{
    protected $signature = 'tenants:repair-db
        {domain? : subdomain مثل sarwan}
        {--all : إصلاح كل التجار}
        {--migrate : تشغيل المايغريشن الناقصة}
        {--fix-admin : إعادة ضبط admin@admin.com}';

    protected $description = 'إصلاح قاعدة التاجر: حذف جداول مركزية بالخطأ + جداول ناقصة + أدمن الدخول';

    /** جداول مركزية لا يجوز وجودها في قاعدة التاجر */
    private array $centralOnlyTables = [
        'tenants',
        'domains',
        'tenant_database_configs',
    ];

    /** جداول التطبيق المتوقعة (مقارنة مع kamo الشغال) */
    private array $expectedTables = [
        'users',
        'user_type',
        'wallets',
        'system_config',
        'car',
        'car_field_histories',
        'car_model',
        'color',
        'company',
        'name',
        'expenses',
        'expenses_type',
        'transactions',
        'transfers',
        'investments',
        'investment_cars',
        'supplier_payments',
        'migrations',
    ];

    public function handle(): int
    {
        $configs = $this->option('all')
            ? TenantDatabaseConfig::query()->where('is_active', true)->get()
            : collect([$this->resolveConfig($this->argument('domain'))])->filter();

        if ($configs->isEmpty()) {
            $this->error('حدد domain أو --all');
            return self::FAILURE;
        }

        foreach ($configs as $config) {
            $this->repairOne($config);
        }

        return self::SUCCESS;
    }

    private function resolveConfig(?string $domain): ?TenantDatabaseConfig
    {
        if (!$domain) {
            return null;
        }

        $sub = explode('.', $domain)[0];

        return TenantDatabaseConfig::query()
            ->where('subdomain', $domain)
            ->orWhere('subdomain', $sub)
            ->first();
    }

    private function repairOne(TenantDatabaseConfig $config): void
    {
        $this->newLine();
        $this->info("=== {$config->subdomain} / {$config->database_name} ===");

        $name = 'repair_' . $config->id;
        config(["database.connections.{$name}" => $config->getConnectionInfo()]);

        try {
            $db = DB::connection($name);
            $db->getPdo();

            // 1) احذف جداول مركزية تسربت لقاعدة التاجر (سبب اختلاف sarwan عن kamo)
            foreach ($this->centralOnlyTables as $table) {
                if (Schema::connection($name)->hasTable($table)) {
                    Schema::connection($name)->drop($table);
                    $this->warn("Dropped polluted central table: {$table}");
                }
            }

            // 2) جداول ناقصة
            $missing = [];
            foreach ($this->expectedTables as $table) {
                if (!Schema::connection($name)->hasTable($table)) {
                    $missing[] = $table;
                }
            }

            if ($missing) {
                $this->warn('Missing tables: ' . implode(', ', $missing));
            } else {
                $this->line('All expected tables present.');
            }

            // 3) مايغريشن
            if ($this->option('migrate') && $missing) {
                $previous = config('database.default');
                config(['database.default' => $name]);
                try {
                    Artisan::call('migrate', [
                        '--database' => $name,
                        '--force' => true,
                        '--path' => 'database/migrations',
                    ]);
                    $this->line(Artisan::output());
                } finally {
                    config(['database.default' => $previous]);
                }
            }

            // 4) أدمن الدخول
            $result = EnsureTenantAdmin::fix($config);
            $this->line(($result['ok'] ? '[OK] ' : '[FAIL] ') . $result['message']
                . " — {$result['email']} / " . EnsureTenantAdmin::defaultPassword());
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        } finally {
            try {
                DB::disconnect($name);
                DB::purge($name);
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }
}
