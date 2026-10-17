<?php
namespace App\Http\Requests\Admin;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
class StorePlanRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasRole(UserRole::ADMIN) === true; }
    public function rules(): array { return ['nombre'=>['required','string','max:100','unique:planes,nombre'],'descripcion'=>['nullable','string','max:1000'],'precio_mensual'=>['required','numeric','min:0'],'porcentaje_comision'=>['required','numeric','min:0','max:100'],'limite_productos'=>['required','integer','min:1'],'estado'=>['sometimes','boolean']]; }
}
