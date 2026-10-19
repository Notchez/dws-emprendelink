<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emprendimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->unique()->constrained('users')->restrictOnDelete();
            $table->string('nombre', 150);
            $table->string('slug', 100)->unique();
            $table->text('descripcion')->nullable();
            $table->string('logo_url', 255)->nullable();
            $table->string('telefono_contacto', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emprendimientos');
    }
};
