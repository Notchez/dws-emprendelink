<?php
namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Emprendimiento;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        return $this->catalog($request);
    }

    public function business(Request $request, Emprendimiento $emprendimiento): View
    {
        $emprendimiento->loadMissing('usuario');

        abort_unless($emprendimiento->estado && $emprendimiento->usuario?->estado, 404);

        return $this->catalog($request, $emprendimiento);
    }

    public function show(Producto $producto): View
    {
        $producto->loadMissing(['emprendimiento.usuario', 'categoria']);

        abort_unless(
            $producto->estado
                && $producto->emprendimiento?->estado
                && $producto->emprendimiento?->usuario?->estado
                && (! $producto->categoria || $producto->categoria->estado),
            404,
        );

        return view('storefront.product', ['producto' => $producto]);
    }

    private function catalog(Request $request, ?Emprendimiento $emprendimiento = null): View
    {
        $hasBusinessFilter = $emprendimiento !== null || $request->filled('emprendimiento');
        $filteredBusinessId = $emprendimiento?->id;

        if (! $filteredBusinessId && $request->filled('emprendimiento')) {
            $filteredBusinessId = Emprendimiento::query()
                ->where('slug', $request->string('emprendimiento')->toString())
                ->where('estado', true)
                ->whereHas('usuario', fn ($user) => $user->where('estado', true))
                ->value('id');
        }

        $products = Producto::with(['emprendimiento', 'categoria'])
            ->where('estado', true)
            ->where(fn ($query) => $query
                ->whereNull('categoria_id')
                ->orWhereHas('categoria', fn ($category) => $category->where('estado', true)))
            ->whereHas('emprendimiento', fn ($query) => $query
                ->where('estado', true)
                ->whereHas('usuario', fn ($user) => $user->where('estado', true)))
            ->when($emprendimiento, fn ($query) => $query->where('emprendimiento_id', $emprendimiento->id))
            ->when(! $emprendimiento && $request->filled('emprendimiento'), fn ($query) => $query
                ->whereHas('emprendimiento', fn ($business) => $business->where('slug', $request->string('emprendimiento')->toString())))
            ->when($request->filled('search'), fn ($query) => $query->where(fn ($search) => $search
                ->where('nombre', 'like', '%'.$request->string('search')->toString().'%')
                ->orWhere('descripcion', 'like', '%'.$request->string('search')->toString().'%')))
            ->when($request->filled('categoria'), fn ($query) => $query->where('categoria_id', $request->integer('categoria')));

        $categories = Categoria::where('estado', true)
            ->whereHas('productos', fn ($query) => $query
                ->where('estado', true)
                ->when($hasBusinessFilter, fn ($products) => $products->where('emprendimiento_id', $filteredBusinessId ?? 0))
                ->whereHas('emprendimiento', fn ($business) => $business
                    ->where('estado', true)
                    ->whereHas('usuario', fn ($user) => $user->where('estado', true))))
            ->orderBy('nombre')
            ->get();

        return view('storefront.catalog', [
            'productos' => $products->latest()->paginate(12)->withQueryString(),
            'categorias' => $categories,
            'emprendimiento' => $emprendimiento,
        ]);
    }
}
