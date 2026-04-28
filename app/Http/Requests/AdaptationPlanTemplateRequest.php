<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdaptationPlanTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'work_schedule' => ['required', 'string', 'max:50'],
            'shifts' => ['required', 'array', 'min:1'],
            'shifts.*' => ['required', 'integer', 'min:1', 'max:12', 'distinct'],
            'task_blueprint' => ['nullable', 'array'],
            'task_blueprint.*.description' => ['required_with:task_blueprint', 'string', 'max:1000'],
            'task_blueprint.*.responsible_role' => [
                'nullable',
                'string',
                'in:Руководитель отдела,Наставник,Сотрудник УПиПК',
            ],
            'task_blueprint.*.day_from' => ['nullable', 'integer', 'min:1', 'max:365'],
            'task_blueprint.*.day_to' => ['nullable', 'integer', 'min:1', 'max:365'],
            'task_blueprint.*.links' => ['nullable', 'array'],
            'task_blueprint.*.links.*' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $blueprint = $this->input('task_blueprint', []);

            foreach ($blueprint as $index => $item) {
                $dayFrom = isset($item['day_from']) ? (int) $item['day_from'] : null;
                $dayTo = isset($item['day_to']) ? (int) $item['day_to'] : null;

                if ($dayTo !== null && $dayFrom === null) {
                    $validator->errors()->add(
                        "task_blueprint.{$index}.day_from",
                        'Укажите день начала периода.'
                    );
                }

                if ($dayFrom !== null && $dayTo !== null && $dayTo < $dayFrom) {
                    $validator->errors()->add(
                        "task_blueprint.{$index}.day_to",
                        'День окончания периода должен быть больше или равен дню начала.'
                    );
                }
            }
        });
    }
}
