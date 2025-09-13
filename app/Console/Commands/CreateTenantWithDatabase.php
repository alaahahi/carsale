<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\TenantDatabaseConfig;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Support\Str;

class CreateTenantWithDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create-with-db 
                            {domain : The domain for the tenant}
                            {name : The tenant name}
                            {--subdomain= : The subdomain (extracted from domain if not provided)}
                            {--db-host= : Database host}
                            {--db-port=3306 : Database port}
                            {--db-name= : Database name}
                            {--db-user= : Database username}
                            {--db-password= : Database password}
                            {--db-driver=mysql : Database driver}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a tenant with custom database configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $domain = $this->argument('domain');
        $name = $this->argument('name');
        
        // Extract subdomain if not provided
        $subdomain = $this->option('subdomain');
        if (!$subdomain) {
            $parts = explode('.', $domain);
            $subdomain = count($parts) > 2 ? $parts[0] : 'default';
        }

        $this->info("Creating tenant for domain: $domain");
        $this->info("Subdomain: $subdomain");
        $this->info("Name: $name");

        // Database configuration
        $dbHost = $this->option('db-host') ?: 'localhost';
        $dbPort = $this->option('db-port') ?: 3306;
        $dbName = $this->option('db-name') ?: 'car_tenant_' . Str::slug($subdomain);
        $dbUser = $this->option('db-user') ?: 'root';
        $dbPassword = $this->option('db-password') ?: '';
        $dbDriver = $this->option('db-driver') ?: 'mysql';

        $this->info("Database: $dbName@$dbHost:$dbPort");

        try {
            \DB::transaction(function () use ($domain, $name, $subdomain, $dbHost, $dbPort, $dbName, $dbUser, $dbPassword, $dbDriver) {
                // Create tenant
                $tenant = Tenant::create([
                    'id' => Str::uuid(),
                    'name' => $name,
                    'email' => null,
                    'phone' => null,
                    'address' => null,
                    'subscription_plan' => 'basic',
                    'subscription_expires_at' => now()->addYear(),
                    'status' => 'active',
                    'settings' => [],
                ]);

                $this->info("✓ Tenant created with ID: " . $tenant->id);

                // Create domain
                Domain::create([
                    'domain' => $domain,
                    'tenant_id' => $tenant->id,
                ]);

                $this->info("✓ Domain created: $domain");

                // Create database configuration
                $dbConfig = TenantDatabaseConfig::create([
                    'tenant_id' => $tenant->id,
                    'subdomain' => $subdomain,
                    'driver' => $dbDriver,
                    'host' => $dbHost,
                    'port' => $dbPort,
                    'database_name' => $dbName,
                    'username' => $dbUser,
                    'password' => $dbPassword,
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'is_active' => true,
                    'description' => "Database config for $name",
                ]);

                $this->info("✓ Database configuration created with ID: " . $dbConfig->id);

                // Test database connection
                if ($dbConfig->testConnection()) {
                    $this->info("✓ Database connection test successful");
                } else {
                    $this->warn("⚠ Database connection test failed - please check your database settings");
                }
            });

            $this->info("\n🎉 Tenant created successfully!");
            $this->info("You can now access: http://$domain");

        } catch (\Exception $e) {
            $this->error("❌ Error creating tenant: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
