<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\CreatesDomainData;
use Tests\TestCase;

class AuthenticationAuthorizationTest extends TestCase
{
    use CreatesDomainData, RefreshDatabase;

    public function test_public_registration_always_creates_an_active_customer(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Cliente Uno', 'email' => 'cliente@example.test', 'telefono' => '7000-1111',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'rol' => 'admin', 'estado' => false,
        ]);

        $response->assertRedirectToRoute('catalog.index');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'cliente@example.test', 'rol' => 'cliente', 'estado' => true]);
    }

    public function test_login_redirects_each_role_to_its_own_area(): void
    {
        foreach ([
            UserRole::ADMIN->value => 'admin.dashboard',
            UserRole::EMPRENDEDOR->value => 'entrepreneur.dashboard',
            UserRole::CLIENTE->value => 'catalog.index',
        ] as $role => $route) {
            $user = $this->user(UserRole::from($role), ['password' => Hash::make('secret123')]);
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'secret123'])
                ->assertRedirectToRoute($route);
            $this->post(route('logout'));
        }
    }

    public function test_invalid_credentials_and_inactive_accounts_are_rejected(): void
    {
        $active = $this->user(attributes: ['password' => Hash::make('correct-password')]);
        $this->post(route('login.store'), ['email' => $active->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $inactive = $this->user(attributes: ['estado' => false, 'password' => Hash::make('correct-password')]);
        $this->post(route('login.store'), ['email' => $inactive->email, 'password' => 'correct-password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_private_routes_require_authentication_role_and_active_account(): void
    {
        $this->get(route('profile.edit'))->assertRedirectToRoute('login');

        $customer = $this->user();
        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($customer)->get(route('entrepreneur.dashboard'))->assertForbidden();

        $inactiveAdmin = $this->user(UserRole::ADMIN, ['estado' => false]);
        $this->actingAs($inactiveAdmin)->get(route('admin.dashboard'))->assertRedirectToRoute('login');
        $this->assertGuest();
    }

    public function test_logout_invalidates_authentication_and_admin_cannot_use_customer_private_routes(): void
    {
        $customer = $this->user();
        $this->actingAs($customer)->post(route('logout'))->assertRedirectToRoute('catalog.index');
        $this->assertGuest();

        $admin = $this->user(UserRole::ADMIN);
        $this->actingAs($admin)->get(route('profile.edit'))->assertForbidden();
        $this->actingAs($admin)->get(route('register'))->assertRedirect();
    }

    public function test_customer_and_entrepreneur_cannot_view_orders_owned_by_others(): void
    {
        $customer = $this->user();
        $otherCustomer = $this->user();
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $otherOwner = $this->user(UserRole::EMPRENDEDOR);
        $order = $this->order($this->business($owner), $customer);

        $this->actingAs($otherCustomer)->get(route('customer.orders.show', $order))->assertForbidden();
        $this->actingAs($otherOwner)->get(route('entrepreneur.orders.show', $order))->assertForbidden();
        $this->actingAs($customer)->get(route('customer.orders.show', $order))->assertOk();
    }
}
