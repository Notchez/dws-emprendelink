<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('comisiones', 'pagada_at')) {
            Schema::table('comisiones', function (Blueprint $table) {
                $table->timestamp('pagada_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Se conserva por seguridad: una instalación nueva puede definirla en su esquema base.
    }
};
