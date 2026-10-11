<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_accessing_admin_dashboard_is_redirected_to_admin_login(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_customer_accessing_admin_dashboard_gets_403_forbidden(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $response = $this->actingAs($customer)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_admin_can_login_with_email_and_reach_dashboard(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin_super@shopbd.com',
            'password' => Hash::make('AdminSecret123'),
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'login' => 'admin_super@shopbd.com',
            'password' => 'AdminSecret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        // Can access dashboard
        $dashRes = $this->get(route('admin.dashboard'));
        $dashRes->assertStatus(200);
        $dashRes->assertSee('ShopBD Executive Dashboard');
    }

    public function test_customer_credentials_cannot_login_at_admin_login_portal(): void
    {
        $customer = User::factory()->create([
            'email' => 'regular_cust@shopbd.com',
            'role' => 'customer',
            'password' => Hash::make('Password123'),
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'login' => 'regular_cust@shopbd.com',
            'password' => 'Password123',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_admin_can_logout(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_artisan_command_safely_creates_admin_without_hardcoded_credentials(): void
    {
        $this->artisan('shopbd:create-admin', [
            '--name' => 'Operational Admin',
            '--email' => 'ops_admin@shopbd.com',
            '--phone' => '01722334455',
            '--password' => 'OpsSecurePassword999',
        ])
        ->expectsOutputToContain('created successfully')
        ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'email' => 'ops_admin@shopbd.com',
            'phone' => '01722334455',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $created = User::where('email', 'ops_admin@shopbd.com')->first();
        $this->assertTrue(Hash::check('OpsSecurePassword999', $created->password));
    }
}
