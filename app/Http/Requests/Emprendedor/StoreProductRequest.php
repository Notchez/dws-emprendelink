<?php

namespace App\Http\Requests\Emprendedor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = $this->user();

        if ($usuario === null || $usuario->emprendimiento === null) {
            return false;
        }

        return $usuario->can('update', $usuario->emprendimiento);
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

