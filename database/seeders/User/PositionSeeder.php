<?php

namespace Database\Seeders\User;

use App\Models\User\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Position::factory()->count(10)->create();
    }
}
