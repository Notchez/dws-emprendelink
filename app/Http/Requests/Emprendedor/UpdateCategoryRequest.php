<?php

namespace App\Http\Requests\Emprendedor;

use App\Models\Categoria;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = $this->user();
        $categoria = $this->route('categoria');

        if ($usuario === null || !($categoria instanceof Categoria)) {
            return false;
        }

        $emprendimiento = $usuario->emprendimiento;

        if ($emprendimiento === null) {
            return false;
        }

        return $usuario->can('update', $emprendimiento)
            && $categoria->emprendimiento_id == $emprendimiento->id;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }
}