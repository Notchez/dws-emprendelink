<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Categoria;
use App\Models\Emprendimiento;
use App\Models\Pedido;
use App\Models\Plan;
use App\Models\Producto;
use App\Models\Suscripcion;
use App\Models\User;
use App\Services\Commissions\CommissionCalculator;
use App\Services\Commissions\CommissionLedgerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DemoShowcaseSeeder extends Seeder
{
    private const ORDER_CODE_PREFIX = 'f3000000-0000-4000-8000-';

    /** @var array<string, Emprendimiento> */
    private array $businesses = [];

    /** @var array<string, Producto> */
    private array $products = [];

    /** @var array<string, User> */
    private array $customers = [];

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException(
                'DemoShowcaseSeeder solo puede ejecutarse en los entornos local o testing.',
            );
        }

        Carbon::setTestNow();

        try {
            DB::transaction(function (): void {
                $plans = $this->requiredPlans();

                $this->createCustomers();
                $this->createBusinesses($plans);
                $this->createCatalog();
                $this->createHistoricalOrders();
            });
        } finally {
            Carbon::setTestNow();
        }
    }

    /** @return array<string, Plan> */
    private function requiredPlans(): array
    {
        $plans = Plan::query()
            ->whereIn('nombre', ['Básico', 'Estándar', 'Premium'])
            ->where('estado', true)
            ->get()
            ->keyBy('nombre');

        foreach (['Básico', 'Estándar', 'Premium'] as $name) {
            if (! $plans->has($name)) {
                throw new RuntimeException(
                    "Falta el plan activo [{$name}]. Ejecute primero PlanSeeder.",
                );
            }
        }

        return $plans->all();
    }

    private function createCustomers(): void
    {
        $definitions = [
            'cliente-principal' => [
                'name' => 'Cliente Demo',
                'email' => 'cliente@emprendelink.test',
                'telefono' => '7000-0000',
                'direccion' => 'San Salvador',
            ],
            'ana' => [
                'name' => 'Ana Martínez',
                'email' => 'ana.martinez@emprendelink.test',
                'telefono' => '7101-2401',
                'direccion' => 'Santa Tecla, La Libertad',
            ],
            'carlos' => [
                'name' => 'Carlos Hernández',
                'email' => 'carlos.hernandez@emprendelink.test',
                'telefono' => '7102-2402',
                'direccion' => 'Mejicanos, San Salvador',
            ],
            'sofia' => [
                'name' => 'Sofía Ramírez',
                'email' => 'sofia.ramirez@emprendelink.test',
                'telefono' => '7103-2403',
                'direccion' => 'Antiguo Cuscatlán, La Libertad',
            ],
        ];

        foreach ($definitions as $key => $data) {
            $this->customers[$key] = $this->upsertDemoUser($data + [
                'rol' => UserRole::CLIENTE,
            ]);
        }
    }

    /** @param array<string, Plan> $plans */
    private function createBusinesses(array $plans): void
    {
        User::query()
            ->where('email', 'emprendedor@emprendelink.test')
            ->where('rol', UserRole::EMPRENDEDOR->value)
            ->firstOrFail();

        $mainBusiness = Emprendimiento::withTrashed()
            ->where('slug', 'manos-creativas')
            ->first();

        if (! $mainBusiness) {
            throw new RuntimeException(
                'Falta el emprendimiento Manos Creativas. Ejecute primero DemoDataSeeder.',
            );
        }

        if ($mainBusiness->trashed()) {
            $mainBusiness->restore();
        }

        $mainBusiness->update([
            'logo_url' => $this->storeBusinessLogo(
                'manos-creativas',
                'Manos Creativas',
                '#6f42c1',
                '#f0d9ff',
                'MC',
            ),
            'estado' => true,
        ]);

        $this->businesses['manos'] = $mainBusiness;

        $definitions = [
            'cafe' => [
                'owner' => [
                    'name' => 'Miguel Portillo',
                    'email' => 'miguel.portillo@emprendelink.test',
                    'telefono' => '7201-3101',
                    'direccion' => 'Apastepeque, San Vicente',
                    'rol' => UserRole::EMPRENDEDOR,
                ],
                'business' => [
                    'nombre' => 'Café Apastepeque',
                    'slug' => 'cafe-apastepeque',
                    'descripcion' => 'Café salvadoreño de altura, tostado en lotes pequeños.',
                    'telefono_contacto' => '7201-3101',
                    'estado' => true,
                ],
                'plan' => 'Básico',
                'colors' => ['#6f4e37', '#ead7c3'],
                'initials' => 'CA',
            ],
            'sabores' => [
                'owner' => [
                    'name' => 'Rosa Aguilar',
                    'email' => 'rosa.aguilar@emprendelink.test',
                    'telefono' => '7202-3102',
                    'direccion' => 'Suchitoto, Cuscatlán',
                    'rol' => UserRole::EMPRENDEDOR,
                ],
                'business' => [
                    'nombre' => 'Sabores de Mi Tierra',
                    'slug' => 'sabores-de-mi-tierra',
                    'descripcion' => 'Alimentos artesanales preparados con recetas locales.',
                    'telefono_contacto' => '7202-3102',
                    'estado' => true,
                ],
                'plan' => 'Premium',
                'colors' => ['#d35400', '#ffe0b2'],
                'initials' => 'ST',
            ],
            'verde' => [
                'owner' => [
                    'name' => 'Diego Morales',
                    'email' => 'diego.morales@emprendelink.test',
                    'telefono' => '7203-3103',
                    'direccion' => 'Santa Ana, Santa Ana',
                    'rol' => UserRole::EMPRENDEDOR,
                ],
                'business' => [
                    'nombre' => 'Raíces Verdes SV',
                    'slug' => 'raices-verdes-sv',
                    'descripcion' => 'Plantas, cuidado personal y artículos sostenibles.',
                    'telefono_contacto' => '7203-3103',
                    'estado' => true,
                ],
                'plan' => 'Estándar',
                'colors' => ['#198754', '#cdebd9'],
                'initials' => 'RV',
            ],
        ];

        foreach ($definitions as $key => $definition) {
            $owner = $this->upsertDemoUser($definition['owner']);
            $businessData = $definition['business'];
            $businessData['usuario_id'] = $owner->id;
            $businessData['logo_url'] = $this->storeBusinessLogo(
                $businessData['slug'],
                $businessData['nombre'],
                $definition['colors'][0],
                $definition['colors'][1],
                $definition['initials'],
            );

            $business = Emprendimiento::withTrashed()->updateOrCreate(
                ['slug' => $businessData['slug']],
                $businessData,
            );

            if ($business->trashed()) {
                $business->restore();
            }

            $this->assignPlan($business, $plans[$definition['plan']]);
            $this->businesses[$key] = $business;
        }

        // El negocio existente conserva su suscripción; solo se exige que tenga una activa.
        if (! $mainBusiness->suscripciones()->where('estado', 'activa')->exists()) {
            $this->assignPlan($mainBusiness, $plans['Estándar']);
        }
    }

    private function assignPlan(Emprendimiento $business, Plan $plan): void
    {
        $business->suscripciones()
            ->where('estado', 'activa')
            ->where('plan_id', '!=', $plan->id)
            ->update([
                'estado' => 'cancelada',
                'fecha_fin' => '2026-06-30',
            ]);

        Suscripcion::query()->updateOrCreate(
            [
                'emprendimiento_id' => $business->id,
                'plan_id' => $plan->id,
            ],
            [
                'fecha_inicio' => '2026-07-01',
                'fecha_fin' => null,
                'estado' => 'activa',
            ],
        );
    }

    private function createCatalog(): void
    {
        $categories = [
            'manos' => [
                ['Artesanías', 'Piezas hechas a mano con identidad local.'],
                ['Textiles', 'Accesorios textiles elaborados por artesanas salvadoreñas.'],
                ['Decoración', 'Detalles decorativos para hogares y espacios de trabajo.'],
            ],
            'cafe' => [
                ['Café tostado', 'Granos salvadoreños tostados en lotes pequeños.'],
                ['Bebidas', 'Preparaciones y accesorios para disfrutar café.'],
            ],
            'sabores' => [
                ['Conservas', 'Preparaciones artesanales envasadas.'],
                ['Panadería', 'Productos horneados con recetas tradicionales.'],
                ['Condimentos', 'Sabores locales listos para cocinar.'],
            ],
            'verde' => [
                ['Plantas', 'Plantas ornamentales cultivadas localmente.'],
                ['Cuidado personal', 'Alternativas artesanales para el cuidado diario.'],
                ['Hogar sostenible', 'Artículos reutilizables y de bajo impacto.'],
            ],
        ];

        $categoryModels = [];

        foreach ($categories as $businessKey => $definitions) {
            foreach ($definitions as [$name, $description]) {
                $category = Categoria::withTrashed()->updateOrCreate(
                    [
                        'emprendimiento_id' => $this->businesses[$businessKey]->id,
                        'nombre' => $name,
                    ],
                    [
                        'descripcion' => $description,
                        'estado' => true,
                    ],
                );

                if ($category->trashed()) {
                    $category->restore();
                }

                $categoryModels[$businessKey.'|'.$name] = $category;
            }
        }

        $definitions = [
            ['manos-bolso', 'manos', 'Artesanías', 'Bolso artesanal', 'Bolso tejido a mano con asas reforzadas y acabados locales.', 25.00, '#6f42c1', '#f3dcff', 'bag'],
            ['manos-pulsera', 'manos', 'Artesanías', 'Pulsera de semillas', 'Pulsera ajustable elaborada con semillas naturales seleccionadas.', 8.50, '#8e5a2b', '#f5dfc2', 'bracelet'],
            ['manos-cojin', 'manos', 'Textiles', 'Cojín bordado', 'Cojín decorativo con bordado floral y funda lavable.', 22.00, '#c2185b', '#ffd6e7', 'cushion'],
            ['manos-camino', 'manos', 'Textiles', 'Camino de mesa', 'Camino de mesa tejido, ideal para seis puestos.', 28.00, '#b8860b', '#fff0b8', 'textile'],
            ['manos-florero', 'manos', 'Decoración', 'Florero de barro', 'Florero modelado y pintado a mano en tonos tierra.', 18.75, '#a0522d', '#f2d0b5', 'vase'],
            ['cafe-clasico', 'cafe', 'Café tostado', 'Café clásico 340 g', 'Tueste medio con notas de cacao y caramelo.', 9.50, '#6f4e37', '#ead7c3', 'coffee'],
            ['cafe-reserva', 'cafe', 'Café tostado', 'Café reserva 340 g', 'Selección de altura con tueste medio-claro y notas frutales.', 13.25, '#4e342e', '#d7ccc8', 'beans'],
            ['cafe-coldbrew', 'cafe', 'Bebidas', 'Concentrado cold brew', 'Botella de 500 ml, lista para mezclar con agua o leche.', 7.75, '#3e2723', '#bcaaa4', 'bottle'],
            ['cafe-taza', 'cafe', 'Bebidas', 'Taza cafetera artesanal', 'Taza de cerámica de 300 ml hecha por manos locales.', 11.00, '#795548', '#efebe9', 'cup'],
            ['sabores-mermelada', 'sabores', 'Conservas', 'Mermelada de piña', 'Mermelada artesanal de piña con canela, frasco de 250 g.', 5.75, '#f59f00', '#fff3bf', 'jar'],
            ['sabores-chiltepe', 'sabores', 'Conservas', 'Salsa de chiltepe', 'Salsa picante de intensidad media, botella de 150 ml.', 4.50, '#dc3545', '#ffd6d9', 'sauce'],
            ['sabores-semita', 'sabores', 'Panadería', 'Semita de piña', 'Semita artesanal de ocho porciones con relleno de piña.', 8.00, '#c47f17', '#f8e1b8', 'bread'],
            ['sabores-galletas', 'sabores', 'Panadería', 'Galletas de café', 'Paquete de doce galletas crujientes con café salvadoreño.', 6.25, '#9c6644', '#f1d8c5', 'cookies'],
            ['sabores-adobo', 'sabores', 'Condimentos', 'Adobo de la casa', 'Mezcla de especias sin conservantes, bolsa de 120 g.', 3.95, '#d35400', '#ffe0b2', 'spice'],
            ['verde-suculenta', 'verde', 'Plantas', 'Suculenta en maceta', 'Suculenta de fácil cuidado en maceta de barro pintada.', 7.50, '#198754', '#cdebd9', 'plant'],
            ['verde-hierbas', 'verde', 'Plantas', 'Kit de hierbas culinarias', 'Tres macetas con albahaca, orégano y hierbabuena.', 14.00, '#2e7d32', '#dcedc8', 'herbs'],
            ['verde-jabon', 'verde', 'Cuidado personal', 'Jabón de avena', 'Jabón artesanal suave de 100 g con avena y miel.', 4.25, '#a1887f', '#efebe9', 'soap'],
            ['verde-bolsa', 'verde', 'Hogar sostenible', 'Bolsa reutilizable', 'Bolsa plegable de tela resistente para compras diarias.', 6.50, '#00897b', '#c8e6e3', 'tote'],
        ];

        foreach ($definitions as [$key, $businessKey, $categoryName, $name, $description, $price, $primary, $secondary, $symbol]) {
            $business = $this->businesses[$businessKey];
            $imagePath = $this->storeProductIllustration(
                $key,
                $name,
                $business->nombre,
                $primary,
                $secondary,
                $symbol,
            );

            $product = Producto::withTrashed()->updateOrCreate(
                [
                    'emprendimiento_id' => $business->id,
                    'nombre' => $name,
                ],
                [
                    'categoria_id' => $categoryModels[$businessKey.'|'.$categoryName]->id,
                    'descripcion' => $description,
                    'precio' => $price,
                    'imagen_url' => $imagePath,
                    'estado' => true,
                ],
            );

            if ($product->trashed()) {
                $product->restore();
            }

            $this->products[$key] = $product;
        }
    }

    private function createHistoricalOrders(): void
    {
        $orders = [
            ['2026-07-08 10:15:00', 'manos', 'ana', OrderStatus::ENTREGADO, [['manos-bolso', 1], ['manos-pulsera', 2]], 'domicilio'],
            ['2026-07-18 15:30:00', 'cafe', 'carlos', OrderStatus::ENTREGADO, [['cafe-clasico', 2], ['cafe-taza', 1]], 'retiro'],
            ['2026-07-27 09:20:00', 'sabores', 'sofia', OrderStatus::CANCELADO, [['sabores-semita', 1], ['sabores-mermelada', 2]], 'domicilio'],
            ['2026-08-04 11:40:00', 'verde', 'cliente-principal', OrderStatus::ENTREGADO, [['verde-suculenta', 2], ['verde-jabon', 2]], 'retiro'],
            ['2026-08-11 16:10:00', 'manos', 'carlos', OrderStatus::ENTREGADO, [['manos-cojin', 2]], 'domicilio'],
            ['2026-08-19 08:55:00', 'cafe', 'ana', OrderStatus::PENDIENTE, [['cafe-reserva', 1], ['cafe-coldbrew', 1]], 'retiro'],
            ['2026-08-28 13:25:00', 'sabores', 'cliente-principal', OrderStatus::ENTREGADO, [['sabores-chiltepe', 2], ['sabores-adobo', 3]], 'domicilio'],
            ['2026-09-03 10:05:00', 'verde', 'sofia', OrderStatus::ENTREGADO, [['verde-hierbas', 1], ['verde-bolsa', 2]], 'domicilio'],
            ['2026-09-12 14:45:00', 'manos', 'ana', OrderStatus::EN_PREPARACION, [['manos-camino', 1], ['manos-florero', 1]], 'retiro'],
            ['2026-09-21 09:35:00', 'cafe', 'cliente-principal', OrderStatus::ENTREGADO, [['cafe-clasico', 3], ['cafe-coldbrew', 2]], 'domicilio'],
            ['2026-09-29 17:15:00', 'sabores', 'carlos', OrderStatus::ENTREGADO, [['sabores-galletas', 2], ['sabores-mermelada', 1]], 'retiro'],
            ['2026-10-02 12:00:00', 'verde', 'ana', OrderStatus::ENTREGADO, [['verde-suculenta', 1], ['verde-jabon', 3]], 'domicilio'],
            ['2026-10-07 10:30:00', 'manos', 'sofia', OrderStatus::CONFIRMADO, [['manos-bolso', 1], ['manos-cojin', 1]], 'retiro'],
            ['2026-10-12 15:20:00', 'cafe', 'carlos', OrderStatus::ENTREGADO, [['cafe-reserva', 2], ['cafe-taza', 2]], 'domicilio'],
            ['2026-10-18 11:10:00', 'sabores', 'cliente-principal', OrderStatus::ENTREGADO, [['sabores-semita', 2], ['sabores-chiltepe', 1]], 'retiro'],
            ['2026-10-24 16:50:00', 'verde', 'sofia', OrderStatus::CANCELADO, [['verde-hierbas', 2], ['verde-bolsa', 1]], 'domicilio'],
            ['2026-10-30 09:40:00', 'manos', 'ana', OrderStatus::ENTREGADO, [['manos-camino', 1], ['manos-pulsera', 3]], 'domicilio'],
            ['2026-11-02 13:05:00', 'cafe', 'cliente-principal', OrderStatus::ENTREGADO, [['cafe-clasico', 1], ['cafe-reserva', 1], ['cafe-taza', 1]], 'retiro'],
            ['2026-11-04 10:25:00', 'sabores', 'carlos', OrderStatus::LISTO, [['sabores-galletas', 3], ['sabores-adobo', 2]], 'retiro'],
            ['2026-11-06 08:40:00', 'verde', 'sofia', OrderStatus::ENTREGADO, [['verde-suculenta', 2], ['verde-jabon', 2], ['verde-bolsa', 1]], 'domicilio'],
        ];

        foreach ($orders as $index => [$date, $businessKey, $customerKey, $status, $items, $deliveryMode]) {
            $business = $this->businesses[$businessKey];
            $customer = $this->customers[$customerKey];
            $subscription = $business->suscripcionActiva()->with('plan')->firstOrFail();
            $plan = $subscription->plan;
            $placedAt = Carbon::parse($date);
            $number = $index + 1;
            $subtotal = 0.0;

            foreach ($items as [$productKey, $quantity]) {
                $subtotal += (float) $this->products[$productKey]->precio * $quantity;
            }

            $commission = app(CommissionCalculator::class)->calculate(
                $subtotal,
                (float) $plan->porcentaje_comision,
            );

            $order = Pedido::query()->updateOrCreate(
                ['codigo_publico' => $this->stableUuid(self::ORDER_CODE_PREFIX, $number)],
                [
                    'emprendimiento_id' => $business->id,
                    'cliente_id' => $customer->id,
                    'token_idempotencia' => $this->stableUuid('f3100000-0000-4000-8000-', $number),
                    'checkout_token' => $this->stableUuid('f3200000-0000-4000-8000-', $number),
                    'nombre_cliente' => $customer->name,
                    'telefono_cliente' => $customer->telefono,
                    'correo_cliente' => $customer->email,
                    'modalidad_entrega' => $deliveryMode,
                    'direccion_entrega' => $deliveryMode === 'domicilio' ? $customer->direccion : null,
                    'notas' => "Pedido histórico de demostración Fase 3 #{$number}",
                    'estado_actual' => $status,
                    'subtotal' => $subtotal,
                    'comision_plataforma' => $status === OrderStatus::ENTREGADO ? $commission : null,
                    'plan_id_aplicado' => $plan->id,
                    'nombre_plan_aplicado' => $plan->nombre,
                    'porcentaje_comision_aplicado' => $plan->porcentaje_comision,
                ],
            );

            $this->setTimestamps($order, $placedAt);

            foreach ($items as [$productKey, $quantity]) {
                $product = $this->products[$productKey];
                $detail = $order->detallePedidos()->updateOrCreate(
                    ['producto_id' => $product->id],
                    [
                        'cantidad' => $quantity,
                        'precio_unitario' => $product->precio,
                        'subtotal' => (float) $product->precio * $quantity,
                    ],
                );
                $this->setTimestamps($detail, $placedAt);
            }

            $this->createStatusHistory($order, $business->usuario_id, $status, $placedAt);

            if ($status === OrderStatus::ENTREGADO) {
                Carbon::setTestNow($placedAt->copy()->addHours(8));
                app(CommissionLedgerService::class)->createForDeliveredOrder($order->refresh());
                Carbon::setTestNow();
            }
        }
    }

    private function createStatusHistory(
        Pedido $order,
        int $actorId,
        OrderStatus $finalStatus,
        Carbon $placedAt,
    ): void {
        $transitions = match ($finalStatus) {
            OrderStatus::PENDIENTE => [],
            OrderStatus::CONFIRMADO => [
                [OrderStatus::PENDIENTE, OrderStatus::CONFIRMADO],
            ],
            OrderStatus::EN_PREPARACION => [
                [OrderStatus::PENDIENTE, OrderStatus::CONFIRMADO],
                [OrderStatus::CONFIRMADO, OrderStatus::EN_PREPARACION],
            ],
            OrderStatus::LISTO => [
                [OrderStatus::PENDIENTE, OrderStatus::CONFIRMADO],
                [OrderStatus::CONFIRMADO, OrderStatus::EN_PREPARACION],
                [OrderStatus::EN_PREPARACION, OrderStatus::LISTO],
            ],
            OrderStatus::ENTREGADO => [
                [OrderStatus::PENDIENTE, OrderStatus::CONFIRMADO],
                [OrderStatus::CONFIRMADO, OrderStatus::EN_PREPARACION],
                [OrderStatus::EN_PREPARACION, OrderStatus::LISTO],
                [OrderStatus::LISTO, OrderStatus::ENTREGADO],
            ],
            OrderStatus::CANCELADO => [
                [OrderStatus::PENDIENTE, OrderStatus::CONFIRMADO],
                [OrderStatus::CONFIRMADO, OrderStatus::CANCELADO],
            ],
        };

        foreach ($transitions as $index => [$previous, $next]) {
            $history = $order->historialEstados()->updateOrCreate(
                [
                    'estado_anterior' => $previous->value,
                    'estado_nuevo' => $next->value,
                ],
                ['usuario_id' => $actorId],
            );

            $this->setTimestamps($history, $placedAt->copy()->addHours($index + 1));
        }
    }

    /** @param array{name: string, email: string, telefono: string, direccion: string, rol: UserRole} $data */
    private function upsertDemoUser(array $data): User
    {
        $user = User::withTrashed()->updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => 'password',
                'rol' => $data['rol'],
                'estado' => true,
                'telefono' => $data['telefono'],
                'direccion' => $data['direccion'],
            ],
        );

        if ($user->trashed()) {
            $user->restore();
        }

        return $user;
    }

    private function setTimestamps(Model $model, Carbon $date): void
    {
        $model->timestamps = false;
        $model->forceFill([
            'created_at' => $date,
            'updated_at' => $date,
        ])->saveQuietly();
        $model->timestamps = true;
    }

    private function stableUuid(string $prefix, int $number): string
    {
        return $prefix.str_pad((string) $number, 12, '0', STR_PAD_LEFT);
    }

    private function storeBusinessLogo(
        string $slug,
        string $name,
        string $primary,
        string $secondary,
        string $initials,
    ): string {
        $path = "demo-showcase/businesses/{$slug}.svg";
        $safeName = $this->escapeXml($name);
        $safeInitials = $this->escapeXml($initials);

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800" role="img" aria-labelledby="title desc">
  <title id="title">Logo ilustrado de {$safeName}</title>
  <desc id="desc">Ilustración original generada para los datos de demostración de EmprendeLink.</desc>
  <rect width="800" height="800" rx="144" fill="{$secondary}"/>
  <circle cx="400" cy="330" r="220" fill="{$primary}"/>
  <path d="M250 580c48-90 252-90 300 0" fill="none" stroke="{$primary}" stroke-width="34" stroke-linecap="round"/>
  <text x="400" y="390" text-anchor="middle" font-family="Arial, sans-serif" font-size="180" font-weight="700" fill="#fff">{$safeInitials}</text>
  <text x="400" y="690" text-anchor="middle" font-family="Arial, sans-serif" font-size="42" font-weight="700" fill="{$primary}">{$safeName}</text>
</svg>
SVG;

        Storage::disk('public')->put($path, $svg);

        return $path;
    }

    private function storeProductIllustration(
        string $key,
        string $name,
        string $business,
        string $primary,
        string $secondary,
        string $symbol,
    ): string {
        $path = "demo-showcase/products/{$key}.svg";
        $safeName = $this->escapeXml($name);
        $safeBusiness = $this->escapeXml($business);
        $shape = $this->productShape($symbol, $primary);

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 900" role="img" aria-labelledby="title desc">
  <title id="title">{$safeName}</title>
  <desc id="desc">Ilustración original del producto {$safeName}, creado para la demostración de EmprendeLink.</desc>
  <defs>
    <linearGradient id="background" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="{$secondary}"/>
      <stop offset="1" stop-color="#ffffff"/>
    </linearGradient>
    <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
      <feDropShadow dx="0" dy="18" stdDeviation="18" flood-color="#1f2937" flood-opacity=".18"/>
    </filter>
  </defs>
  <rect width="1200" height="900" fill="url(#background)"/>
  <circle cx="600" cy="385" r="285" fill="#fff" opacity=".86"/>
  <g filter="url(#shadow)">{$shape}</g>
  <text x="600" y="760" text-anchor="middle" font-family="Arial, sans-serif" font-size="58" font-weight="700" fill="#212529">{$safeName}</text>
  <text x="600" y="824" text-anchor="middle" font-family="Arial, sans-serif" font-size="32" fill="#495057">{$safeBusiness}</text>
</svg>
SVG;

        Storage::disk('public')->put($path, $svg);

        return $path;
    }

    private function productShape(string $symbol, string $color): string
    {
        return match ($symbol) {
            'bag', 'tote' => "<path d=\"M390 315h420l-34 300H424z\" fill=\"{$color}\"/><path d=\"M485 335c0-165 230-165 230 0\" fill=\"none\" stroke=\"#fff\" stroke-width=\"30\"/>",
            'bracelet' => "<circle cx=\"600\" cy=\"420\" r=\"190\" fill=\"none\" stroke=\"{$color}\" stroke-width=\"72\" stroke-dasharray=\"42 18\"/><circle cx=\"600\" cy=\"420\" r=\"85\" fill=\"#fff\"/>",
            'cushion' => "<rect x=\"390\" y=\"225\" width=\"420\" height=\"390\" rx=\"80\" fill=\"{$color}\"/><path d=\"M500 420h200M600 320v200\" stroke=\"#fff\" stroke-width=\"26\" stroke-linecap=\"round\"/>",
            'textile' => "<path d=\"M390 220h420v400H390z\" fill=\"{$color}\"/><path d=\"M420 275h360M420 365h360M420 455h360M420 545h360\" stroke=\"#fff\" stroke-width=\"20\" stroke-dasharray=\"45 24\"/>",
            'vase' => "<path d=\"M500 210h200l-35 120c120 120 105 285-65 320-170-35-185-200-65-320z\" fill=\"{$color}\"/><path d=\"M525 445c50-55 100 55 150 0\" fill=\"none\" stroke=\"#fff\" stroke-width=\"24\"/>",
            'coffee', 'beans', 'spice' => "<path d=\"M425 220h350l45 410H380z\" fill=\"{$color}\"/><path d=\"M510 390c55-100 145-100 180 0-35 100-125 100-180 0z\" fill=\"#fff\"/><path d=\"M600 310c-45 50-45 110 0 160\" fill=\"none\" stroke=\"{$color}\" stroke-width=\"18\"/>",
            'bottle', 'sauce' => "<path d=\"M540 190h120v105l65 90v245H475V385l65-90z\" fill=\"{$color}\"/><rect x=\"505\" y=\"420\" width=\"190\" height=\"120\" rx=\"22\" fill=\"#fff\" opacity=\".9\"/>",
            'cup' => "<path d=\"M390 300h360v250c0 75-75 110-180 110s-180-35-180-110z\" fill=\"{$color}\"/><path d=\"M750 345h65c110 0 110 165 0 165h-65\" fill=\"none\" stroke=\"{$color}\" stroke-width=\"36\"/><path d=\"M505 235c-35-55 35-70 0-125M600 235c-35-55 35-70 0-125M695 235c-35-55 35-70 0-125\" fill=\"none\" stroke=\"{$color}\" stroke-width=\"18\" stroke-linecap=\"round\"/>",
            'jar' => "<rect x=\"430\" y=\"270\" width=\"340\" height=\"350\" rx=\"65\" fill=\"{$color}\"/><rect x=\"410\" y=\"215\" width=\"380\" height=\"90\" rx=\"22\" fill=\"#495057\"/><circle cx=\"600\" cy=\"450\" r=\"95\" fill=\"#fff\" opacity=\".9\"/>",
            'bread' => "<path d=\"M360 470c0-175 120-250 240-250s240 75 240 250c0 105-85 165-240 165s-240-60-240-165z\" fill=\"{$color}\"/><path d=\"M470 305l60 95M590 270l45 105M710 305l-55 95\" stroke=\"#fff\" stroke-width=\"25\" stroke-linecap=\"round\"/>",
            'cookies' => "<circle cx=\"510\" cy=\"390\" r=\"175\" fill=\"{$color}\"/><circle cx=\"680\" cy=\"475\" r=\"155\" fill=\"{$color}\"/><g fill=\"#4e342e\"><circle cx=\"460\" cy=\"335\" r=\"18\"/><circle cx=\"555\" cy=\"420\" r=\"20\"/><circle cx=\"500\" cy=\"500\" r=\"16\"/><circle cx=\"650\" cy=\"420\" r=\"17\"/><circle cx=\"735\" cy=\"500\" r=\"19\"/></g>",
            'plant', 'herbs' => "<path d=\"M485 455h230l-35 185H520z\" fill=\"{$color}\"/><path d=\"M600 470V235M600 330c-125-10-145-115-135-155 100 0 145 55 135 155M600 380c125-10 145-115 135-155-100 0-145 55-135 155\" fill=\"none\" stroke=\"{$color}\" stroke-width=\"30\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>",
            'soap' => "<rect x=\"380\" y=\"265\" width=\"440\" height=\"330\" rx=\"125\" fill=\"{$color}\"/><path d=\"M505 430c55-85 135-85 190 0-55 85-135 85-190 0z\" fill=\"#fff\" opacity=\".8\"/>",
            default => "<circle cx=\"600\" cy=\"420\" r=\"210\" fill=\"{$color}\"/>",
        };
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
