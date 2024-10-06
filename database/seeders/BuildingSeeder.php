<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Partition;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class BuildingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Building::factory(5)
        ->has(
            Unit::factory()
            ->has(Partition::factory()->count(rand(0, 3)))
            ->count(rand(0, 10))
        )
        ->create();
    }
}
