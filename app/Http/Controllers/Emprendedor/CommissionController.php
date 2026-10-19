<?php

namespace App\Http\Controllers\Emprendedor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Emprendedor\FilterCommissionMonthRequest;
use App\Models\Comision;
use App\Models\EstadoCuenta;
use App\Services\Commissions\MonthlyAccountStatementService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CommissionController extends Controller
{
    public function __construct(private MonthlyAccountStatementService $statements) {}

    public function index(FilterCommissionMonthRequest $request): View
    {
        $month = $request->validated('mes')
            ?: now()->subMonthNoOverflow()->format('Y-m');
        $periodStart = Carbon::createFromFormat('Y-m-d', $month.'-01')->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();
        $business = $request->user()->emprendimiento;

        $commissionCount = Comision::query()
            ->where('emprendimiento_id', $business->id)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->count();

        if ($commissionCount > 0) {
            $this->statements->recalculate($business, $periodStart);
        }

        $statement = EstadoCuenta::query()
            ->where('emprendimiento_id', $business->id)
            ->where('periodo_mes', $periodStart->month)
            ->where('periodo_anio', $periodStart->year)
            ->first();

        return view('entrepreneur.commissions', [
            'mes' => $month,
            'periodStart' => $periodStart,
            'periodClosed' => $periodStart->lt(now()->startOfMonth()),
            'statement' => $statement,
            'commissionCount' => $commissionCount,
        ]);
    }

    public function sales(Request $request): View
    {
        $query = $request->user()->emprendimiento->pedidos()
            ->with('cliente')
            ->where('estado_actual', 'Entregado')
            ->when(
                $request->desde,
                fn ($query, $value) => $query->whereDate('created_at', '>=', $value),
            )
            ->when(
                $request->hasta,
                fn ($query, $value) => $query->whereDate('created_at', '<=', $value),
            );

        return view('entrepreneur.sales', [
            'pedidos' => (clone $query)->latest()->paginate(15)->withQueryString(),
            'total' => (clone $query)->sum('subtotal'),
        ]);
    }
}
