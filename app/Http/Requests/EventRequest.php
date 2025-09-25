<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class EventRequest extends FormRequest
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
            'description' => ['required', 'string'],
            'department_id' => ['required', 'numeric'],
            'time' => ['required', 'date_format:H:i'],
            'date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Title is required.',
            'title.string' => 'Title must be a string.',
            'description.required' => 'Description is required.',
            'description.string' => 'Description must be a string.',
            'department_id.required' => 'Department is required.',
            'department_id.numeric' => 'Department must be a number.',
            'time.required' => 'Time is required.',
            'time.date' => 'Time must be a time.',
            'date.required' => 'Date is required.',
            'date.date' => 'Date must be a date.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors(),
        ], 422));
    }
}
