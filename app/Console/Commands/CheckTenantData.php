<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stancl\Tenancy\Database\Models\Domain;
use App\Models\Tenant;
use App\Models\TenantDatabaseConfig;

class CheckTenantData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:check-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check tenant data in database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== Checking Tenant Data ===");
        $this->newLine();

        // Check tenants
        $tenants = Tenant::all();
        $this->info("Tenants (" . $tenants->count() . "):");
        foreach ($tenants as $tenant) {
            $this->line("  - {$tenant->name} (ID: {$tenant->id})");
        }

        $this->newLine();

        // Check domains
        $domains = Domain::all();
        $this->info("Domains (" . $domains->count() . "):");
        foreach ($domains as $domain) {
            $this->line("  - {$domain->domain} (Tenant ID: {$domain->tenant_id})");
        }

        $this->newLine();

        // Check database configs
        $configs = TenantDatabaseConfig::all();
        $this->info("Database Configs (" . $configs->count() . "):");
        foreach ($configs as $config) {
            $this->line("  - {$config->subdomain} -> {$config->database_name} (Tenant ID: {$config->tenant_id})");
        }

        $this->newLine();
        $this->info("=== End Check ===");
        
        return 0;
    }
}
