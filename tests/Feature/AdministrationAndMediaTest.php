<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Exceptions\ProductLimitReachedException;
use App\Models\Plan;
use App\Services\Admin\BusinessAdministrationService;
use App\Services\Auth\EmprendedorRegistrationService;
use App\Services\Emprendedor\BusinessProfileService;
use App\Services\Emprendedor\ProductCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesDomainData;
use Tests\TestCase;

class AdministrationAndMediaTest extends TestCase
{
    use CreatesDomainData, RefreshDatabase;

    public function test_admin_registration_atomically_creates_entrepreneur_business_and_subscription(): void
    {
        $plan = Plan::factory()->create();
        $user = app(EmprendedorRegistrationService::class)->register([
            'name' => 'Emprendedor', 'email' => 'owner@example.test', 'password' => 'password123',
            'telefono' => '7000-0000', 'nombre_emprendimiento' => 'Negocio QA', 'plan_id' => $plan->id,
        ]);

        $this->assertSame(UserRole::EMPRENDEDOR, $user->rol);
        $this->assertSame('Negocio QA', $user->emprendimiento->nombre);
        $this->assertSame($plan->id, $user->emprendimiento->suscripcionActiva->plan_id);
    }

    public function test_admin_cannot_assign_an_inactive_plan(): void
    {
        $admin = $this->user(UserRole::ADMIN);
        $business = $this->business();
        $inactive = Plan::factory()->create(['estado' => false]);

        $this->actingAs($admin)->patch(route('admin.emprendimientos.plan', $business), ['plan_id' => $inactive->id])
            ->assertSessionHasErrors('plan_id');
    }

    public function test_admin_can_activate_and_deactivate_other_users_but_not_self(): void
    {
        $admin = $this->user(UserRole::ADMIN);
        $customer = $this->user();
        $this->actingAs($admin)->patch(route('admin.users.status', $customer), ['estado' => false])->assertRedirect();
        $this->assertFalse($customer->refresh()->estado);
        $this->patch(route('admin.users.status', $customer), ['estado' => true])->assertRedirect();
        $this->assertTrue($customer->refresh()->estado);
        $this->patch(route('admin.users.status', $admin), ['estado' => false])->assertForbidden();
    }

    public function test_admin_main_queries_load_successfully(): void
    {
        $admin = $this->user(UserRole::ADMIN);
        $this->business();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.users.index'))->assertOk();
        $this->get(route('admin.emprendimientos.index'))->assertOk();
        $this->get(route('admin.pedidos.index'))->assertOk();
        $this->get(route('admin.comisiones.index'))->assertOk();
    }

    public function test_assigning_a_new_plan_closes_the_previous_subscription(): void
    {
        $business = $this->business();
        $newPlan = Plan::factory()->create();
        app(BusinessAdministrationService::class)->assignPlan($business, $newPlan);

        $this->assertSame(1, $business->suscripciones()->where('estado', 'activa')->count());
        $this->assertSame($newPlan->id, $business->suscripcionActiva()->first()->plan_id);
        $this->assertSame(1, $business->suscripciones()->where('estado', 'cancelada')->count());
    }

    public function test_product_limit_is_enforced_for_creation_and_reactivation(): void
    {
        $plan = Plan::factory()->create(['limite_productos' => 1]);
        $business = $this->business(plan: $plan);
        $service = app(ProductCatalogService::class);
        $service->create($business, ['nombre' => 'Primero', 'precio' => 10, 'estado' => true]);

        $this->expectException(ProductLimitReachedException::class);
        $service->create($business, ['nombre' => 'Segundo', 'precio' => 10, 'estado' => true]);
    }

    public function test_product_image_replacement_removes_old_file_but_deactivation_keeps_new_file(): void
    {
        Storage::fake('public');
        $business = $this->business();
        $service = app(ProductCatalogService::class);
        $product = $service->create($business, ['nombre' => 'Producto', 'precio' => 15, 'estado' => true], $this->fakeImage('old.jpg'));
        $oldPath = $product->imagen_url;
        Storage::disk('public')->assertExists($oldPath);

        $product = $service->update($product, ['nombre' => 'Producto', 'precio' => 15, 'estado' => true], $this->fakeImage('new.png'));
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($product->imagen_url);

        $newPath = $product->imagen_url;
        $service->setStatus($product, false);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_business_logo_replacement_removes_the_previous_file(): void
    {
        Storage::fake('public');
        $business = $this->business();
        $service = app(BusinessProfileService::class);
        $business = $service->update($business, ['nombre' => $business->nombre], $this->fakeImage('first.jpg'));
        $oldPath = $business->logo_url;

        $business = $service->update($business, ['nombre' => $business->nombre], $this->fakeImage('second.jpg'));
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($business->logo_url);
    }

    public function test_entrepreneur_cannot_edit_another_business_product(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $this->business($owner);
        $otherProduct = $this->product($this->business());

        $this->actingAs($owner)->put(route('entrepreneur.products.update', $otherProduct), [
            'nombre' => 'Intento', 'precio' => 10, 'estado' => true,
        ])->assertForbidden();
    }

    private function fakeImage(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2Q==', true),
        );
    }
}
