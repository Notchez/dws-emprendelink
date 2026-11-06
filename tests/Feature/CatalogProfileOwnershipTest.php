<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Categoria;
use App\Models\DetallePedido;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Concerns\CreatesDomainData;
use Tests\TestCase;

class CatalogProfileOwnershipTest extends TestCase
{
    use CreatesDomainData, RefreshDatabase;

    public function test_public_catalog_only_exposes_active_products_from_active_entities(): void
    {
        $visibleBusiness = $this->business(attributes: ['nombre' => 'Visible']);
        $visible = $this->product($visibleBusiness, ['nombre' => 'Producto visible']);
        $hiddenProduct = $this->product($visibleBusiness, ['nombre' => 'Producto desactivado', 'estado' => false]);
        $hiddenBusiness = $this->business(attributes: ['estado' => false]);
        $this->product($hiddenBusiness, ['nombre' => 'Negocio oculto']);

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee($visible->nombre)
            ->assertDontSee($hiddenProduct->nombre)
            ->assertDontSee('Negocio oculto');
    }

    public function test_business_catalog_route_and_query_parameter_filter_products_and_categories(): void
    {
        $firstBusiness = $this->business(attributes: ['nombre' => 'Negocio Uno']);
        $secondBusiness = $this->business(attributes: ['nombre' => 'Negocio Dos']);
        $first = $this->product($firstBusiness, ['nombre' => 'Solo primero']);
        $second = $this->product($secondBusiness, ['nombre' => 'Solo segundo']);

        $this->get(route('catalog.business', $firstBusiness))
            ->assertOk()->assertSee($first->nombre)->assertDontSee($second->nombre);
        $this->get(route('catalog.index', ['emprendimiento' => $secondBusiness->slug]))
            ->assertOk()->assertSee($second->nombre)->assertDontSee($first->nombre);
    }

    public function test_customer_profile_updates_allowed_fields_but_never_email_or_role(): void
    {
        $customer = $this->user(attributes: ['email' => 'original@example.test']);
        $this->actingAs($customer)->put(route('profile.update'), [
            'name' => 'Nombre Actualizado', 'telefono' => '7999-0000', 'direccion' => 'Nueva dirección',
            'email' => 'attacker@example.test', 'rol' => 'admin', 'estado' => false,
        ])->assertRedirect();

        $customer->refresh();
        $this->assertSame('Nombre Actualizado', $customer->name);
        $this->assertSame('original@example.test', $customer->email);
        $this->assertSame(UserRole::CLIENTE, $customer->rol);
        $this->assertTrue($customer->estado);
    }

    public function test_entrepreneur_category_crud_is_scoped_to_own_business(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);
        $this->actingAs($owner)->post(route('entrepreneur.categories.store'), [
            'nombre' => 'Propia', 'descripcion' => 'Categoría propia', 'estado' => true,
        ])->assertRedirect();
        $category = Categoria::where('emprendimiento_id', $business->id)->where('nombre', 'Propia')->firstOrFail();
        $product = $this->product($business, ['categoria_id' => $category->id]);

        $this->put(route('entrepreneur.categories.update', $category), [
            'nombre' => 'Actualizada', 'estado' => false,
        ])->assertRedirect();
        $this->assertDatabaseHas('categorias', ['id' => $category->id, 'nombre' => 'Actualizada', 'estado' => false]);

        $other = Categoria::factory()->create(['emprendimiento_id' => $this->business()->id]);
        $this->put(route('entrepreneur.categories.update', $other), ['nombre' => 'Intrusión', 'estado' => true])->assertForbidden();
        $this->delete(route('entrepreneur.categories.destroy', $other))->assertForbidden();

        $this->delete(route('entrepreneur.categories.destroy', $category))->assertRedirect();
        $this->assertSoftDeleted('categorias', ['id' => $category->id]);
        $this->assertDatabaseHas('productos', ['id' => $product->id, 'categoria_id' => null]);
    }

    public function test_product_deletion_is_scoped_soft_deletes_and_preserves_order_history(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);
        $product = $this->product($business, ['nombre' => 'Producto histórico']);
        $order = $this->order($business, attributes: ['estado_actual' => OrderStatus::ENTREGADO]);
        DetallePedido::create([
            'pedido_id' => $order->id,
            'producto_id' => $product->id,
            'cantidad' => 1,
            'precio_unitario' => 100,
            'subtotal' => 100,
        ]);

        $otherProduct = $this->product($this->business(), ['nombre' => 'Ajeno']);
        $this->actingAs($owner)
            ->delete(route('entrepreneur.products.destroy', $otherProduct))
            ->assertForbidden();

        $this->delete(route('entrepreneur.products.destroy', $product))
            ->assertRedirectToRoute('entrepreneur.products.index');

        $this->assertSoftDeleted('productos', ['id' => $product->id]);
        $this->assertDatabaseHas('productos', ['id' => $product->id, 'estado' => false]);
        $this->assertSame('Producto histórico', $order->fresh()->detallePedidos()->firstOrFail()->producto->nombre);
    }

    public function test_entrepreneur_cannot_edit_another_business_and_invalid_image_is_rejected(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);
        $otherBusiness = $this->business();

        $this->actingAs($owner)->put(route('entrepreneur.business.update', $otherBusiness), [
            'nombre' => 'Intrusión',
        ])->assertForbidden();

        $this->post(route('entrepreneur.products.store'), [
            'nombre' => 'Archivo inválido', 'precio' => 10, 'estado' => true,
            'imagen' => UploadedFile::fake()->create('script.php', 2, 'text/x-php'),
        ])->assertSessionHasErrors('imagen');
        $this->assertDatabaseMissing('productos', ['emprendimiento_id' => $business->id, 'nombre' => 'Archivo inválido']);
    }

    public function test_catalog_forms_match_backend_contracts_and_category_modals_have_valid_table_placement(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);
        Categoria::factory()->create(['emprendimiento_id' => $business->id]);

        $categories = $this->actingAs($owner)->get(route('entrepreneur.categories.index'))->assertOk();
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$categories->getContent());
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);

        $this->assertSame(0, $xpath->query('//tbody//div[contains(concat(" ", normalize-space(@class), " "), " modal ")]')?->length);
        $categories->assertDontSee('fas fa', false)->assertSee('bi bi-pencil', false);

        $businessForm = $this->get(route('entrepreneur.business.show'))->assertOk();
        $businessForm->assertDontSee('codigo_area', false)
            ->assertDontSee('formatPhone', false)
            ->assertSee('maxlength="2000"', false)
            ->assertSee('maxlength="20"', false);

        $this->get(route('entrepreneur.products.create'))
            ->assertOk()
            ->assertSee('maxlength="2000"', false);
    }

    public function test_guest_and_non_customers_cannot_mutate_the_cart(): void
    {
        $product = $this->product($this->business());
        $this->post(route('cart.store', $product), ['cantidad' => 1])->assertRedirectToRoute('login');

        $admin = $this->user(UserRole::ADMIN);
        $this->actingAs($admin)->post(route('cart.store', $product), ['cantidad' => 1])->assertForbidden();
    }
}
