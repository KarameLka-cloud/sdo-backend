<?php

namespace Database\Seeders\User;

use App\Models\User\Position;
use Illuminate\Database\Seeder;

$positions = [
    'Юрисконсульт',
    'Юрисконсульт 2 категории',
    'Специалист 1 категории',
    'Ведущий специалист',
    'Менеджер 1 категории',
    'Менеджер 2 категории',
    'Главный менеджер',
    'Заместитель руководителя',
    'Руководитель отделения',
    'Ведущий менеджер',
];

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            'Все сотрудники',
            'Юрисконсульт',
            'Юрисконсульт 2 категории',
            'Специалист 1 категории',
            'Ведущий специалист',
            'Менеджер 1 категории',
            'Менеджер 2 категории',
            'Главный менеджер',
            'Заместитель руководителя',
            'Руководитель отделения',
            'Ведущий менеджер',
        ];

        foreach ($positions as $positionName) {
            Position::create([
                'name' => $positionName,
            ]);
        }
    }
}
