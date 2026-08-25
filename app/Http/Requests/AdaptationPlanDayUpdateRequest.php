<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdaptationPlanDayUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date_from' => ['required', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'completion' => ['required', 'in:в процессе,выполнен,есть замечания'],
            'employee_comment' => ['nullable', 'string', 'max:4000'],
            'intern_comment' => ['nullable', 'string', 'max:4000'],
            'mentor_comment' => ['nullable', 'string', 'max:4000'],
            'department_head_comment' => ['nullable', 'string', 'max:4000'],
        ];
    }
}
