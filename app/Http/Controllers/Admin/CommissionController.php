<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterAccountStatementMonthRequest;
use App\Http\Requests\Admin\PayAccountStatementRequest;
use App\Models\Comision;
use App\Models\EstadoCuenta;
use App\Services\Commissions\MonthlyAccountStatementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CommissionController extends Controller
{
    public function __construct(private MonthlyAccountStatementService $statements) {}

    public function index(FilterAccountStatementMonthRequest $request): View
    {
        $month = $request->validated('mes')
            ?: now()->subMonthNoOverflow()->format('Y-m');
        $periodStart = Carbon::createFromFormat('Y-m-d', $month.'-01')->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        $this->statements->synchronizePeriod($periodStart);

        $statements = EstadoCuenta::query()
            ->with('emprendimiento')
            ->where('periodo_mes', $periodStart->month)
            ->where('periodo_anio', $periodStart->year)
            ->where('total_adeudado', '>', 0)
            ->orderBy(
                fn ($query) => $query->select('nombre')
                    ->from('emprendimientos')
                    ->whereColumn('emprendimientos.id', 'estados_cuenta.emprendimiento_id'),
            )
            ->paginate(15)
            ->withQueryString();

        $counts = Comision::query()
            ->selectRaw('emprendimiento_id, COUNT(*) AS total')
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->whereIn('emprendimiento_id', $statements->getCollection()->pluck('emprendimiento_id'))
            ->groupBy('emprendimiento_id')
            ->pluck('total', 'emprendimiento_id');

        $statements->getCollection()->each(
            fn (EstadoCuenta $statement) => $statement->setAttribute(
                'commission_count',
                (int) ($counts[$statement->emprendimiento_id] ?? 0),
            ),
        );

        return view('admin.comisiones.index', [
            'mes' => $month,
            'periodStart' => $periodStart,
            'periodClosed' => $periodStart->lt(now()->startOfMonth()),
            'statements' => $statements,
            'periodTotal' => EstadoCuenta::query()
                ->where('periodo_mes', $periodStart->month)
                ->where('periodo_anio', $periodStart->year)
                ->sum('total_adeudado'),
            'commissionCount' => Comision::query()
                ->whereBetween('created_at', [$periodStart, $periodEnd])
                ->count(),
        ]);
    }

    public function pay(
        PayAccountStatementRequest $request,
        EstadoCuenta $estadoCuenta,
    ): RedirectResponse {
        $this->statements->markAsPaid($estadoCuenta);

        return redirect()
            ->route('admin.comisiones.index', [
                'mes' => sprintf(
                    '%04d-%02d',
                    $estadoCuenta->periodo_anio,
                    $estadoCuenta->periodo_mes,
                ),
            ])
            ->with('success', 'El período mensual fue marcado como pagado.');
    }
}
