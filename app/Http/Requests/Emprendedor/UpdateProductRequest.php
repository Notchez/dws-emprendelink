<?php

namespace App\Http\Requests\Emprendedor;

use App\Models\Producto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = $this->user();
        $producto = $this->route('producto');

        if ($usuario === null || !($producto instanceof Producto)) {
            return false;
        }

        $emprendimiento = $usuario->emprendimiento;

        if ($emprendimiento === null) {
            return false;
        }

        return $usuario->can('update', $emprendimiento)
            && $producto->emprendimiento_id == $emprendimiento->id;
    }

    public function rules(): array
    {
        $emprendimiento = $this->user()?->emprendimiento;

        return [
            'nombre' => ['required', 'string', 'max:150'],

            'descripcion' => ['nullable', 'string'],

            'precio' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
                'decimal:0,2',
            ],

            'categoria_id' => [
                'nullable',
                'integer',
                Rule::exists('categorias', 'id')
                    ->where('emprendimiento_id', $emprendimiento?->id)
                    ->whereNull('deleted_at'),
            ],

            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
