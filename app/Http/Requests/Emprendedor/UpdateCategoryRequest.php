<?php
namespace App\Http\Requests\Emprendedor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('update',$this->route('categoria')) === true; }
    public function rules(): array { $categoria=$this->route('categoria'); return ['nombre'=>['required','string','max:100',Rule::unique('categorias','nombre')->where('emprendimiento_id',$categoria->emprendimiento_id)->whereNull('deleted_at')->ignore($categoria->id)],'descripcion'=>['nullable','string','max:255'],'estado'=>['required','boolean']]; }
}
