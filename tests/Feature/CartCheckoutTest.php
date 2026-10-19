<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pedido;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesDomainData;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use CreatesDomainData, RefreshDatabase;

    public function test_customer_can_add_update_remove_and_clear_cart_items(): void
    {
        $customer = $this->user();
        $product = $this->product($this->business());

        $this->actingAs($customer)->post(route('cart.store', $product), ['cantidad' => 2])->assertRedirectToRoute('cart.index');
        $this->assertSame(2, session('cart')[$product->id]);
        $this->patch(route('cart.update', $product), ['cantidad' => 4]);
        $this->assertSame(4, session('cart')[$product->id]);
        $this->delete(route('cart.destroy', $product));
        $this->assertArrayNotHasKey($product->id, session('cart'));
        $this->post(route('cart.store', $product), ['cantidad' => 1]);
        $this->delete(route('cart.clear'));
        $this->assertNull(session('cart'));
    }

    public function test_cart_json_update_changes_session_and_returns_server_totals(): void
    {
        $customer = $this->user();
        $business = $this->business();
        $first = $this->product($business, ['precio' => 25]);
        $second = $this->product($business, ['precio' => 10]);

        $this->actingAs($customer)->post(route('cart.store', $first), ['cantidad' => 2]);
        $this->post(route('cart.store', $second), ['cantidad' => 1]);

        $this->patchJson(route('cart.update', $first), ['cantidad' => 4])
            ->assertOk()
            ->assertJson([
                'quantity' => 4,
                'line_subtotal' => 100,
                'cart_total' => 110,
            ])
            ->assertSessionHas('cart.'.$first->id, 4);
    }

    public function test_cart_patch_cannot_introduce_a_product_that_is_not_in_session(): void
    {
        $customer = $this->user();
        $product = $this->product($this->business());

        $this->actingAs($customer)->patchJson(route('cart.update', $product), ['cantidad' => 4])
            ->assertNotFound();
        $this->assertNull(session('cart'));
    }

    public function test_inactive_products_categories_businesses_and_owners_cannot_be_added(): void
    {
        $customer = $this->user();
        $business = $this->business();
        $category = Categoria::factory()->create(['emprendimiento_id' => $business->id, 'estado' => false]);
        $product = $this->product($business, ['categoria_id' => $category->id]);

        $this->actingAs($customer)->post(route('cart.store', $product), ['cantidad' => 1])->assertStatus(422);
        $category->update(['estado' => true]);
        $product->update(['estado' => false]);
        $this->post(route('cart.store', $product), ['cantidad' => 1])->assertStatus(422);
        $product->update(['estado' => true]);
        $business->update(['estado' => false]);
        $this->post(route('cart.store', $product), ['cantidad' => 1])->assertStatus(422);
    }

    public function test_checkout_groups_orders_by_business_and_uses_server_snapshots(): void
    {
        $customer = $this->user(attributes: ['name' => 'Nombre Real', 'email' => 'real@example.test', 'telefono' => '7111-2222']);
        $first = $this->product($this->business(), ['precio' => 10]);
        $second = $this->product($this->business(), ['precio' => 7.50]);
        $token = (string) Str::uuid();

        $this->actingAs($customer)->post(route('cart.store', $first), ['cantidad' => 2]);
        $this->post(route('cart.store', $second), ['cantidad' => 3]);
        $response = $this->post(route('checkout.store'), [
            'modalidad_entrega' => 'domicilio', 'direccion' => 'Otra dirección', 'notas' => 'Llamar',
            'token_idempotencia' => $token, 'name' => 'Falso', 'email' => 'falso@example.test', 'telefono' => '0000',
        ]);

        $response->assertRedirectToRoute('checkout.confirmed');
        $this->assertSame(2, Pedido::count());
        $this->assertDatabaseHas('pedidos', ['cliente_id' => $customer->id, 'nombre_cliente' => 'Nombre Real', 'correo_cliente' => 'real@example.test', 'telefono_cliente' => '7111-2222', 'direccion_entrega' => 'Otra dirección']);
        $this->assertEqualsCanonicalizing([20.0, 22.5], Pedido::pluck('subtotal')->map(fn ($value) => (float) $value)->all());
        $this->assertDatabaseCount('detalle_pedidos', 2);
        $this->assertNull(session('cart'));
    }

    public function test_checkout_is_idempotent_and_does_not_duplicate_orders(): void
    {
        $customer = $this->user();
        $product = $this->product($this->business());
        $token = (string) Str::uuid();
        $payload = ['modalidad_entrega' => 'retiro', 'token_idempotencia' => $token];

        $this->actingAs($customer)->post(route('cart.store', $product), ['cantidad' => 1]);
        $this->post(route('checkout.store'), $payload)->assertRedirectToRoute('checkout.confirmed');
        $this->withSession(['cart' => [$product->id => 1]])->post(route('checkout.store'), $payload)->assertRedirectToRoute('checkout.confirmed');

        $this->assertSame(1, Pedido::where('checkout_token', $token)->count());
    }

    public function test_failed_checkout_rolls_back_every_order_and_preserves_cart(): void
    {
        $customer = $this->user();
        $valid = $this->product($this->business());
        $invalidBusiness = $this->business();
        $invalidBusiness->suscripciones()->update(['estado' => 'cancelada']);
        $invalid = $this->product($invalidBusiness);

        $this->actingAs($customer)->post(route('cart.store', $valid), ['cantidad' => 1]);
        $this->post(route('cart.store', $invalid), ['cantidad' => 1]);
        $this->post(route('checkout.store'), ['modalidad_entrega' => 'retiro', 'token_idempotencia' => (string) Str::uuid()])
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('pedidos', 0);
        $this->assertCount(2, session('cart'));
    }
}
