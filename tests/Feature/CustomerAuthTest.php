<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_login_and_register_forms(): void
    {
        $loginRes = $this->get(route('login'));
        $loginRes->assertStatus(200);
        $loginRes->assertSee('Customer Sign In');

        $registerRes = $this->get(route('register'));
        $registerRes->assertStatus(200);
        $registerRes->assertSee('Create Account');
    }

    public function test_customer_registration_validation_errors(): void
    {
        $response = $this->post(route('register'), [
            'name' => '',
            'email' => 'not-an-email',
            'phone' => '12345', // invalid BD phone
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'phone', 'password']);
    }

    public function test_customer_registration_success_with_bangladesh_phone(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Sakib Al Hasan',
            'email' => 'sakib@example.com',
            'phone' => '+8801711998877',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('customer.profile'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'sakib@example.com',
            'phone' => '01711998877', // normalized format
            'role' => 'customer',
        ]);
    }

    public function test_customer_can_login_with_email(): void
    {
        $user = User::factory()->create([
            'email' => 'tanvir@example.com',
            'password' => Hash::make('secretPass123'),
        ]);

        $response = $this->post(route('login'), [
            'login' => 'tanvir@example.com',
            'password' => 'secretPass123',
        ]);

        $response->assertRedirect(route('customer.profile'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_customer_can_login_with_mobile_number(): void
    {
        $user = User::factory()->create([
            'email' => 'mobileuser@example.com',
            'phone' => '01799887766',
            'password' => Hash::make('myPass999'),
        ]);

        // Customer types +8801799887766
        $response = $this->post(route('login'), [
            'login' => '+8801799887766',
            'password' => 'myPass999',
        ]);

        $response->assertRedirect(route('customer.profile'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_rate_limiting_after_repeated_failed_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'target@example.com',
            'password' => Hash::make('correctPassword'),
        ]);

        // 5 bad attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), [
                'login' => 'target@example.com',
                'password' => 'wrongPassword',
            ]);
        }

        // 6th attempt should be blocked by rate limiter
        $response = $this->post(route('login'), [
            'login' => 'target@example.com',
            'password' => 'wrongPassword',
        ]);

        $response->assertSessionHasErrors('login');
    }

    public function test_authenticated_customer_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_unauthenticated_guest_cannot_access_profile(): void
    {
        $response = $this->get(route('customer.profile'));
        $response->assertRedirect(route('login'));
    }

    public function test_customer_can_update_profile_and_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'orig@example.com',
            'phone' => '01811112222',
            'password' => Hash::make('oldPassword123'),
        ]);

        $this->actingAs($user);

        // Update profile
        $updateRes = $this->put(route('customer.profile.update'), [
            'name' => 'Updated Name',
            'email' => 'newemail@example.com',
            'phone' => '01922223333',
        ]);
        $updateRes->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'newemail@example.com',
            'phone' => '01922223333',
        ]);

        // Update password
        $passRes = $this->put(route('customer.password.update'), [
            'current_password' => 'oldPassword123',
            'password' => 'brandNewPass888',
            'password_confirmation' => 'brandNewPass888',
        ]);
        $passRes->assertSessionHas('success');

        $this->assertTrue(Hash::check('brandNewPass888', $user->fresh()->password));
    }

    public function test_customer_can_add_and_delete_address(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $addRes = $this->post(route('customer.address.store'), [
            'recipient_name' => 'Customer Name',
            'phone' => '01711223344',
            'division' => 'Dhaka',
            'district' => 'Dhaka',
            'upazila_thana' => 'Uttara',
            'address_line' => 'Sector 3, Road 7, House 12',
            'is_default' => 1,
        ]);

        $addRes->assertSessionHas('success');
        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'recipient_name' => 'Customer Name',
            'district' => 'Dhaka',
            'is_default' => 1,
        ]);

        $address = Address::where('user_id', $user->id)->first();

        $delRes = $this->delete(route('customer.address.delete', $address));
        $delRes->assertSessionHas('success');
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    /**
     * Requirement Test: Auto account creation for guest purchases.
     * When a guest purchases with a phone number, an account is auto-created
     * and the customer can later log in using that phone number.
     */
    public function test_guest_purchase_auto_creates_account_and_allows_subsequent_login(): void
    {
        $authService = app(AuthService::class);

        // 1. Guest purchases with mobile number
        $phone = '01755667788';
        $name = 'Guest Shopper';
        $email = 'guestshopper@gmail.com';

        $result = $authService->findOrCreateFromPurchase($phone, $name, $email);

        $this->assertTrue($result['was_created']);
        $this->assertNotNull($result['initial_password']);
        $createdUser = $result['user'];

        $this->assertDatabaseHas('users', [
            'phone' => '01755667788',
            'name' => 'Guest Shopper',
            'email' => 'guestshopper@gmail.com',
            'role' => 'customer',
        ]);

        // 2. Later, customer logs in using their phone number and initial password
        $loginRes = $this->post(route('login'), [
            'login' => $phone,
            'password' => $result['initial_password'],
        ]);

        $loginRes->assertRedirect(route('customer.profile'));
        $this->assertAuthenticatedAs($createdUser);

        // 3. If the same phone places another order, existing account is returned without duplicate
        $secondResult = $authService->findOrCreateFromPurchase($phone, $name, $email);
        $this->assertFalse($secondResult['was_created']);
        $this->assertEquals($createdUser->id, $secondResult['user']->id);
    }
}
