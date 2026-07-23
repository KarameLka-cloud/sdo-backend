<?php

namespace App\Http\Requests;

use App\Models\LearningItem;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LearningItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type');

        $rules = [
            'category' => ['required', Rule::in(LearningItem::CATEGORIES)],
            'type' => ['required', Rule::in(LearningItem::TYPES)],
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'duration' => ['required', 'integer', 'min:1'],
            'link' => ['nullable', 'string'],
            'time' => ['nullable', 'date_format:H:i'],
            'department_id' => ['nullable', 'numeric'],
            'note_department' => ['nullable', 'string'],
            'position_id' => ['nullable', 'numeric'],
            'note_position' => ['nullable', 'string'],
        ];

        return match ($type) {
            LearningItem::TYPE_EVENT => array_merge($rules, [
                'department_id' => ['required', 'numeric'],
                'link' => ['nullable', 'string'],
                'time' => ['nullable', 'date_format:H:i'],
            ]),
            LearningItem::TYPE_COURSE => array_merge($rules, [
                'department_id' => ['required', 'numeric'],
                'link' => ['required', 'string'],
            ]),
            LearningItem::TYPE_WEBINAR => array_merge($rules, [
                'link' => ['nullable', 'string'],
                'time' => ['nullable', 'date_format:H:i'],
            ]),
            LearningItem::TYPE_TEST => array_merge($rules, [
                'position_id' => ['required', 'numeric'],
                'link' => ['required', 'string'],
            ]),
            default => $rules,
        };
    }

    public function messages(): array
    {
        return [
            'category.required' => 'Category is required.',
            'category.in' => 'Category is invalid.',
            'type.required' => 'Type is required.',
            'type.in' => 'Type is invalid.',
            'title.required' => 'Title is required.',
            'title.string' => 'Title must be a string.',
            'description.string' => 'Description must be a string.',
            'link.required' => 'Link is required.',
            'link.string' => 'Link must be a string.',
            'department_id.required' => 'Department is required.',
            'department_id.numeric' => 'Department must be a number.',
            'note_department.string' => 'Note department must be a string.',
            'position_id.required' => 'Position is required.',
            'position_id.numeric' => 'Position must be a number.',
            'note_position.string' => 'Note position must be a string.',
            'time.date_format' => 'Time must be a time.',
            'date.required' => 'Date is required.',
            'date.date' => 'Date must be a date.',
            'duration.required' => 'Duration is required.',
            'duration.integer' => 'Duration must be an integer.',
            'duration.min' => 'Duration must be at least 1 minute.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge($this->normalizeNullableStrings([
            'description',
            'link',
            'note_department',
            'note_position',
            'time',
        ]));
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!is_array($data)) {
            return $data;
        }

        $type = $data['type'] ?? null;

        $data['department_id'] = in_array($type, [
            LearningItem::TYPE_EVENT,
            LearningItem::TYPE_COURSE,
        ], true) ? ($data['department_id'] ?? null) : null;

        $data['note_department'] = in_array($type, [
            LearningItem::TYPE_EVENT,
            LearningItem::TYPE_COURSE,
        ], true) ? ($data['note_department'] ?? null) : null;

        $data['position_id'] = $type === LearningItem::TYPE_TEST
            ? ($data['position_id'] ?? null)
            : null;

        $data['note_position'] = $type === LearningItem::TYPE_TEST
            ? ($data['note_position'] ?? null)
            : null;

        $data['time'] = in_array($type, [
            LearningItem::TYPE_EVENT,
            LearningItem::TYPE_WEBINAR,
        ], true) ? ($data['time'] ?? null) : null;

        return $data;
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
