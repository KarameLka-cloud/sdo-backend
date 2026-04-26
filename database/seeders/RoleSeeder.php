<?php

namespace Database\Seeders;

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
                'name' => 'DEPARTMENT_HEAD',
                'display_name' => 'Руководитель отдела',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}