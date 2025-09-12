<?php

namespace App\Models;

use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'name',
        'domain',
        'email',
        'phone',
        'address',
        'status',
        'subscription_plan',
        'subscription_expires_at',
        'settings',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'subscription_expires_at' => 'datetime',
    ];

    /**
     * Get the users for this tenant
     */
    public function users()
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    /**
     * Get the companies for this tenant
     */
    public function companies()
    {
        return $this->hasMany(Company::class, 'tenant_id');
    }

    /**
     * Get the cars for this tenant
     */
    public function cars()
    {
        return $this->hasMany(Car::class, 'tenant_id');
    }

    /**
     * Get the database configuration for this tenant (one-to-one relationship)
     */
    public function databaseConfig()
    {
        return $this->hasOne(TenantDatabaseConfig::class, 'tenant_id');
    }

    /**
     * Get the database configurations for this tenant (keeping for backward compatibility)
     */
    public function databaseConfigs()
    {
        return $this->hasMany(TenantDatabaseConfig::class, 'tenant_id');
    }

    /**
     * Get the active database configuration for this tenant
     */
    public function activeDatabaseConfig()
    {
        return $this->hasOne(TenantDatabaseConfig::class, 'tenant_id')->where('is_active', true);
    }

    /**
     * Check if tenant is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if tenant subscription is valid
     */
    public function hasValidSubscription()
    {
        return $this->subscription_expires_at && $this->subscription_expires_at->isFuture();
    }

    /**
     * Get tenant setting
     */
    public function getSetting($key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Set tenant setting
     */
    public function setSetting($key, $value)
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->update(['settings' => $settings]);
    }

    /**
     * Create tenant database
     */
    public function createDatabase()
    {
        try {
            $databaseManager = app(\Stancl\Tenancy\Contracts\DatabaseManager::class);
            return $databaseManager->createDatabase($this);
        } catch (\Exception $e) {
            // Fallback: use artisan command
            \Artisan::call('tenants:create', [
                '--tenants' => $this->id,
            ]);
            return true;
        }
    }

    /**
     * Delete tenant database
     */
    public function deleteDatabase()
    {
        try {
            $databaseManager = app(\Stancl\Tenancy\Contracts\DatabaseManager::class);
            return $databaseManager->deleteDatabase($this);
        } catch (\Exception $e) {
            // Fallback: use artisan command
            \Artisan::call('tenants:delete', [
                '--tenants' => $this->id,
            ]);
            return true;
        }
    }

    /**
     * Run code in tenant context
     */
    public function run($callback)
    {
        try {
            return tenancy()->initialize($this, $callback);
        } catch (\Exception $e) {
            // Fallback: run in tenant context manually
            $originalConnection = config('database.default');
            config(['database.default' => 'tenant']);
            
            $result = $callback();
            
            config(['database.default' => $originalConnection]);
            return $result;
        }
    }

    /**
     * Create database with custom configuration
     */
    public function createDatabaseWithConfig($config = null)
    {
        try {
            if ($config) {
                // Use custom configuration
                $connectionName = 'custom_tenant_' . $this->id;
                $connectionInfo = $config->getConnectionInfo();
                
                config([
                    "database.connections.{$connectionName}" => $connectionInfo
                ]);
                
                // Create database using custom connection
                $connection = \DB::connection($connectionName);
                $connection->statement("CREATE DATABASE IF NOT EXISTS `{$config->database_name}`");
                
                return true;
            } else {
                // Use default tenancy configuration
                return $this->createDatabase();
            }
        } catch (\Exception $e) {
            throw new \Exception("فشل في إنشاء قاعدة البيانات: " . $e->getMessage());
        }
    }

    /**
     * Run migrations with custom configuration
     */
    public function runMigrationsWithConfig($config = null, $force = false)
    {
        try {
            if ($config) {
                // Use custom configuration for migrations
                $connectionName = 'migration_tenant_' . $this->id;
                $connectionInfo = $config->getConnectionInfo();
                
                config([
                    "database.connections.{$connectionName}" => $connectionInfo
                ]);
                
                // Run migrations using custom connection
                \Artisan::call('migrate', [
                    '--database' => $connectionName,
                    '--force' => $force,
                ]);
                
                return true;
            } else {
                // Use default tenancy migrations
                \Artisan::call('tenants:migrate', [
                    '--tenants' => $this->id,
                ]);
                
                return true;
            }
        } catch (\Exception $e) {
            throw new \Exception("فشل في تشغيل المايكريشن: " . $e->getMessage());
        }
    }

    /**
     * Test database connection with custom configuration
     */
    public function testDatabaseConnection($config = null)
    {
        try {
            if ($config) {
                return $config->testConnection();
            } else {
                // Test default tenant connection
                $this->run(function() {
                    \DB::connection()->getPdo();
                });
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get database connection info
     */
    public function getDatabaseInfo($config = null)
    {
        try {
            if ($config) {
                return $config->getSafeConnectionInfo();
            } else {
                $this->run(function() use (&$info) {
                    $connection = \DB::connection();
                    $info = [
                        'database' => $connection->getDatabaseName(),
                        'host' => $connection->getConfig('host'),
                        'port' => $connection->getConfig('port'),
                        'driver' => $connection->getConfig('driver'),
                    ];
                });
                return $info ?? [];
            }
        } catch (\Exception $e) {
            return [];
        }
    }
}
