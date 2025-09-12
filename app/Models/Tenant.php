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
}
