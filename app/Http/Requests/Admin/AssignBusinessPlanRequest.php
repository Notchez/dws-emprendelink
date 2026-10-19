<?php
namespace App\Http\Requests\Admin;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class AssignBusinessPlanRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasRole(UserRole::ADMIN) === true; }
    public function rules(): array { return ['plan_id'=>['required',Rule::exists('planes','id')->where('estado',true)->whereNull('deleted_at')]]; }
}
