<?php
namespace App\Http\Controllers\Emprendedor;
use App\Http\Controllers\Controller;
use App\Services\Emprendedor\EntrepreneurDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;
class DashboardController extends Controller
{
    public function __construct(private EntrepreneurDashboardService $dashboard) {}
    public function __invoke(Request $request): View { $business=$request->user()->emprendimiento()->with('suscripcionActiva.plan')->firstOrFail(); return view('entrepreneur.dashboard',array_merge(['emprendimiento'=>$business],$this->dashboard->metrics($business))); }
}
