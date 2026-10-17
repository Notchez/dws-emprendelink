<?php
namespace App\Http\Requests\Emprendedor;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ChangeOrderStatusRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('update',$this->route('pedido')) === true; }
    public function rules(): array { return ['estado'=>['required',Rule::enum(OrderStatus::class)]]; }
}
