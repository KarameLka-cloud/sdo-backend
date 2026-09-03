<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AdaptationPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id', 'unique:adaptation_plans,user_id'],
            'start_date' => ['required', 'date'],
            'adaptation_plan_template_id' => ['required', 'exists:adaptation_plan_templates,id'],
            'shift' => ['required', 'integer', 'min:1'],
            'mentor' => ['required', 'integer', 'exists:users,id'],
            'department_head' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.unique' => 'План адаптации для этого пользователя уже создан.',
        ];
    }
}
