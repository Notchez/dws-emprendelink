<?php
namespace App\Http\Requests\Admin;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
class UpdateUserStatusRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasRole(UserRole::ADMIN) === true && $this->route('user')?->id !== $this->user()?->id; }
    public function rules(): array { return ['estado'=>['required','boolean']]; }
}
