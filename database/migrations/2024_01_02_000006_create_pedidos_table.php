<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emprendimiento_id')->constrained('emprendimientos')->restrictOnDelete();
            $table->string('nombre_cliente', 100);
            $table->string('telefono_cliente', 20);
            $table->enum('modalidad_entrega', ['domicilio', 'retiro']);
            $table->text('direccion_entrega')->nullable();
            $table->enum('estado_actual', ['Pendiente', 'Confirmado', 'En preparación', 'Listo', 'Entregado', 'Cancelado'])->default('Pendiente');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('comision_plataforma', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
