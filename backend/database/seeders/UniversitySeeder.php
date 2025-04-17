<?php

namespace Database\Seeders;

use App\Models\University;
use Illuminate\Database\Seeder;

class UniversitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 4 universities
        $universities = [
            [
                'full_name' => 'Massachusetts Institute of Technology',
                'country' => 'United States',
                'city' => 'Cambridge'
            ],
            [
                'full_name' => 'University of Oxford',
                'country' => 'United Kingdom',
                'city' => 'Oxford'
            ],
            [
                'full_name' => 'University of Tokyo',
                'country' => 'Japan',
                'city' => 'Tokyo'
            ],
            [
                'full_name' => 'University of Toronto',
                'country' => 'Canada',
                'city' => 'Toronto'
            ]
        ];

        // Insert universities into database
        foreach ($universities as $university) {
            University::create($university);
        }
    }
}