<?php

namespace App\Services\Emprendedor;

use App\Models\Emprendimiento;
use Illuminate\Database\Eloquent\Collection;

class ProductManagementService
{
    public function listar(Emprendimiento $emprendimiento): Collection
    {
        return $emprendimiento->productos()
            ->with('categoria')
            ->orderBy('nombre')
            ->get();
    }

    public function crear(Emprendimiento $emprendimiento, array $datos): \App\Models\Producto
{
    if (!empty($datos['categoria_id'])) {
        $emprendimiento->categorias()
            ->findOrFail($datos['categoria_id']);
    }

    return $emprendimiento->productos()->create([
        'categoria_id' => $datos['categoria_id'] ?? null,
        'nombre' => $datos['nombre'],
        'descripcion' => $datos['descripcion'] ?? null,
        'precio' => $datos['precio'],
        'imagen_url' => null,
        'estado' => false,
    ]);
}


public function actualizar(
    Emprendimiento $emprendimiento,
    \App\Models\Producto $producto,
    array $datos
): \App\Models\Producto {

    $producto = $emprendimiento->productos()
        ->findOrFail($producto->id);

    if (!empty($datos['categoria_id'])) {
        $emprendimiento->categorias()
            ->findOrFail($datos['categoria_id']);
    }

    $producto->update([
        'categoria_id' => $datos['categoria_id'] ?? null,
        'nombre' => $datos['nombre'],
        'descripcion' => $datos['descripcion'] ?? null,
        'precio' => $datos['precio'],
    ]);

    return $producto->refresh();
}


public function eliminar(
    Emprendimiento $emprendimiento,
    \App\Models\Producto $producto
): void {

    $producto = $emprendimiento->productos()
        ->findOrFail($producto->id);

    $producto->delete();
}


}
