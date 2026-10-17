<?php
namespace App\Http\Controllers\Admin;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEntrepreneurRequest;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Models\Plan;
use App\Models\User;
use App\Services\Admin\UserAdministrationService;
use App\Services\Auth\EmprendedorRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class UserController extends Controller
{
    public function __construct(private UserAdministrationService $users,private EmprendedorRegistrationService $registration) {}
    public function index(Request $request): View { $q=User::with('emprendimiento')->when($request->search,fn($q,$v)=>$q->where(fn($x)=>$x->where('name','like',"%$v%")->orWhere('email','like',"%$v%")))->when($request->rol,fn($q,$v)=>$q->where('rol',$v)); return view('admin.users.index',['usuarios'=>$q->latest()->paginate(15)->withQueryString(),'roles'=>UserRole::cases()]); }
    public function show(User $user): View { return view('admin.users.show',['usuario'=>$user->load('emprendimiento.suscripcionActiva.plan')]); }
    public function status(UpdateUserStatusRequest $request,User $user): RedirectResponse { $this->users->setStatus($user,$request->boolean('estado')); return back()->with('success','Estado de cuenta actualizado.'); }
    public function createEntrepreneur(): View { return view('admin.users.create-entrepreneur',['planes'=>Plan::where('estado',true)->orderBy('precio_mensual')->get()]); }
    public function storeEntrepreneur(StoreEntrepreneurRequest $request): RedirectResponse { $user=$this->registration->register($request->validated()); return redirect()->route('admin.users.show',$user)->with('success','Emprendedor y negocio creados correctamente.'); }
}
