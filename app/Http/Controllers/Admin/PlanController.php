<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;

class PlanController extends Controller
{
    // Listar planes disponibles (RF-09)
    public function index()
    {
        $planes = Plan::all();
        return view('admin.planes.index', compact('planes'));
    }

    // Mostrar formulario para editar un plan (RF-10)
    public function edit($id)
    {
        $plan = Plan::findOrFail($id);
        return view('admin.planes.edit', compact('plan'));
    }

    // Actualizar la información del plan, precios y límites (RF-10)
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'precio_mensual' => 'required|numeric|min:0',
            'porcentaje_comision' => 'required|numeric|min:0|max:100',
            'limite_productos' => 'required|integer|min:0',
        ]);

        $plan = Plan::findOrFail($id);
        $plan->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio_mensual' => $request->precio_mensual,
            'porcentaje_comision' => $request->porcentaje_comision,
            'limite_productos' => $request->limite_productos,
        ]);

        return redirect()->route('admin.planes.index')
            ->with('success', 'El plan ha sido actualizado exitosamente.');
    }
}
