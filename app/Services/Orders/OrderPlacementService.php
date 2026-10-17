<?php
namespace App\Services\Orders;
use App\Enums\OrderStatus;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class OrderPlacementService
{
    public function __construct(private CartService $cart) {}
    public function place(User $customer,array $data): Collection
    {
        $customer->refresh();

        if (blank($customer->name) || blank($customer->email) || blank($customer->telefono)) {
            throw ValidationException::withMessages([
                'contacto' => 'Completa tu nombre, correo y teléfono desde Mi perfil antes de confirmar el pedido.',
            ]);
        }

        $existing=Pedido::where('cliente_id',$customer->id)->where('checkout_token',$data['token_idempotencia'])->get();
        if ($existing->isNotEmpty()) { $this->cart->clear(); return $existing; }
        $items=$this->cart->items();
        if ($items->isEmpty()) throw ValidationException::withMessages(['cart'=>'Tu carrito está vacío.']);
        try {
            $orders=DB::transaction(function () use ($customer,$data,$items) {
                $created=new Collection();
                foreach ($items->groupBy(fn ($item)=>$item['product']->emprendimiento_id) as $businessId=>$group) {
                $ids=$group->pluck('product.id');
                $products=Producto::with(['categoria','emprendimiento.usuario'])->whereIn('id',$ids)->lockForUpdate()->get()->keyBy('id');
                foreach ($group as $item) {
                    $p=$products->get($item['product']->id);
                    if (! $p || ! $p->estado || ($p->categoria !== null && ! $p->categoria->estado) || ! $p->emprendimiento->estado || ! $p->emprendimiento->usuario->estado) {
                        throw ValidationException::withMessages(['cart'=>'Uno de los productos ya no está disponible.']);
                    }
                }
                $business=$products->first()->emprendimiento;
                $subscription=$business->suscripcionActiva()->with('plan')->first();
                if (! $subscription?->plan) throw ValidationException::withMessages(['cart'=>'El emprendimiento no tiene un plan activo.']);
                $subtotal=$group->sum(fn ($item)=>(float)$products[$item['product']->id]->precio*$item['quantity']);
                $order=Pedido::create([
                    'emprendimiento_id'=>$businessId,'cliente_id'=>$customer->id,'codigo_publico'=>(string)Str::uuid(),
                    'token_idempotencia'=>(string)Str::uuid(),'checkout_token'=>$data['token_idempotencia'],'nombre_cliente'=>$customer->name,
                    'telefono_cliente'=>$customer->telefono,'correo_cliente'=>$customer->email,'modalidad_entrega'=>$data['modalidad_entrega'],
                    'direccion_entrega'=>$data['modalidad_entrega']==='domicilio'?$data['direccion']:null,'notas'=>$data['notas']??null,
                    'estado_actual'=>OrderStatus::PENDIENTE,'subtotal'=>$subtotal,'plan_id_aplicado'=>$subscription->plan->id,
                    'nombre_plan_aplicado'=>$subscription->plan->nombre,'porcentaje_comision_aplicado'=>$subscription->plan->porcentaje_comision,
                ]);
                foreach ($group as $item) { $p=$products[$item['product']->id]; $order->detallePedidos()->create(['producto_id'=>$p->id,'cantidad'=>$item['quantity'],'precio_unitario'=>$p->precio,'subtotal'=>(float)$p->precio*$item['quantity']]); }
                    $created->push($order);
                }
                return $created;
            });
        } catch (QueryException $exception) {
            $existing=Pedido::where('cliente_id',$customer->id)->where('checkout_token',$data['token_idempotencia'])->get();
            if ($existing->isEmpty()) throw $exception;
            $this->cart->clear();
            return $existing;
        }
        $this->cart->clear(); return $orders;
    }
}
