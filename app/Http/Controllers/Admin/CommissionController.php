<?php
namespace App\Http\Controllers\Admin;
use App\Enums\CommissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCommissionStatusRequest;
use App\Models\Comision;
use App\Services\Commissions\CommissionStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class CommissionController extends Controller
{
    public function __construct(private CommissionStatusService $statuses) {}

    public function index(): View
    {
        $this->authorize('viewAny', Comision::class);

        return view('admin.comisiones.index', [
            'comisiones' => Comision::with(['pedido', 'emprendimiento'])->latest()->paginate(15),
            'total' => Comision::sum('total_comision'),
            'pendiente' => Comision::where('estado', CommissionStatus::PENDIENTE->value)->sum('total_comision'),
        ]);
    }

    public function update(UpdateCommissionStatusRequest $request, Comision $comision): RedirectResponse
    {
        $this->statuses->transition($comision, CommissionStatus::from($request->validated('estado')));

        return back()->with('success', 'Estado de la comisión actualizado.');
    }
}
