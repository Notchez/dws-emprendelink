<?php
namespace App\Http\Requests\Admin;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
class UpdateBusinessAdministrationRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasRole(UserRole::ADMIN) === true; }
    public function rules(): array { return ['nombre'=>['required','string','max:150'],'descripcion'=>['nullable','string','max:2000'],'telefono_contacto'=>['nullable','string','max:20'],'estado'=>['required','boolean']]; }
}
