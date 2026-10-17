<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Models\Plan;
use App\Services\Admin\PlanManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class PlanController extends Controller
{
    public function __construct(private PlanManagementService $plans) {}
    public function index(): View { return view('admin.planes.index',['planes'=>Plan::withCount('suscripciones')->orderBy('precio_mensual')->get()]); }
    public function create(): View { return view('admin.planes.form',['plan'=>new Plan]); }
    public function store(StorePlanRequest $request): RedirectResponse { $this->plans->create($request->validated()); return redirect()->route('admin.planes.index')->with('success','Plan creado.'); }
    public function edit(Plan $plan): View { return view('admin.planes.form',compact('plan')); }
    public function update(UpdatePlanRequest $request,Plan $plan): RedirectResponse { $this->plans->update($plan,$request->validated()); return redirect()->route('admin.planes.index')->with('success','Plan actualizado.'); }
}
