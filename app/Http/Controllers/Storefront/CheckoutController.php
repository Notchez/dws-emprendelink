<?php
namespace App\Http\Controllers\Storefront;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreOrderRequest;
use App\Services\CartService;
use App\Services\Orders\OrderPlacementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
class CheckoutController extends Controller
{
    public function __construct(private CartService $cart,private OrderPlacementService $orders) {}
    public function create(): View|RedirectResponse { if ($this->cart->items()->isEmpty()) return redirect()->route('cart.index')->withErrors(['cart'=>'Tu carrito está vacío.']); return view('storefront.checkout',['items'=>$this->cart->items(),'total'=>$this->cart->total(),'token'=>(string)Str::uuid()]); }
    public function store(StoreOrderRequest $request): RedirectResponse { $orders=$this->orders->place($request->user(),$request->validated()); session(['confirmed_order_ids'=>$orders->pluck('id')->all()]); return redirect()->route('checkout.confirmed'); }
    public function confirmed(): View { $ids=session('confirmed_order_ids',[]); abort_if($ids===[],404); $orders=request()->user()->pedidos()->with('emprendimiento')->whereIn('id',$ids)->get(); return view('storefront.confirmed',compact('orders')); }
}
