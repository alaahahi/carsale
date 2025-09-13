<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TenantDatabaseConfig;
use Illuminate\Support\Facades\DB;

class CreateTenantDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create-db {config_id : The database config ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create database for tenant configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $configId = $this->argument('config_id');
        
        $config = TenantDatabaseConfig::find($configId);
        
        if (!$config) {
            $this->error("Database configuration not found with ID: $configId");
            return 1;
        }

        $this->info("Creating database for config ID: $configId");
        $this->info("Database: {$config->database_name}");
        $this->info("Host: {$config->host}:{$config->port}");

        try {
            // Create connection info for database creation
            $connectionInfo = $config->getConnectionInfo();
            $connectionName = 'create_db_' . $config->id;
            
            // Use a connection without database name to create the database
            $createConnectionInfo = $connectionInfo;
            $createConnectionInfo['database'] = null;
            
            config([
                "database.connections.{$connectionName}" => $createConnectionInfo
            ]);

            $connection = DB::connection($connectionName);
            
            // Create database
            $connection->statement("CREATE DATABASE IF NOT EXISTS `{$config->database_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            $this->info("✓ Database '{$config->database_name}' created successfully");

            // Test connection to the new database
            $testConnectionInfo = $connectionInfo;
            $testConnectionName = 'test_db_' . $config->id;
            
            config([
                "database.connections.{$testConnectionName}" => $testConnectionInfo
            ]);

            $testConnection = DB::connection($testConnectionName);
            $testConnection->getPdo();
            
            $this->info("✓ Database connection test successful");

            // Run migrations if they exist
            try {
                $this->info("Running migrations...");
                \Artisan::call('migrate', [
                    '--database' => $testConnectionName,
                    '--force' => true,
                ]);
                $this->info("✓ Migrations completed successfully");
            } catch (\Exception $e) {
                $this->warn("⚠ Migrations failed: " . $e->getMessage());
            }

        } catch (\Exception $e) {
            $this->error("❌ Error creating database: " . $e->getMessage());
            return 1;
        }

        $this->info("\n🎉 Database setup completed successfully!");
        return 0;
    }
}
