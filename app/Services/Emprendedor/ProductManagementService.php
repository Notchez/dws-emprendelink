<?php

namespace App\Services\Emprendedor;

use App\Models\Emprendimiento;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;

class ProductManagementService
{
    public function listar(Emprendimiento $emprendimiento): Collection
    {
        return $emprendimiento->productos()
            ->with('categoria')
            ->orderBy('nombre')
            ->get();
    }

    public function crear(
    Emprendimiento $emprendimiento,
    array $datos,
    ?UploadedFile $imagen = null
): \App\Models\Producto {

    if (!empty($datos['categoria_id'])) {
        $emprendimiento->categorias()
            ->findOrFail($datos['categoria_id']);
    }

    $rutaImagen = null;

    if ($imagen !== null) {
        $rutaImagen = $imagen->store(
            'productos/imagenes',
            'public'
        );
    }

    try {
        return $emprendimiento->productos()->create([
            'categoria_id' => $datos['categoria_id'] ?? null,
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'precio' => $datos['precio'],
            'imagen_url' => $rutaImagen,
            'estado' => false,
        ]);
    } catch (\Throwable $error) {
        if ($rutaImagen !== null) {
            \Illuminate\Support\Facades\Storage::disk('public')
                ->delete($rutaImagen);
        }

        throw $error;
    }
}


public function actualizar(
    Emprendimiento $emprendimiento,
    Producto $producto,
    array $datos,
    ?UploadedFile $imagen = null
): Producto {

    $producto = $emprendimiento->productos()
        ->findOrFail($producto->id);

    if (!empty($datos['categoria_id'])) {
        $emprendimiento->categorias()
            ->findOrFail($datos['categoria_id']);
    }

    $rutaAnterior = $producto->imagen_url;
    $rutaNueva = null;

    if ($imagen !== null) {
        $rutaNueva = $imagen->store(
            'productos/imagenes',
            'public'
        );
    }

    try {
        $producto->update([
            'categoria_id' => $datos['categoria_id'] ?? null,
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'precio' => $datos['precio'],
            'imagen_url' => $rutaNueva ?? $rutaAnterior,
        ]);
    } catch (\Throwable $error) {
        if ($rutaNueva !== null) {
            Storage::disk('public')->delete($rutaNueva);
        }

        throw $error;
    }

    if ($rutaNueva !== null && $rutaAnterior !== null) {
        Storage::disk('public')->delete($rutaAnterior);
    }

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

public function cambiarEstado(
    Emprendimiento $emprendimiento,
    Producto $producto,
    bool $activar
): Producto {
    return DB::transaction(function () use (
        $emprendimiento,
        $producto,
        $activar
    ) {
        // Bloqueamos el emprendimiento para serializar
        // las activaciones de sus productos.
        Emprendimiento::query()
            ->whereKey($emprendimiento->id)
            ->lockForUpdate()
            ->firstOrFail();

        $producto = $emprendimiento->productos()
            ->findOrFail($producto->id);

        if (!$activar) {
            $producto->update(['estado' => false]);

            return $producto->refresh();
        }

        if ($producto->estado) {
            return $producto;
        }

        $suscripcion = $emprendimiento->suscripciones()
            ->where('estado', 'activa')
            ->whereDate('fecha_inicio', '<=', today())
            ->where(function ($consulta) {
                $consulta->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', today());
            })
            ->with('plan')
            ->orderByDesc('fecha_inicio')
            ->first();

        if (!$suscripcion || !$suscripcion->plan) {
            throw ValidationException::withMessages([
                'estado' => 'No tienes una suscripción activa y vigente.',
            ]);
        }

        $limite = $suscripcion->plan->limite_productos;

        $activos = $emprendimiento->productos()
            ->where('estado', true)
            ->count();

        if ($activos >= $limite) {
            throw ValidationException::withMessages([
                'estado' => 'Has alcanzado el límite de productos activos de tu plan.',
            ]);
        }

        $producto->update(['estado' => true]);

        return $producto->refresh();
    });
}


}
