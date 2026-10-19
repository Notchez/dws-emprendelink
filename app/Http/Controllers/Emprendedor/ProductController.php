<?php
namespace App\Http\Controllers\Emprendedor;
use App\Http\Controllers\Controller;
use App\Http\Requests\Emprendedor\StoreProductRequest;
use App\Http\Requests\Emprendedor\UpdateProductRequest;
use App\Http\Requests\Emprendedor\UpdateProductStatusRequest;
use App\Models\Producto;
use App\Services\Emprendedor\ProductCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class ProductController extends Controller
{
    public function __construct(private ProductCatalogService $products) {}
    public function index(Request $request): View { $business=$request->user()->emprendimiento; return view('entrepreneur.products.index',['productos'=>$business->productos()->with('categoria')->latest()->paginate(15),'categorias'=>$business->categorias()->where('estado',true)->orderBy('nombre')->get()]); }
    public function create(Request $request): View { return view('entrepreneur.products.form',['producto'=>new Producto,'categorias'=>$request->user()->emprendimiento->categorias()->where('estado',true)->get()]); }
    public function store(StoreProductRequest $request): RedirectResponse { $this->products->create($request->user()->emprendimiento,$request->safe()->except('imagen'),$request->file('imagen')); return redirect()->route('entrepreneur.products.index')->with('success','Producto creado.'); }
    public function edit(Request $request,Producto $producto): View { $this->authorize('update',$producto); return view('entrepreneur.products.form',['producto'=>$producto,'categorias'=>$request->user()->emprendimiento->categorias()->where('estado',true)->get()]); }
    public function update(UpdateProductRequest $request,Producto $producto): RedirectResponse { $this->products->update($producto,$request->safe()->except('imagen'),$request->file('imagen')); return redirect()->route('entrepreneur.products.index')->with('success','Producto actualizado.'); }
    public function status(UpdateProductStatusRequest $request,Producto $producto): RedirectResponse { $this->products->setStatus($producto,$request->boolean('estado')); return back()->with('success','Estado del producto actualizado.'); }
}
