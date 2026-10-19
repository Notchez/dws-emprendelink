<?php
namespace App\Http\Controllers\Storefront;
use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\View\View;
class CustomerOrderController extends Controller
{
    public function index(Request $request): View { return view('storefront.orders.index',['pedidos'=>$request->user()->pedidos()->with('emprendimiento')->latest()->paginate(15)]); }
    public function show(Pedido $pedido): View { $this->authorize('view',$pedido); return view('storefront.orders.show',['pedido'=>$pedido->load(['emprendimiento','detallePedidos.producto','historialEstados'])]); }
}
