<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Emprendimiento;
use App\Models\Comision;
use App\Models\Pedido;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        // Métricas globales basadas en tus tablas reales
        $totalVentas = Pedido::sum('subtotal');
        $totalComisiones = Comision::sum('total_comision');
        $comisionesPendientes = Comision::where('estado', 'pendiente')->sum('total_comision');
        $emprendimientosCount = Emprendimiento::count();

        // Listado detallado de comisiones
        $comisiones = Comision::with(['emprendimiento.usuario'])->latest()->paginate(10);

        return view('admin.comisiones.index', compact(
            'totalVentas',
            'totalComisiones',
            'comisionesPendientes',
            'emprendimientosCount',
            'comisiones'
        ));
    }
}
