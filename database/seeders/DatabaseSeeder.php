<?php

namespace Database\Seeders;

use Database\Seeders\User\DepartmentSeeder;
use Database\Seeders\User\PositionSeeder;
use Database\Seeders\User\RoleSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            DepartmentSeeder::class,
            PositionSeeder::class
        ]);
    }
}
