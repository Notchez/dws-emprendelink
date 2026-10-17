<?php
namespace App\Http\Controllers\Storefront;
use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class CartController extends Controller
{
    public function __construct(private CartService $cart) {}
    public function index(): View { return view('storefront.cart',['items'=>$this->cart->items(),'total'=>$this->cart->total()]); }
    public function store(Request $request,Producto $producto): RedirectResponse { $data=$request->validate(['cantidad'=>['required','integer','min:1','max:99']]); $this->cart->add($producto,$data['cantidad']); return redirect()->route('cart.index')->with('success','Producto agregado al carrito.'); }
    public function update(Request $request,Producto $producto): RedirectResponse { $data=$request->validate(['cantidad'=>['required','integer','min:0','max:99']]); $this->cart->update($producto,$data['cantidad']); return back()->with('success','Carrito actualizado.'); }
    public function destroy(Producto $producto): RedirectResponse { $this->cart->remove($producto); return back()->with('success','Producto eliminado.'); }
    public function clear(): RedirectResponse { $this->cart->clear(); return back()->with('success','Carrito vaciado.'); }
}
