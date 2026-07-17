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

    /** ترتيب الحذف: الأبناء أولاً ثم الآباء */
    private array $centralOnlyTables = [
        'domains',
        'tenant_database_configs',
        'tenants',
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

            // 1) احذف جداول مركزية تسربت لقاعدة التاجر (مع تعطيل FK)
            $this->dropPollutedCentralTables($db, $name);

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

    private function dropPollutedCentralTables($db, string $connectionName): void
    {
        $toDrop = [];
        foreach ($this->centralOnlyTables as $table) {
            if (Schema::connection($connectionName)->hasTable($table)) {
                $toDrop[] = $table;
            }
        }

        if ($toDrop === []) {
            $this->line('No polluted central tables.');
            return;
        }

        try {
            $db->statement('SET FOREIGN_KEY_CHECKS=0');

            // أزل أي FK من جداول التطبيق تشير إلى tenants (مثل users.tenant_id)
            foreach (['users', 'company', 'car', 'cars'] as $table) {
                if (!Schema::connection($connectionName)->hasTable($table)) {
                    continue;
                }
                $this->dropForeignKeysReferencing($db, $connectionName, $table, 'tenants');
            }

            foreach ($toDrop as $table) {
                Schema::connection($connectionName)->drop($table);
                $this->warn("Dropped polluted central table: {$table}");
            }
        } finally {
            $db->statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    private function dropForeignKeysReferencing($db, string $connectionName, string $table, string $refTable): void
    {
        $database = $db->getDatabaseName();
        $fks = $db->select(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND REFERENCED_TABLE_NAME = ?
               AND CONSTRAINT_NAME != ?',
            [$database, $table, $refTable, 'PRIMARY']
        );

        foreach ($fks as $fk) {
            $constraint = $fk->CONSTRAINT_NAME;
            try {
                $db->statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint}`");
                $this->line("Dropped FK {$table}.{$constraint}");
            } catch (\Throwable $e) {
                $this->warn("Could not drop FK {$constraint}: " . $e->getMessage());
            }
        }
    }
}
