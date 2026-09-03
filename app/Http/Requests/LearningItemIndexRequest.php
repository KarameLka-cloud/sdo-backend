<?php

namespace App\Http\Requests;

use App\Models\LearningItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LearningItemIndexRequest extends FormRequest
{
    /** Any authenticated user may browse the catalogue. */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in(LearningItem::CATEGORIES)],
            'type' => ['required', Rule::in(LearningItem::TYPES)],
        ];
    }
}
