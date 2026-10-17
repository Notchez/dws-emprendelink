<?php
namespace App\Http\Controllers\Emprendedor;
use App\Http\Controllers\Controller;
use App\Models\Comision;
use Illuminate\Http\Request;
use Illuminate\View\View;
class CommissionController extends Controller
{
    public function index(Request $request): View { $this->authorize('viewAny', Comision::class); $business=$request->user()->emprendimiento; return view('entrepreneur.commissions',['comisiones'=>$business->comisiones()->with(['pedido','emprendimiento'])->latest()->paginate(15),'total'=>$business->comisiones()->sum('total_comision')]); }
    public function sales(Request $request): View { $q=$request->user()->emprendimiento->pedidos()->with('cliente')->where('estado_actual','Entregado')->when($request->desde,fn($q,$v)=>$q->whereDate('created_at','>=',$v))->when($request->hasta,fn($q,$v)=>$q->whereDate('created_at','<=',$v)); return view('entrepreneur.sales',['pedidos'=>(clone $q)->latest()->paginate(15)->withQueryString(),'total'=>(clone $q)->sum('subtotal')]); }
}
