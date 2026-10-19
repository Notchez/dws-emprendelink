<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Emprendimiento;
use App\Models\Plan;
use App\Http\Requests\Admin\AssignBusinessPlanRequest;
use App\Http\Requests\Admin\UpdateBusinessAdministrationRequest;
use App\Services\Admin\BusinessAdministrationService;
use Illuminate\View\View;
class EmprendimientoController extends Controller
{
    public function __construct(private BusinessAdministrationService $businesses) {}
    public function index(): View { $q=Emprendimiento::with(['usuario','suscripcionActiva.plan'])->withCount(['productos','pedidos'])->when(request('search'),fn($q,$v)=>$q->where('nombre','like',"%$v%")); return view('admin.Emprendimientos.index',['emprendimientos'=>$q->latest()->paginate(15)->withQueryString()]); }
    public function show(Emprendimiento $emprendimiento): View { return view('admin.Emprendimientos.show',['emprendimiento'=>$emprendimiento->load(['usuario','suscripcionActiva.plan'])->loadCount(['productos','pedidos']),'planes'=>Plan::where('estado',true)->orderBy('precio_mensual')->get()]); }
    public function update(UpdateBusinessAdministrationRequest $request,Emprendimiento $emprendimiento) { $this->businesses->update($emprendimiento,$request->validated()); return back()->with('success','Emprendimiento actualizado.'); }
    public function plan(AssignBusinessPlanRequest $request,Emprendimiento $emprendimiento) { $this->businesses->assignPlan($emprendimiento,Plan::where('estado',true)->findOrFail($request->integer('plan_id'))); return back()->with('success','Plan asignado correctamente.'); }
}
