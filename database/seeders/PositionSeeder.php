<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Seed the application's positions.
     */
    public function run(): void
    {
        $positions = [
            'Sales Executive',
            'Programmer',
            'QA (Quality Assurance)',
            'HR Officer',
            'Customer Service & Planning',
            'Process Engineer',
            'Network Engineer',
            'Accountant',
        ];

        foreach ($positions as $title) {
            Position::firstOrCreate(['title' => $title], ['is_active' => true]);
        }
    }
}
