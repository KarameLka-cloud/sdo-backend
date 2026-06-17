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
            'description' => ['nullable', 'string'],
            'link' => ['required', 'string'],
            'position_id' => ['required', 'numeric'],
            'note_position' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'duration' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Title is required.',
            'title.string' => 'Title must be a string.',
            'description.string' => 'Description must be a string.',
            'link.required' => 'Link is required.',
            'link.string' => 'Link must be a string.',
            'position_id.required' => 'Position is required.',
            'position_id.numeric' => 'Position must be a number.',
            'note_position.string' => 'Note position must be a string.',
            'date.required' => 'Date is required.',
            'date.date' => 'Date must be a date.',
            'duration.required' => 'Duration is required.',
            'duration.integer' => 'Duration must be an integer.',
            'duration.min' => 'Duration must be at least 1 minute.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge($this->normalizeNullableStrings(['description', 'note_position']));
    }

    private function normalizeNullableStrings(array $fields): array
    {
        $normalized = [];

        foreach ($fields as $field) {
            if (!$this->has($field)) {
                continue;
            }

            $value = $this->input($field);
            $normalized[$field] = is_string($value) && trim($value) === '' ? null : $value;
        }

        return $normalized;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors(),
        ], 422));
    }
}
