<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;
use App\Helpers\SubdomainHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubdomainSystemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test tenant creation with subdomain
     */
    public function test_tenant_creation_with_subdomain()
    {
        $tenant = Tenant::create([
            'id' => 'test-tenant-1',
            'name' => 'Test Company',
            'email' => 'test@example.com',
            'status' => 'active',
            'subscription_plan' => 'basic',
            'settings' => [],
        ]);

        $domain = Domain::create([
            'domain' => 'test.car-management.local',
            'tenant_id' => $tenant->id,
        ]);

        $this->assertDatabaseHas('tenants', [
            'id' => 'test-tenant-1',
            'name' => 'Test Company',
        ]);

        $this->assertDatabaseHas('domains', [
            'domain' => 'test.car-management.local',
            'tenant_id' => 'test-tenant-1',
        ]);
    }

    /**
     * Test subdomain helper functions
     */
    public function test_subdomain_helper_functions()
    {
        // Create test tenant
        $tenant = Tenant::create([
            'id' => 'test-tenant-2',
            'name' => 'Test Company 2',
            'email' => 'test2@example.com',
            'status' => 'active',
            'subscription_plan' => 'premium',
            'settings' => [],
        ]);

        $domain = Domain::create([
            'domain' => 'test2.car-management.local',
            'tenant_id' => $tenant->id,
        ]);

        // Test subdomain extraction
        $subdomain = SubdomainHelper::extractSubdomain('test2.car-management.local');
        $this->assertEquals('test2', $subdomain);

        // Test central domain check
        $isCentral = SubdomainHelper::isCentralDomain('admin.car-management.local');
        $this->assertTrue($isCentral);

        $isNotCentral = SubdomainHelper::isCentralDomain('test2.car-management.local');
        $this->assertFalse($isNotCentral);

        // Test subdomain validation
        $isValid = SubdomainHelper::validateSubdomain('test2');
        $this->assertTrue($isValid);

        $isInvalid = SubdomainHelper::validateSubdomain('a');
        $this->assertFalse($isInvalid);

        // Test URL generation
        $url = SubdomainHelper::generateSubdomainUrl('test2', '/dashboard');
        $this->assertStringContains('test2.', $url);
        $this->assertStringContains('/dashboard', $url);
    }

    /**
     * Test tenant cache functionality
     */
    public function test_tenant_cache_functionality()
    {
        // Create test tenant
        $tenant = Tenant::create([
            'id' => 'test-tenant-3',
            'name' => 'Test Company 3',
            'email' => 'test3@example.com',
            'status' => 'active',
            'subscription_plan' => 'enterprise',
            'settings' => [],
        ]);

        $domain = Domain::create([
            'domain' => 'test3.car-management.local',
            'tenant_id' => $tenant->id,
        ]);

        // Test cache retrieval
        $cachedTenant = SubdomainHelper::getTenantByDomain('test3.car-management.local');
        $this->assertNotNull($cachedTenant);
        $this->assertEquals('test-tenant-3', $cachedTenant->id);

        // Test cache clearing
        SubdomainHelper::clearTenantCache('test-tenant-3', 'test3.car-management.local');
        
        // Cache should be cleared, but tenant should still exist
        $this->assertDatabaseHas('tenants', ['id' => 'test-tenant-3']);
    }

    /**
     * Test tenant status methods
     */
    public function test_tenant_status_methods()
    {
        $activeTenant = Tenant::create([
            'id' => 'active-tenant',
            'name' => 'Active Company',
            'email' => 'active@example.com',
            'status' => 'active',
            'subscription_plan' => 'premium',
            'subscription_expires_at' => now()->addDays(30),
            'settings' => [],
        ]);

        $inactiveTenant = Tenant::create([
            'id' => 'inactive-tenant',
            'name' => 'Inactive Company',
            'email' => 'inactive@example.com',
            'status' => 'inactive',
            'subscription_plan' => 'basic',
            'subscription_expires_at' => now()->subDays(30),
            'settings' => [],
        ]);

        // Test active status
        $this->assertTrue($activeTenant->isActive());
        $this->assertTrue($activeTenant->hasValidSubscription());

        // Test inactive status
        $this->assertFalse($inactiveTenant->isActive());
        $this->assertFalse($inactiveTenant->hasValidSubscription());
    }

    /**
     * Test tenant settings
     */
    public function test_tenant_settings()
    {
        $tenant = Tenant::create([
            'id' => 'settings-tenant',
            'name' => 'Settings Company',
            'email' => 'settings@example.com',
            'status' => 'active',
            'subscription_plan' => 'premium',
            'settings' => ['theme' => 'dark', 'language' => 'ar'],
        ]);

        // Test getting settings
        $theme = $tenant->getSetting('theme');
        $this->assertEquals('dark', $theme);

        $language = $tenant->getSetting('language');
        $this->assertEquals('ar', $language);

        $defaultValue = $tenant->getSetting('nonexistent', 'default');
        $this->assertEquals('default', $defaultValue);

        // Test setting settings
        $tenant->setSetting('theme', 'light');
        $this->assertEquals('light', $tenant->getSetting('theme'));
    }
}
