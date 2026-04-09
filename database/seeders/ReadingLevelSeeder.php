<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReadingLevelSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('reading_levels')->insert([
            ['level' => 1, 'label' => 'Simula',   'accuracy_threshold' => 60, 'description' => 'Beginning reader',  'created_at' => now(), 'updated_at' => now()],
            ['level' => 2, 'label' => 'Lumalago', 'accuracy_threshold' => 70, 'description' => 'Developing reader', 'created_at' => now(), 'updated_at' => now()],
            ['level' => 3, 'label' => 'Mahusay',  'accuracy_threshold' => 80, 'description' => 'Proficient reader', 'created_at' => now(), 'updated_at' => now()],
            ['level' => 4, 'label' => 'Bihasa',   'accuracy_threshold' => 90, 'description' => 'Advanced reader',   'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}