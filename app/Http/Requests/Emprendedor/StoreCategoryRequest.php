<?php
namespace App\Http\Requests\Emprendedor;
use App\Models\Categoria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('create', Categoria::class) === true; }
    public function rules(): array { return ['nombre'=>['required','string','max:100',Rule::unique('categorias','nombre')->where('emprendimiento_id',$this->user()->emprendimiento?->id)->whereNull('deleted_at')],'descripcion'=>['nullable','string','max:255'],'estado'=>['sometimes','boolean']]; }
}
