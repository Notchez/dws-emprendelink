<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'rol')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('rol', 20)->default('cliente');
            });
        } elseif (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY rol VARCHAR(20) NULL');
            DB::table('users')->whereNull('rol')->update(['rol' => 'cliente']);
            DB::statement("ALTER TABLE users MODIFY rol VARCHAR(20) NOT NULL DEFAULT 'cliente'");
        } else {
            Schema::table('users', fn (Blueprint $table) => $table->string('rol', 20)->nullable()->change());
            DB::table('users')->whereNull('rol')->update(['rol' => 'cliente']);
            Schema::table('users', fn (Blueprint $table) => $table->string('rol', 20)->default('cliente')->nullable(false)->change());
        }

        if (! Schema::hasColumn('users', 'estado')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('estado')->default(true);
            });
        }

        if (! Schema::hasColumn('users', 'telefono')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('telefono', 20)->nullable();
            });
        }

        if (! Schema::hasColumn('users', 'direccion')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('direccion')->nullable();
            });
        }

        if (! Schema::hasColumn('emprendimientos', 'estado')) {
            Schema::table('emprendimientos', function (Blueprint $table) {
                $table->boolean('estado')->default(true);
            });
        }

        if (! Schema::hasColumn('pedidos', 'cliente_id')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->foreignId('cliente_id')->nullable();
            });
        }

        if (! $this->hasForeignKey('pedidos', 'cliente_id')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->foreign('cliente_id')->references('id')->on('users')->restrictOnDelete();
            });
        }

        if (! Schema::hasColumn('pedidos', 'codigo_publico')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->uuid('codigo_publico')->nullable();
            });
        }

        if (! Schema::hasIndex('pedidos', ['codigo_publico'], 'unique')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->unique('codigo_publico');
            });
        }

        if (! Schema::hasColumn('pedidos', 'token_idempotencia')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->uuid('token_idempotencia')->nullable();
            });
        }

        if (! Schema::hasIndex('pedidos', ['token_idempotencia'], 'unique')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->unique('token_idempotencia');
            });
        }

        if (! Schema::hasColumn('pedidos', 'checkout_token')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->uuid('checkout_token')->nullable();
            });
        }

        if (! Schema::hasColumn('pedidos', 'correo_cliente')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->string('correo_cliente')->nullable();
            });
        }

        if (! Schema::hasColumn('pedidos', 'notas')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->text('notas')->nullable();
            });
        }

        if (! Schema::hasColumn('pedidos', 'plan_id_aplicado')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->foreignId('plan_id_aplicado')->nullable();
            });
        }

        if (! $this->hasForeignKey('pedidos', 'plan_id_aplicado')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->foreign('plan_id_aplicado')->references('id')->on('planes')->restrictOnDelete();
            });
        }

        if (! Schema::hasColumn('pedidos', 'nombre_plan_aplicado')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->string('nombre_plan_aplicado', 100)->nullable();
            });
        }

        if (! Schema::hasColumn('pedidos', 'porcentaje_comision_aplicado')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->decimal('porcentaje_comision_aplicado', 5, 2)->nullable();
            });
        }

        if (! Schema::hasIndex('pedidos', ['checkout_token', 'emprendimiento_id'], 'unique')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->unique(['checkout_token', 'emprendimiento_id'], 'pedidos_checkout_negocio_unico');
            });
        }

        DB::table('pedidos')->orderBy('id')->chunkById(100, function ($orders) {
            foreach ($orders as $order) {
                $subscription = DB::table('suscripciones')
                    ->join('planes', 'planes.id', '=', 'suscripciones.plan_id')
                    ->where('suscripciones.emprendimiento_id', $order->emprendimiento_id)
                    ->orderByRaw("CASE WHEN suscripciones.estado = 'activa' THEN 0 ELSE 1 END")
                    ->orderByDesc('suscripciones.fecha_inicio')
                    ->select('planes.id', 'planes.nombre', 'planes.porcentaje_comision')
                    ->first();

                DB::table('pedidos')->where('id', $order->id)->update([
                    'codigo_publico' => $order->codigo_publico ?: (string) Str::uuid(),
                    'token_idempotencia' => $order->token_idempotencia ?: (string) Str::uuid(),
                    'plan_id_aplicado' => $order->plan_id_aplicado ?: $subscription?->id,
                    'nombre_plan_aplicado' => $order->nombre_plan_aplicado ?: $subscription?->nombre,
                    'porcentaje_comision_aplicado' => $order->porcentaje_comision_aplicado ?? $subscription?->porcentaje_comision,
                ]);
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE pedidos MODIFY codigo_publico CHAR(36) NOT NULL');
            DB::statement('ALTER TABLE pedidos MODIFY token_idempotencia CHAR(36) NOT NULL');
        } else {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->uuid('codigo_publico')->nullable(false)->change();
                $table->uuid('token_idempotencia')->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropUnique('pedidos_checkout_negocio_unico');
            $table->dropConstrainedForeignId('cliente_id');
            $table->dropConstrainedForeignId('plan_id_aplicado');
            $table->dropColumn([
                'codigo_publico', 'token_idempotencia', 'checkout_token', 'correo_cliente', 'notas',
                'nombre_plan_aplicado', 'porcentaje_comision_aplicado',
            ]);
        });
        Schema::table('emprendimientos', fn (Blueprint $table) => $table->dropColumn('estado'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['telefono', 'direccion']));
    }

    private function hasForeignKey(string $table, string $column): bool
    {
        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            if (in_array($column, $foreignKey['columns'], true)) {
                return true;
            }
        }

        return false;
    }
};
