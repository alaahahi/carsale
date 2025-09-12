<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MainPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test main page loads successfully
     */
    public function test_main_page_loads_successfully()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertViewIs('main.index');
    }

    /**
     * Test select tenant page loads successfully
     */
    public function test_select_tenant_page_loads_successfully()
    {
        $response = $this->get('/select-tenant');
        $response->assertStatus(200);
        $response->assertViewIs('main.select-tenant');
    }

    /**
     * Test dashboard page loads successfully
     */
    public function test_dashboard_page_loads_successfully()
    {
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('main.dashboard');
    }

    /**
     * Test admin redirect works
     */
    public function test_admin_redirect_works()
    {
        $response = $this->get('/admin');
        $response->assertRedirect(route('tenants.index'));
    }

    /**
     * Test main page shows tenant statistics
     */
    public function test_main_page_shows_tenant_statistics()
    {
        // Create test tenants
        Tenant::create([
            'id' => 'test-tenant-1',
            'name' => 'Test Company 1',
            'email' => 'test1@example.com',
            'status' => 'active',
            'subscription_plan' => 'basic',
            'settings' => [],
        ]);

        Tenant::create([
            'id' => 'test-tenant-2',
            'name' => 'Test Company 2',
            'email' => 'test2@example.com',
            'status' => 'inactive',
            'subscription_plan' => 'premium',
            'settings' => [],
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Test Company 1');
        $response->assertSee('Test Company 2');
    }

    /**
     * Test select tenant page shows active tenants
     */
    public function test_select_tenant_page_shows_active_tenants()
    {
        // Create active tenant
        $activeTenant = Tenant::create([
            'id' => 'active-tenant',
            'name' => 'Active Company',
            'email' => 'active@example.com',
            'status' => 'active',
            'subscription_plan' => 'premium',
            'settings' => [],
        ]);

        // Create inactive tenant
        Tenant::create([
            'id' => 'inactive-tenant',
            'name' => 'Inactive Company',
            'email' => 'inactive@example.com',
            'status' => 'inactive',
            'subscription_plan' => 'basic',
            'settings' => [],
        ]);

        $response = $this->get('/select-tenant');
        $response->assertStatus(200);
        $response->assertSee('Active Company');
        $response->assertDontSee('Inactive Company');
    }

    /**
     * Test dashboard shows correct statistics
     */
    public function test_dashboard_shows_correct_statistics()
    {
        // Create tenants with different statuses
        Tenant::create([
            'id' => 'active-tenant',
            'name' => 'Active Company',
            'email' => 'active@example.com',
            'status' => 'active',
            'subscription_plan' => 'premium',
            'settings' => [],
        ]);

        Tenant::create([
            'id' => 'inactive-tenant',
            'name' => 'Inactive Company',
            'email' => 'inactive@example.com',
            'status' => 'inactive',
            'subscription_plan' => 'basic',
            'settings' => [],
        ]);

        Tenant::create([
            'id' => 'suspended-tenant',
            'name' => 'Suspended Company',
            'email' => 'suspended@example.com',
            'status' => 'suspended',
            'subscription_plan' => 'enterprise',
            'settings' => [],
        ]);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('3'); // Total tenants
        $response->assertSee('1'); // Active tenants
        $response->assertSee('1'); // Inactive tenants
        $response->assertSee('1'); // Suspended tenants
    }

    /**
     * Test tenant redirect functionality
     */
    public function test_tenant_redirect_functionality()
    {
        // Create tenant with domain
        $tenant = Tenant::create([
            'id' => 'redirect-tenant',
            'name' => 'Redirect Company',
            'email' => 'redirect@example.com',
            'status' => 'active',
            'subscription_plan' => 'premium',
            'settings' => [],
        ]);

        Domain::create([
            'domain' => 'redirect.car-management.local',
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->post('/redirect-to-tenant', [
            'tenant_id' => $tenant->id,
        ]);

        $response->assertRedirect();
    }
}
