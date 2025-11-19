<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class TestRequest extends FormRequest
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
            'title' => ['required', 'string'],
            'url' => ['required', 'string'],
            'position_id' => ['required', 'numeric'],
            'note_position' => ['nullable', 'string'],
            'date_end' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Title is required.',
            'title.string' => 'Title must be a string.',
            'url.required' => 'Url is required.',
            'url.string' => 'Url must be a string.',
            'position_id.required' => 'Position is required.',
            'position_id.numeric' => 'Position must be a number.',
            'note_position.string' => 'Note position must be a string.',
            'date_end.required' => 'Date is required.',
            'date_end.date' => 'Date must be a date.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors(),
        ], 422));
    }
}
