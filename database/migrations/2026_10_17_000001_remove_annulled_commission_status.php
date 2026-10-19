<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('comisiones')
            ->where('estado', 'anulada')
            ->update([
                'estado' => 'pendiente',
                'pagada_at' => null,
            ]);

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE comisiones MODIFY estado ENUM('pendiente', 'pagada') NOT NULL DEFAULT 'pendiente'",
            );
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE comisiones MODIFY estado ENUM('pendiente', 'pagada', 'anulada') NOT NULL DEFAULT 'pendiente'",
            );
        }
    }
};
