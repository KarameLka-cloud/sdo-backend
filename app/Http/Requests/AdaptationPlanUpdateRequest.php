<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdaptationPlanUpdateRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => ['sometimes', 'date'],
            'adaptation_plan_template_id' => ['sometimes', 'integer', 'exists:adaptation_plan_templates,id'],
            'shift' => ['sometimes', 'integer', 'min:1'],
            'mentor' => ['sometimes', 'integer', 'exists:users,id'],
            'department_head' => ['sometimes', 'integer', 'exists:users,id'],
        ];
    }
}
