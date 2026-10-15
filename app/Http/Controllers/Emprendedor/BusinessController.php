<?php

namespace App\Http\Controllers\Emprendedor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Emprendedor\UpdateBusinessRequest;
use App\Models\Emprendimiento;
use App\Services\Emprendedor\BusinessProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BusinessController extends Controller
{
    public function show()
    {
        $usuario = auth()->user();

        $emprendimiento = $usuario->emprendimiento;

        if (!$emprendimiento) {
            abort(404, 'No se encontró un emprendimiento asociado a este usuario.');
        }

        Gate::authorize('view', $emprendimiento);

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

$plan = $suscripcion?->plan;

$productosActivos = $emprendimiento->productos()
    ->where('estado', true)
    ->count();

$limiteProductos = $plan?->limite_productos;

$productosDisponibles = $limiteProductos !== null
    ? max(0, $limiteProductos - $productosActivos)
    : 0;

return view('emprendedor.negocio.show', compact(
    'emprendimiento',
    'plan',
    'productosActivos',
    'limiteProductos',
    'productosDisponibles'
));
    }

    public function update(
        UpdateBusinessRequest $request,
        Emprendimiento $emprendimiento,
        BusinessProfileService $servicio
    ) {
        $datos = $request->safe()->except(['logo']);

        $servicio->actualizar(
            $emprendimiento,
            $datos,
            $request->file('logo')
        );

        return redirect()
           ->route('entrepreneur.business.show')
           ->with('success', 'Los datos del emprendimiento se actualizaron correctamente.');
    }
}