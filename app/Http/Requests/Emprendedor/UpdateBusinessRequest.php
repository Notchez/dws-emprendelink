<?php
namespace App\Http\Requests\Emprendedor;
use Illuminate\Foundation\Http\FormRequest;
class UpdateBusinessRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('update',$this->route('emprendimiento')) === true; }
    public function rules(): array { return ['nombre'=>['required','string','max:150'],'descripcion'=>['nullable','string','max:2000'],'telefono_contacto'=>['nullable','string','max:20'],'logo'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:2048']]; }
}
