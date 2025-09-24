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
//        DB::table('roles')->insert([
//            'name' => 'admin',
//            'display_name' => 'Администратор',
//            'created_at' => now(),
//            'updated_at' => now(),
//        ]);
        Role::create([
            'name' => 'ADMIN',
            'display_name' => 'Администратор',
        ]);
    }
}
