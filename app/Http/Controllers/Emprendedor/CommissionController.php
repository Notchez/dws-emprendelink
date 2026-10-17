<?php

namespace App\Http\Controllers\Emprendedor;

use App\Enums\CommissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Emprendedor\FilterCommissionMonthRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CommissionController extends Controller
{
    public function index(FilterCommissionMonthRequest $request): View
    {
        $month = $request->validated('mes') ?: now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m-d', $month.'-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $query = $request->user()->emprendimiento->comisiones()
            ->whereBetween('created_at', [$start, $end]);

        return view('entrepreneur.commissions', [
            'mes' => $month,
            'comisiones' => (clone $query)->with('pedido')->latest()->paginate(15)->withQueryString(),
            'cargosMes' => (clone $query)->sum('total_comision'),
            'pendienteMes' => (clone $query)->where('estado', CommissionStatus::PENDIENTE->value)->sum('total_comision'),
            'pagadoMes' => (clone $query)->where('estado', CommissionStatus::PAGADA->value)->sum('total_comision'),
        ]);
    }

    public function sales(Request $request): View
    {
        $q = $request->user()->emprendimiento->pedidos()->with('cliente')->where('estado_actual', 'Entregado')->when($request->desde, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))->when($request->hasta, fn ($q, $v) => $q->whereDate('created_at', '<=', $v));

        return view('entrepreneur.sales', ['pedidos' => (clone $q)->latest()->paginate(15)->withQueryString(), 'total' => (clone $q)->sum('subtotal')]);
    }
}
