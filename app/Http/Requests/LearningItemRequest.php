<?php

namespace App\Http\Requests;

use App\Models\LearningItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LearningItemRequest extends FormRequest
{
    /** Which learning types actually use each optional field. */
    private const FIELD_APPLIES_TO = [
        'department_id' => [LearningItem::TYPE_EVENT, LearningItem::TYPE_COURSE],
        'note_department' => [LearningItem::TYPE_EVENT, LearningItem::TYPE_COURSE],
        'position_id' => [LearningItem::TYPE_TEST],
        'note_position' => [LearningItem::TYPE_TEST],
        'time' => [LearningItem::TYPE_EVENT, LearningItem::TYPE_WEBINAR],
    ];

    /** Authorization is enforced by the `role:full_access` route middleware. */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $department = ['required', 'numeric', 'exists:departments,id'];
        $position = ['required', 'numeric', 'exists:positions,id'];
        $requiredLink = ['required', 'string'];

        $typeRules = match ($this->input('type')) {
            LearningItem::TYPE_EVENT => ['department_id' => $department],
            LearningItem::TYPE_COURSE => ['department_id' => $department, 'link' => $requiredLink],
            LearningItem::TYPE_TEST => ['position_id' => $position, 'link' => $requiredLink],
            default => [],
        };

        return [
            'category' => ['required', Rule::in(LearningItem::CATEGORIES)],
            'type' => ['required', Rule::in(LearningItem::TYPES)],
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'duration' => ['required', 'integer', 'min:1'],
            'link' => ['nullable', 'string'],
            'time' => ['nullable', 'date_format:H:i'],
            'department_id' => ['nullable', 'numeric', 'exists:departments,id'],
            'note_department' => ['nullable', 'string'],
            'position_id' => ['nullable', 'numeric', 'exists:positions,id'],
            'note_position' => ['nullable', 'string'],
            ...$typeRules,
        ];
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
            'department_id.exists' => 'Department does not exist.',
            'note_department.string' => 'Note department must be a string.',
            'position_id.required' => 'Position is required.',
            'position_id.numeric' => 'Position must be a number.',
            'position_id.exists' => 'Position does not exist.',
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
        $blankToNull = [];

        foreach (['description', 'link', 'note_department', 'note_position', 'time'] as $field) {
            if ($this->has($field)) {
                $value = $this->input($field);
                $blankToNull[$field] = is_string($value) && trim($value) === '' ? null : $value;
            }
        }

        $this->merge($blankToNull);
    }

    /**
     * Clears fields that do not belong to the submitted type, so switching a
     * type never leaves stale organisational data behind.
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (! is_array($data)) {
            return $data;
        }

        $type = $data['type'] ?? null;

        foreach (self::FIELD_APPLIES_TO as $field => $types) {
            $data[$field] = in_array($type, $types, true) ? ($data[$field] ?? null) : null;
        }

        return $data;
    }
}
