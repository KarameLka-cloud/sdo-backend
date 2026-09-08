<?php

namespace Database\Seeders\User;

use App\Models\User\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'ADMIN',
                'display_name' => 'Администратор',
            ],
            [
                'name' => 'MENTOR',
                'display_name' => 'Наставник',
            ],
            [
                'name' => 'SUPERVISOR',
                'display_name' => 'Руководитель отделения',
            ],
            [
                'name' => 'DEPARTMENT_HEAD',
                'display_name' => 'Начальник отдела',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                ['display_name' => $role['display_name']],
            );
        }
    }
}
