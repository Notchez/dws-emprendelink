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

        return view('emprendedor.negocio.show', compact('emprendimiento'));
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