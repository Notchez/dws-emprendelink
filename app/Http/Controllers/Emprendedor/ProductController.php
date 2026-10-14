<?php

namespace App\Http\Controllers\Emprendedor;

use App\Http\Controllers\Controller;
use App\Services\Emprendedor\ProductManagementService;
use Illuminate\Support\Facades\Gate;

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
}

