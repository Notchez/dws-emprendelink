<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estados_cuenta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emprendimiento_id')->constrained('emprendimientos')->restrictOnDelete();
            $table->tinyInteger('periodo_mes');
            $table->year('periodo_anio');
            $table->decimal('total_adeudado', 10, 2);
            $table->enum('estado_pago', ['pendiente', 'pagado'])->default('pendiente');
            $table->dateTime('fecha_pago')->nullable();
            $table->timestamps();

            $table->unique(['emprendimiento_id', 'periodo_mes', 'periodo_anio'], 'estado_cuenta_periodo_unico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estados_cuenta');
    }
};
