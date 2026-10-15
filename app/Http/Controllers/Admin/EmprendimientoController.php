<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Emprendimiento;

class EmprendimientoController extends Controller
{
    // 1. Listar emprendedores con buscador y filtros (RF-06)
    public function index(Request $request)
    {
        // Se agrega 'suscripciones.plan' para traer la relación completa del plan en la tabla
        $query = Emprendimiento::with(['usuario', 'suscripciones.plan', 'comisiones']);

        if ($request->filled('search')) {
            $query->where('nombre', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('estado')) {
            $estadoFiltro = $request->estado;
            $query->whereHas('suscripciones', function ($q) use ($estadoFiltro) {
                $q->where('estado', $estadoFiltro);
            });
        }

        $emprendimientos = $query->paginate(10);

        return view('admin.emprendimientos.index', compact('emprendimientos'));
    }

    // 2. Ver detalles del emprendedor y su información financiera (RF-07)
    public function show($id)
    {
        $emprendimiento = Emprendimiento::with(['usuario', 'comisiones', 'suscripciones.plan'])->findOrFail($id);

        return view('admin.emprendimientos.show', compact('emprendimiento'));
    }

    // 3. Actualizar o suspender el estado de la cuenta mediante la suscripción (RF-08)
    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'estado' => 'required|in:activa,vencida,cancelada', // Valores permitidos por el ENUM de suscripciones
        ]);

        $emprendimiento = Emprendimiento::findOrFail($id);

        // Buscar la suscripción actual o más reciente
        $suscripcion = $emprendimiento->suscripciones()->latest()->first();

        if ($suscripcion) {
            $suscripcion->estado = $request->estado;
            $suscripcion->save();
        } else {
            // Si no cuenta con una suscripción previa, creamos una por defecto
            $emprendimiento->suscripciones()->create([
                'plan_id' => 1, // Plan inicial predeterminado
                'fecha_inicio' => now(),
                'estado' => $request->estado,
            ]);
        }

        return redirect()->route('admin.emprendimientos.show', $id)
            ->with('success', 'El estado del emprendimiento ha sido actualizado exitosamente.');
    }
}
