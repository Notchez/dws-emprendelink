<?php

namespace App\Http\Controllers;

use App\Models\Emprendimiento;
use App\Models\Producto;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request, string $slug)
    {
        $emprendimiento = Emprendimiento::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $categorias = $emprendimiento->categorias()
            ->orderBy('nombre')
            ->get();

        $productos = $emprendimiento->productos()
            ->where('estado', true)
            ->with('categoria')
            ->when($request->filled('buscar'), function ($consulta) use ($request) {
                $buscar = $request->input('buscar');

                $consulta->where(function ($q) use ($buscar) {
                    $q->where('nombre', 'like', '%' . $buscar . '%')
                        ->orWhere('descripcion', 'like', '%' . $buscar . '%');
                });
            })
            ->when($request->filled('categoria'), function ($consulta) use ($request) {
                $consulta->where('categoria_id', $request->input('categoria'));
            })
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        return view('catalogo.index', compact(
            'emprendimiento',
            'categorias',
            'productos'
        ));
    }

    public function show(string $slug, Producto $producto)
    {
        $emprendimiento = Emprendimiento::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $producto = $emprendimiento->productos()
            ->where('estado', true)
            ->with('categoria')
            ->findOrFail($producto->id);

        return view('catalogo.show', compact(
            'emprendimiento',
            'producto'
        ));
    }
}
