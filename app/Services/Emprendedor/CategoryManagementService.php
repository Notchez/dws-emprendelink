<?php

namespace App\Services\Emprendedor;

use App\Models\Categoria;
use App\Models\Emprendimiento;
use Illuminate\Support\Facades\DB;

class CategoryManagementService
{
    public function create(Emprendimiento $business, array $data): Categoria
    {
        return $business->categorias()->create($data);
    }

    public function update(Categoria $category, array $data): Categoria
    {
        $category->update($data);

        return $category->refresh();
    }

    public function delete(Categoria $category): void
    {
        DB::transaction(function () use ($category): void {
            $locked = Categoria::query()->lockForUpdate()->findOrFail($category->id);
            $locked->productos()->update(['categoria_id' => null]);
            $locked->delete();
        });
    }
}
