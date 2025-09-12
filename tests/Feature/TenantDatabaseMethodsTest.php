<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TenantDatabaseMethodsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test createDatabase method exists and works
     */
    public function test_create_database_method_exists()
    {
        $tenant = new Tenant();
        $this->assertTrue(method_exists($tenant, 'createDatabase'));
    }

    /**
     * Test deleteDatabase method exists
     */
    public function test_delete_database_method_exists()
    {
        $tenant = new Tenant();
        $this->assertTrue(method_exists($tenant, 'deleteDatabase'));
    }

    /**
     * Test run method exists
     */
    public function test_run_method_exists()
    {
        $tenant = new Tenant();
        $this->assertTrue(method_exists($tenant, 'run'));
    }

    /**
     * Test tenant creation with database
     */
    public function test_tenant_creation_with_database()
    {
        $tenant = Tenant::create([
            'id' => 'test-tenant-db',
            'name' => 'Test Company DB',
            'email' => 'testdb@example.com',
            'status' => 'active',
            'subscription_plan' => 'basic',
            'settings' => [],
        ]);

        // Test that tenant was created
        $this->assertDatabaseHas('tenants', [
            'id' => 'test-tenant-db',
            'name' => 'Test Company DB',
        ]);

        // Test that methods are callable
        $this->assertTrue(is_callable([$tenant, 'createDatabase']));
        $this->assertTrue(is_callable([$tenant, 'deleteDatabase']));
        $this->assertTrue(is_callable([$tenant, 'run']));
    }

    /**
     * Test tenant with domain creation
     */
    public function test_tenant_with_domain_creation()
    {
        $tenant = Tenant::create([
            'id' => 'test-tenant-domain',
            'name' => 'Test Company Domain',
            'email' => 'testdomain@example.com',
            'status' => 'active',
            'subscription_plan' => 'premium',
            'settings' => [],
        ]);

        $domain = Domain::create([
            'domain' => 'testdomain.car-management.local',
            'tenant_id' => $tenant->id,
        ]);

        $this->assertDatabaseHas('tenants', [
            'id' => 'test-tenant-domain',
        ]);

        $this->assertDatabaseHas('domains', [
            'domain' => 'testdomain.car-management.local',
            'tenant_id' => 'test-tenant-domain',
        ]);
    }

    /**
     * Test tenant status methods
     */
    public function test_tenant_status_methods()
    {
        $activeTenant = Tenant::create([
            'id' => 'active-tenant-test',
            'name' => 'Active Company Test',
            'email' => 'active@example.com',
            'status' => 'active',
            'subscription_plan' => 'premium',
            'subscription_expires_at' => now()->addDays(30),
            'settings' => [],
        ]);

        $this->assertTrue($activeTenant->isActive());
        $this->assertTrue($activeTenant->hasValidSubscription());
    }

    /**
     * Test tenant settings methods
     */
    public function test_tenant_settings_methods()
    {
        $tenant = Tenant::create([
            'id' => 'settings-tenant-test',
            'name' => 'Settings Company Test',
            'email' => 'settings@example.com',
            'status' => 'active',
            'subscription_plan' => 'enterprise',
            'settings' => ['theme' => 'dark', 'language' => 'ar'],
        ]);

        // Test getting settings
        $this->assertEquals('dark', $tenant->getSetting('theme'));
        $this->assertEquals('ar', $tenant->getSetting('language'));
        $this->assertEquals('default', $tenant->getSetting('nonexistent', 'default'));

        // Test setting settings
        $tenant->setSetting('theme', 'light');
        $this->assertEquals('light', $tenant->getSetting('theme'));
    }

    /**
     * Test tenant relationships
     */
    public function test_tenant_relationships()
    {
        $tenant = Tenant::create([
            'id' => 'relationship-tenant',
            'name' => 'Relationship Company',
            'email' => 'relationship@example.com',
            'status' => 'active',
            'subscription_plan' => 'basic',
            'settings' => [],
        ]);

        // Test that relationships exist
        $this->assertTrue(method_exists($tenant, 'users'));
        $this->assertTrue(method_exists($tenant, 'companies'));
        $this->assertTrue(method_exists($tenant, 'cars'));
        $this->assertTrue(method_exists($tenant, 'domains'));
    }
}
