<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class CourseRequest extends FormRequest
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
            'department_id' => ['required', 'numeric'],
            'note_department' => ['nullable', 'string'],
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
            'department_id.required' => 'Department is required.',
            'department_id.numeric' => 'Department must be a number.',
            'note_department.string' => 'Note department must be a string.',
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
