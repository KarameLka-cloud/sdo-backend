<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdaptationPlanInternCommentRequest extends FormRequest
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
            'intern_comment' => ['nullable', 'string', 'max:4000'],
        ];
    }
}
