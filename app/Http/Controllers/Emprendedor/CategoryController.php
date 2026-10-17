<?php
namespace App\Http\Controllers\Emprendedor;
use App\Http\Controllers\Controller;
use App\Http\Requests\Emprendedor\StoreCategoryRequest;
use App\Http\Requests\Emprendedor\UpdateCategoryRequest;
use App\Models\Categoria;
use App\Services\Emprendedor\CategoryManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class CategoryController extends Controller
{
    public function __construct(private CategoryManagementService $categories) {}
    public function index(Request $request): View { return view('entrepreneur.categories',['categorias'=>$request->user()->emprendimiento->categorias()->withCount('productos')->orderBy('nombre')->get()]); }
    public function store(StoreCategoryRequest $request): RedirectResponse { $this->categories->create($request->user()->emprendimiento,$request->validated()); return back()->with('success','Categoría creada.'); }
    public function update(UpdateCategoryRequest $request,Categoria $categoria): RedirectResponse { $this->categories->update($categoria,$request->validated()); return back()->with('success','Categoría actualizada.'); }
    public function destroy(Request $request,Categoria $categoria): RedirectResponse { $this->authorize('delete',$categoria); $this->categories->delete($categoria); return back()->with('success','Categoría eliminada.'); }
}
