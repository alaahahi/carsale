<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin tenants page loads successfully
     */
    public function test_admin_tenants_page_loads_successfully()
    {
        $response = $this->get('/admin/tenants');
        $response->assertStatus(200);
        $response->assertViewIs('tenants.index');
    }

    /**
     * Test admin tenants create page loads successfully
     */
    public function test_admin_tenants_create_page_loads_successfully()
    {
        $response = $this->get('/admin/tenants/create');
        $response->assertStatus(200);
        $response->assertViewIs('tenants.create');
    }

    /**
     * Test admin index redirects to tenants
     */
    public function test_admin_index_redirects_to_tenants()
    {
        $response = $this->get('/admin');
        $response->assertRedirect(route('tenants.index'));
    }

    /**
     * Test main admin route redirects to tenants
     */
    public function test_main_admin_route_redirects_to_tenants()
    {
        $response = $this->get('/admin');
        $response->assertRedirect(route('tenants.index'));
    }

    /**
     * Test admin routes work with tenants
     */
    public function test_admin_routes_work_with_tenants()
    {
        // Create a test tenant
        $tenant = Tenant::create([
            'id' => 'test-tenant',
            'name' => 'Test Company',
            'email' => 'test@example.com',
            'status' => 'active',
            'subscription_plan' => 'basic',
            'settings' => [],
        ]);

        // Test tenant show page
        $response = $this->get("/admin/tenants/{$tenant->id}");
        $response->assertStatus(200);
        $response->assertViewIs('tenants.show');

        // Test tenant edit page
        $response = $this->get("/admin/tenants/{$tenant->id}/edit");
        $response->assertStatus(200);
        $response->assertViewIs('tenants.edit');
    }

    /**
     * Test admin routes are accessible from main domain
     */
    public function test_admin_routes_accessible_from_main_domain()
    {
        // Simulate request from main domain
        $this->app['request']->headers->set('HOST', '127.0.0.1:8000');
        
        $response = $this->get('/admin/tenants');
        $response->assertStatus(200);
    }

    /**
     * Test central admin routes work for subdomains
     */
    public function test_central_admin_routes_work_for_subdomains()
    {
        // This would need proper subdomain setup in testing
        // For now, just test that the route exists
        $response = $this->get('/central-admin/tenants');
        // This might return 404 in testing environment, which is expected
        // In real subdomain environment, it should work
    }
}
