<?php

namespace App\Http\Controllers\Emprendedor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Emprendedor\StoreCategoryRequest;
use App\Http\Requests\Emprendedor\UpdateCategoryRequest;
use App\Models\Categoria;
use App\Services\Emprendedor\CategoryManagementService;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function index(CategoryManagementService $servicio)
    {
        $emprendimiento = auth()->user()->emprendimiento;

        if (!$emprendimiento) {
            abort(404, 'No se encontró tu emprendimiento.');
        }

        Gate::authorize('view', $emprendimiento);

        $categorias = $servicio->listar($emprendimiento);

        return view('emprendedor.categorias.index', compact('categorias'));
    }

    public function store(
        StoreCategoryRequest $request,
        CategoryManagementService $servicio
    ) {
        $emprendimiento = $request->user()->emprendimiento;

        $servicio->crear($emprendimiento, $request->validated());

        return redirect()
            ->route('entrepreneur.categories.index')
            ->with('success', 'Categoría registrada correctamente.');
    }

    public function update(
        UpdateCategoryRequest $request,
        Categoria $categoria,
        CategoryManagementService $servicio
    ) {
        $emprendimiento = $request->user()->emprendimiento;

        $servicio->actualizar(
            $emprendimiento,
            $categoria,
            $request->validated()
        );

        return redirect()
            ->route('entrepreneur.categories.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(
        Categoria $categoria,
        CategoryManagementService $servicio
    ) {
        $usuario = auth()->user();
        $emprendimiento = $usuario->emprendimiento;

        if (!$emprendimiento) {
            abort(404);
        }

        Gate::authorize('update', $emprendimiento);

        $servicio->eliminar($emprendimiento, $categoria);

        return redirect()
            ->route('entrepreneur.categories.index')
            ->with('success', 'Categoría eliminada correctamente.');
    }
}
