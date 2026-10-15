<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Emprendimiento;
use App\Models\Comision;

class DashboardController extends Controller
{
    // 1. Pantalla de Inicio / Dashboard Principal (Mockup)
    public function index()
    {
        // Emprendedores activos a través de sus suscripciones
        $emprendedoresActivos = Emprendimiento::whereHas('suscripciones', function ($q) {
            $q->where('estado', 'activa');
        })->count();

        // Pedidos del mes actual
        $pedidosDelMes = Pedido::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Comisiones generadas y pendientes
        $comisionesGeneradas = Comision::sum('total_comision');
        $pagosPendientes = Comision::where('estado', 'pendiente')->sum('total_comision');

        // Pedidos recientes (últimos 5)
        $pedidosRecientes = Pedido::with(['emprendimiento.usuario'])->latest()->take(5)->get();

        // Conteo de pedidos por su columna 'estado_actual'
        $pedidosPorEstado = [
            'pendientes' => Pedido::where('estado_actual', 'pendiente')->count(),
            'confirmados' => Pedido::where('estado_actual', 'confirmado')->count(),
            'en_preparacion' => Pedido::where('estado_actual', 'en_preparacion')->count(),
            'listos' => Pedido::where('estado_actual', 'listo')->count(),
            'entregados' => Pedido::where('estado_actual', 'entregado')->count(),
        ];

        return view('admin.dashboard', compact(
            'emprendedoresActivos',
            'pedidosDelMes',
            'comisionesGeneradas',
            'pagosPendientes',
            'pedidosRecientes',
            'pedidosPorEstado'
        ));
    }

    // 2. Listar todos los pedidos de la plataforma para auditoría (Módulo 4)
    public function indexPedidos(Request $request)
    {
        $query = Pedido::with(['emprendimiento']);

        // Filtrar por estado actual si se envía
        if ($request->filled('estado')) {
            $query->where('estado_actual', $request->estado);
        }

        // Filtrar por emprendimiento si se selecciona
        if ($request->filled('emprendimiento_id')) {
            $query->where('emprendimiento_id', $request->emprendimiento_id);
        }

        $pedidos = $query->latest()->paginate(10);
        $emprendimientos = Emprendimiento::all();

        return view('admin.pedidos.index', compact('pedidos', 'emprendimientos'));
    }

    // 3. Ver el detalle completo de un pedido específico
    public function showPedido($id)
    {
        $pedido = Pedido::with(['emprendimiento.usuario', 'detallePedidos.producto'])->findOrFail($id);

        return view('admin.pedidos.show', compact('pedido'));
    }
}
