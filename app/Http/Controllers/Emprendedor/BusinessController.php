<?php
namespace App\Http\Controllers\Emprendedor;
use App\Http\Controllers\Controller;
use App\Http\Requests\Emprendedor\UpdateBusinessRequest;
use App\Models\Emprendimiento;
use App\Services\Emprendedor\BusinessProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class BusinessController extends Controller
{
    public function __construct(private BusinessProfileService $businesses) {}
    public function show(Request $request): View { return view('entrepreneur.business',['emprendimiento'=>$request->user()->emprendimiento()->with('suscripcionActiva.plan')->firstOrFail()]); }
    public function update(UpdateBusinessRequest $request,Emprendimiento $emprendimiento): RedirectResponse { $this->businesses->update($emprendimiento,$request->safe()->except('logo'),$request->file('logo')); return back()->with('success','Negocio actualizado.'); }
}
