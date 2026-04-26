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
        Role::create([
            [
                'name' => 'ADMIN',
                'display_name' => 'Администратор',
            ],
            [
                'name' => 'MENTOR',
                'display_name' => 'Наставник',
            ],
            [
                'name' => 'ADMIN',
                'DEPARTMENT_HEAD' => 'Руководитель отдела',
            ],
        ]);
    }
}
