<?php

namespace App\Http\Controllers\Emprendedor;

use App\Http\Controllers\Controller;
use App\Services\Emprendedor\ProductManagementService;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Emprendedor\StoreProductRequest;
use App\Models\Producto;
use App\Http\Requests\Emprendedor\UpdateProductRequest;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    public function index(ProductManagementService $servicio)
    {
        $emprendimiento = auth()->user()->emprendimiento;

        if (!$emprendimiento) {
            abort(404, 'No se encontró tu emprendimiento.');
        }

        Gate::authorize('view', $emprendimiento);

        $productos = $servicio->listar($emprendimiento);

        $categorias = $emprendimiento->categorias()
            ->orderBy('nombre')
            ->get();

        return view('emprendedor.productos.index', compact(
            'productos',
            'categorias'
        ));
    }

    public function store(
    StoreProductRequest $request,
    ProductManagementService $servicio
) {
    $emprendimiento = $request->user()->emprendimiento;

   $servicio->crear(
    $emprendimiento,
    $request->safe()->except('imagen'),
    $request->file('imagen')
);

    return redirect()
        ->route('entrepreneur.products.index')
        ->with('success', 'Producto registrado correctamente.');
}

public function update(
    UpdateProductRequest $request,
    Producto $producto,
    ProductManagementService $servicio
) {
    $emprendimiento = $request->user()->emprendimiento;

    $servicio->actualizar(
    $emprendimiento,
    $producto,
    $request->safe()->except('imagen'),
    $request->file('imagen')
);

    return redirect()
        ->route('entrepreneur.products.index')
        ->with('success', 'Producto actualizado correctamente.');
}

public function destroy(
    Producto $producto,
    ProductManagementService $servicio
): RedirectResponse {
    $usuario = auth()->user();

    $emprendimiento = $usuario->emprendimiento;

    if (!$emprendimiento) {
        abort(404, 'No se encontró tu emprendimiento.');
    }

    Gate::authorize('update', $emprendimiento);

    $servicio->eliminar($emprendimiento, $producto);

    return redirect()
        ->route('entrepreneur.products.index')
        ->with('success', 'Producto eliminado correctamente.');
}

public function cambiarEstado(
    Producto $producto,
    ProductManagementService $servicio
) {
    $usuario = auth()->user();
    $emprendimiento = $usuario->emprendimiento;

    if (!$emprendimiento) {
        abort(404, 'No se encontró tu emprendimiento.');
    }

    Gate::authorize('update', $emprendimiento);

    $producto = $emprendimiento->productos()
        ->findOrFail($producto->id);

    $servicio->cambiarEstado(
        $emprendimiento,
        $producto,
        !$producto->estado
    );

    return redirect()
        ->route('entrepreneur.products.index')
        ->with('success', 'Estado del producto actualizado correctamente.');
}

}

