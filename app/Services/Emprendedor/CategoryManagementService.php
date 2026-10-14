<?php

namespace App\Services\Emprendedor;

use App\Models\Categoria;
use App\Models\Emprendimiento;
use Illuminate\Database\Eloquent\Collection;

class CategoryManagementService
{
    public function listar(Emprendimiento $emprendimiento): Collection
    {
        return $emprendimiento->categorias()
            ->orderBy('nombre')
            ->get();
    }

    public function crear(Emprendimiento $emprendimiento, array $datos): Categoria
    {
        return $emprendimiento->categorias()->create([
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'estado' => true,
        ]);
    }

    public function actualizar(
        Emprendimiento $emprendimiento,
        Categoria $categoria,
        array $datos
    ): Categoria {
        $categoria = $emprendimiento->categorias()
            ->findOrFail($categoria->id);

        $categoria->update([
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
        ]);

        return $categoria->refresh();
    }

    public function eliminar(
        Emprendimiento $emprendimiento,
        Categoria $categoria
    ): void {
        $categoria = $emprendimiento->categorias()
            ->findOrFail($categoria->id);

        $categoria->delete();
    }
}

