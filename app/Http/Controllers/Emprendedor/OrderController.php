<?php
namespace App\Http\Controllers\Emprendedor;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Emprendedor\ChangeOrderStatusRequest;
use App\Models\Pedido;
use App\Services\Orders\OrderWorkflowService;
use Illuminate\Http\Request;
use Illuminate\View\View;
class OrderController extends Controller
{
    public function __construct(private OrderWorkflowService $workflow) {}
    public function index(Request $request): View { $q=$request->user()->emprendimiento->pedidos()->with('cliente')->when($request->estado,fn($q,$v)=>$q->where('estado_actual',$v)); return view('entrepreneur.orders.index',['pedidos'=>$q->latest()->paginate(15)->withQueryString(),'estados'=>OrderStatus::cases()]); }
    public function show(Request $request,Pedido $pedido): View { $this->authorize('view',$pedido); return view('entrepreneur.orders.show',['pedido'=>$pedido->load(['cliente','detallePedidos.producto','historialEstados.usuario']),'transiciones'=>$pedido->estado_actual->allowedTransitions()]); }
    public function status(ChangeOrderStatusRequest $request,Pedido $pedido) { $this->workflow->transition($pedido,OrderStatus::from($request->validated('estado')),$request->user()); return back()->with('success','Estado del pedido actualizado.'); }
}
