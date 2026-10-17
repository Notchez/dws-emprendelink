<?php
namespace App\Http\Requests\Admin;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
class StoreEntrepreneurRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasRole(UserRole::ADMIN) === true; }
    public function rules(): array { return ['name'=>['required','string','max:100'],'email'=>['required','email','unique:users,email'],'password'=>['required',Password::min(8)],'telefono'=>['nullable','string','max:20'],'nombre_emprendimiento'=>['required','string','max:150'],'descripcion'=>['nullable','string','max:1000'],'plan_id'=>['required',Rule::exists('planes','id')->where('estado',true)->whereNull('deleted_at')]]; }
}
