<?php
namespace App\Http\Requests\Storefront;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::CLIENTE) === true;
    }

    public function rules(): array
    {
        return [
            'modalidad_entrega' => ['required', Rule::in(['domicilio', 'retiro'])],
            'direccion' => ['nullable', 'required_if:modalidad_entrega,domicilio', 'string', 'max:500'],
            'notas' => ['nullable', 'string', 'max:1000'],
            'token_idempotencia' => ['required', 'uuid'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            $user = $this->user();

            if (! $user || blank($user->name) || blank($user->email) || blank($user->telefono)) {
                $validator->errors()->add('contacto', 'Completa tu nombre, correo y teléfono desde Mi perfil antes de confirmar el pedido.');
            }
        }];
    }
}
