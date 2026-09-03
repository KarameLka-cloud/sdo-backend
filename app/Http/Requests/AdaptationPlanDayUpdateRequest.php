<?php

namespace App\Http\Requests;

use App\Enums\CompletionStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Manager-side edit of a plan day. `intern_comment` is deliberately absent:
 * it belongs to the intern and is only writable through their own endpoint.
 */
class AdaptationPlanDayUpdateRequest extends FormRequest
{
    /** Authorization happens in the controller via AdaptationPlanPolicy. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date_from' => ['required', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'completion' => ['required', Rule::in(CompletionStatus::values())],
            'employee_comment' => ['nullable', 'string', 'max:4000'],
            'mentor_comment' => ['nullable', 'string', 'max:4000'],
            'department_head_comment' => ['nullable', 'string', 'max:4000'],
        ];
    }
}
