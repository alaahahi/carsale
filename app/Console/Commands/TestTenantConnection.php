<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\DynamicDatabaseHelper;
use App\Helpers\SubdomainHelper;
use App\Models\TenantDatabaseConfig;

class TestTenantConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:test-connection {domain : The domain to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test tenant connection for a specific domain';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $domain = $this->argument('domain');
        
        $this->info("Testing connection for domain: $domain");
        $this->newLine();

        try {
            // Test SubdomainHelper
            $this->info("1. Testing SubdomainHelper:");
            $subdomain = SubdomainHelper::extractSubdomain($domain);
            $this->line("   Subdomain: " . ($subdomain ?? 'null'));
            
            // Test tenant data retrieval
            $this->info("2. Testing tenant data retrieval:");
            $tenantData = SubdomainHelper::getTenantAndDatabaseConfigByDomain($domain);
            
            if ($tenantData && $tenantData['tenant'] && $tenantData['database_config']) {
                $this->line("   ✓ Tenant found: " . $tenantData['tenant']->name);
                $this->line("   ✓ Database config found: " . $tenantData['database_config']->database_name);
                $this->line("   ✓ Subdomain: " . $tenantData['subdomain']);
                
                // Test database connection
                $this->info("3. Testing database connection:");
                $dbConfig = $tenantData['database_config'];
                
                if ($dbConfig->testConnection()) {
                    $this->line("   ✓ Database connection successful");
                    
                    // Test DynamicDatabaseHelper
                    $this->info("4. Testing DynamicDatabaseHelper:");
                    $connectionName = DynamicDatabaseHelper::setConnection($dbConfig);
                    $this->line("   ✓ Connection name: $connectionName");
                    
                    // Test actual database query
                    try {
                        $connection = \DB::connection($connectionName);
                        $tables = $connection->select('SHOW TABLES');
                        $this->line("   ✓ Database query successful - " . count($tables) . " tables found");
                        
                        // Show some tables
                        if (count($tables) > 0) {
                            $this->line("   Tables:");
                            foreach (array_slice($tables, 0, 5) as $table) {
                                $tableName = array_values((array)$table)[0];
                                $this->line("     - $tableName");
                            }
                            if (count($tables) > 5) {
                                $this->line("     ... and " . (count($tables) - 5) . " more");
                            }
                        }
                        
                    } catch (\Exception $e) {
                        $this->error("   ❌ Database query failed: " . $e->getMessage());
                    }
                    
                } else {
                    $this->error("   ❌ Database connection failed");
                }
                
            } else {
                $this->error("   ❌ No tenant or database configuration found for domain: $domain");
                $this->line("   Make sure the tenant and database configuration exist.");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }

        $this->newLine();
        $this->info("🎉 Test completed!");
        return 0;
    }
}
