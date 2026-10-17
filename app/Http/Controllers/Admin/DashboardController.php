<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\Admin\AdminDashboardService;
use Illuminate\View\View;
class DashboardController extends Controller
{
    public function __construct(private AdminDashboardService $dashboard) {}
    public function index(): View { return view('admin.dashboard',$this->dashboard->metrics()); }
    public function orders(): View { return view('admin.pedidos.index',['pedidos'=>Pedido::with(['emprendimiento','cliente'])->when(request('estado'),fn($q,$v)=>$q->where('estado_actual',$v))->latest()->paginate(15)->withQueryString()]); }
    public function order(Pedido $pedido): View { return view('admin.pedidos.show',['pedido'=>$pedido->load(['emprendimiento','cliente','detallePedidos.producto','historialEstados.usuario','comision'])]); }
}
