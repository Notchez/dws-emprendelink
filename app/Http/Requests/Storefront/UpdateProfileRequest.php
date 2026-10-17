<?php
namespace App\Http\Requests\Storefront;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasRole(UserRole::CLIENTE) === true; }
    public function rules(): array { return ['name'=>['required','string','max:100'],'telefono'=>['nullable','string','max:20'],'direccion'=>['nullable','string','max:500']]; }
}
