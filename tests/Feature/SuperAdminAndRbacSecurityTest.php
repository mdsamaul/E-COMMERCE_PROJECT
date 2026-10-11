<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminAndRbacSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $regularAdmin;
    protected User $customer;
    protected Role $adminRole;
    protected Role $superAdminRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $this->seed(RolePermissionSeeder::class);

        $this->superAdminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->adminRole = Role::where('slug', 'admin')->firstOrFail();

        // Create Super Admin
        $this->superAdmin = User::factory()->superAdmin()->create([
            'email' => 'master_sa@shopbd.com',
            'password' => Hash::make('SuperSecret123'),
        ]);
        $this->superAdmin->roles()->attach($this->superAdminRole);

        // Create Regular Admin
        $this->regularAdmin = User::factory()->admin()->create([
            'email' => 'regular_admin@shopbd.com',
            'password' => Hash::make('AdminSecret123'),
            'is_super_admin' => false,
        ]);
        $this->regularAdmin->roles()->attach($this->adminRole);

        // Create Customer
        $this->customer = User::factory()->create([
            'role' => 'customer',
            'is_super_admin' => false,
            'password' => Hash::make('CustomerPass123'),
        ]);
    }

    /**
     * Requirement 1: Super Admin can log in at /superadmin/login.
     */
    public function test_super_admin_can_login_at_superadmin_login(): void
    {
        $response = $this->post(route('superadmin.login.submit'), [
            'login' => 'master_sa@shopbd.com',
            'password' => 'SuperSecret123',
        ]);

        $response->assertRedirect(route('superadmin.dashboard'));
        $this->assertAuthenticatedAs($this->superAdmin);
    }

    /**
     * Requirement 2: Super Admin can access /superadmin/dashboard.
     */
    public function test_super_admin_can_access_superadmin_dashboard(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('superadmin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Super Administrator Control Center');
        $response->assertSee('SUPER ADMIN');
    }

    /**
     * Requirement 3: Regular Admin can log in at /admin/login.
     */
    public function test_regular_admin_can_login_at_admin_login(): void
    {
        $response = $this->post(route('admin.login.submit'), [
            'login' => 'regular_admin@shopbd.com',
            'password' => 'AdminSecret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->regularAdmin);
    }

    /**
     * Requirement 4: Regular Admin can access /admin/dashboard when authorized.
     */
    public function test_regular_admin_can_access_admin_dashboard_when_authorized(): void
    {
        $response = $this->actingAs($this->regularAdmin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Executive Dashboard');
    }

    /**
     * Requirement 5: Regular Admin cannot access /superadmin/dashboard.
     */
    public function test_regular_admin_cannot_access_superadmin_dashboard(): void
    {
        $response = $this->actingAs($this->regularAdmin)->get(route('superadmin.dashboard'));

        $response->assertStatus(403);
    }

    /**
     * Requirement 6: Regular Admin cannot create an Admin account.
     */
    public function test_regular_admin_cannot_create_an_admin_account(): void
    {
        $response = $this->actingAs($this->regularAdmin)->post(route('superadmin.admins.store'), [
            'name' => 'Attempted Rogue Admin',
            'email' => 'rogue@shopbd.com',
            'phone' => '01799887766',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_active' => 1,
            'role_id' => $this->adminRole->id,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', ['email' => 'rogue@shopbd.com']);
    }

    /**
     * Requirement 7: A customer cannot access either administrative dashboard.
     */
    public function test_customer_cannot_access_either_administrative_dashboard(): void
    {
        $superAdminRes = $this->actingAs($this->customer)->get(route('superadmin.dashboard'));
        $superAdminRes->assertStatus(403);

        $adminRes = $this->actingAs($this->customer)->get(route('admin.dashboard'));
        $adminRes->assertStatus(403);
    }

    /**
     * Requirement 8: Unauthenticated requests redirect to the appropriate login page.
     */
    public function test_unauthenticated_requests_redirect_to_appropriate_login_page(): void
    {
        // Accessing superadmin route redirects to /superadmin/login
        $saRes = $this->get(route('superadmin.dashboard'));
        $saRes->assertRedirect(route('superadmin.login'));

        // Accessing admin route redirects to /admin/login
        $adminRes = $this->get(route('admin.dashboard'));
        $adminRes->assertRedirect(route('admin.login'));

        // Accessing customer protected route redirects to customer login
        $custRes = $this->get(route('customer.profile'));
        $custRes->assertRedirect(route('login'));
    }

    /**
     * Requirement 9: Direct POST, PUT, PATCH, and DELETE requests are protected.
     */
    public function test_direct_mutating_requests_are_protected(): void
    {
        // Unauthenticated mutating request
        $this->post(route('superadmin.admins.store'), [])->assertRedirect(route('superadmin.login'));
        $this->patch(route('superadmin.admins.status', $this->regularAdmin->id))->assertRedirect(route('superadmin.login'));

        // Ordinary Admin attempting Super Admin state changes
        $this->actingAs($this->regularAdmin)
            ->patch(route('superadmin.admins.status', $this->regularAdmin->id))
            ->assertStatus(403);

        $customRole = Role::create(['name' => 'Demo Role', 'slug' => 'demo-role', 'is_system' => false]);
        $this->actingAs($this->regularAdmin)
            ->delete(route('superadmin.roles.destroy', $customRole->id))
            ->assertStatus(403);
    }

    /**
     * Requirement 10: Super Admin can create a regular Admin account.
     */
    public function test_super_admin_can_create_a_regular_admin_account(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('superadmin.admins.store'), [
            'name' => 'Kawsar Mahmud',
            'email' => 'kawsar_ops@shopbd.com',
            'phone' => '01719998877',
            'password' => 'KawsarPass123',
            'password_confirmation' => 'KawsarPass123',
            'is_active' => 1,
            'role_id' => $this->adminRole->id,
        ]);

        $response->assertRedirect(route('superadmin.admins.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Kawsar Mahmud',
            'email' => 'kawsar_ops@shopbd.com',
            'phone' => '01719998877',
            'role' => 'admin',
            'is_super_admin' => false,
            'is_active' => true,
        ]);

        $createdUser = User::where('email', 'kawsar_ops@shopbd.com')->firstOrFail();
        $this->assertTrue($createdUser->roles->contains('id', $this->adminRole->id));
    }

    /**
     * Requirement 11: Newly created Admin credentials are stored securely.
     */
    public function test_newly_created_admin_credentials_are_stored_securely(): void
    {
        $this->actingAs($this->superAdmin)->post(route('superadmin.admins.store'), [
            'name' => 'Security Test Admin',
            'email' => 'security_audit@shopbd.com',
            'phone' => '01718889900',
            'password' => 'PlaintextNeverStored999',
            'password_confirmation' => 'PlaintextNeverStored999',
            'is_active' => 1,
            'role_id' => $this->adminRole->id,
        ]);

        $user = User::where('email', 'security_audit@shopbd.com')->firstOrFail();

        // Ensure password is not plain text
        $this->assertNotEquals('PlaintextNeverStored999', $user->password);
        $this->assertTrue(Hash::check('PlaintextNeverStored999', $user->password));
    }

    /**
     * Requirement 12: The create form cannot assign a protected Super Admin role.
     */
    public function test_create_form_cannot_assign_protected_super_admin_role(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('superadmin.admins.store'), [
            'name' => 'Malicious Elevation Attempt',
            'email' => 'malicious@shopbd.com',
            'phone' => '01717776655',
            'password' => 'SecretPassword123',
            'password_confirmation' => 'SecretPassword123',
            'is_active' => 1,
            'role_id' => $this->superAdminRole->id, // Attempting to pass super-admin role ID
        ]);

        $response->assertSessionHasErrors('role_id');
        $this->assertDatabaseMissing('users', ['email' => 'malicious@shopbd.com']);
    }

    /**
     * Requirement 13: An Admin cannot promote themselves or another user to Super Admin.
     */
    public function test_admin_cannot_promote_themselves_or_another_user_to_super_admin(): void
    {
        // Try manipulating update endpoint via form injection
        $response = $this->actingAs($this->regularAdmin)->put(route('admin.profile.update'), [
            'name' => 'Self Escalated Name',
            'email' => $this->regularAdmin->email,
            'phone' => $this->regularAdmin->phone,
            'is_super_admin' => 1,
            'role' => 'superadmin',
        ]);

        $this->regularAdmin->refresh();
        $this->assertFalse($this->regularAdmin->is_super_admin);
        $this->assertFalse($this->regularAdmin->isSuperAdmin());
    }

    /**
     * Requirement 14: Permission assignment cannot exceed the actor's authorized delegation scope.
     */
    public function test_permission_assignment_cannot_exceed_delegation_scope(): void
    {
        $superAdminOnlyPerm = Permission::where('module', 'SuperAdmin')->firstOrFail();

        $response = $this->actingAs($this->superAdmin)->post(route('superadmin.admins.store'), [
            'name' => 'Scope Test User',
            'email' => 'scope_test@shopbd.com',
            'phone' => '01715554433',
            'password' => 'ScopePass123',
            'password_confirmation' => 'ScopePass123',
            'is_active' => 1,
            'role_id' => $this->adminRole->id,
            'permissions' => [$superAdminOnlyPerm->id], // Disallowed SuperAdmin module right
        ]);

        $response->assertSessionHasErrors('permissions.0');
        $this->assertDatabaseMissing('users', ['email' => 'scope_test@shopbd.com']);
    }

    /**
     * Requirement 15: Existing product, category, order, customer, and report routes continue to work.
     */
    public function test_existing_administrative_routes_continue_to_work(): void
    {
        $this->actingAs($this->regularAdmin);

        $this->get(route('admin.products.index'))->assertStatus(200);
        $this->get(route('admin.categories.index'))->assertStatus(200);
        $this->get(route('admin.brands.index'))->assertStatus(200);
        $this->get(route('admin.orders.index'))->assertStatus(200);
        $this->get(route('admin.customers.index'))->assertStatus(200);
        $this->get(route('admin.reports.sales'))->assertStatus(200);
        $this->get(route('admin.profile'))->assertStatus(200);
    }

    /**
     * Requirement 16: Existing customer login and registration continue to work.
     */
    public function test_existing_customer_login_and_registration_continue_to_work(): void
    {
        $this->get(route('login'))->assertStatus(200);
        $this->get(route('register'))->assertStatus(200);

        $loginRes = $this->post(route('login'), [
            'login' => $this->customer->email,
            'password' => 'CustomerPass123',
        ]);

        $loginRes->assertRedirect(route('customer.profile'));
        $this->assertAuthenticatedAs($this->customer);
    }

    /**
     * Requirement 17: No conflicting or duplicate route definitions exist.
     */
    public function test_no_conflicting_or_duplicate_route_definitions_exist(): void
    {
        $this->assertEquals(url('/superadmin/login'), route('superadmin.login'));
        $this->assertEquals(url('/superadmin/dashboard'), route('superadmin.dashboard'));
        $this->assertEquals(url('/admin/login'), route('admin.login'));
        $this->assertEquals(url('/admin/dashboard'), route('admin.dashboard'));

        $this->assertNotEquals(route('superadmin.login'), route('admin.login'));
        $this->assertNotEquals(route('superadmin.dashboard'), route('admin.dashboard'));
    }
}
