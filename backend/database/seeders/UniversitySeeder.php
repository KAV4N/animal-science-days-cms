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
        $universities = [
            [
                'full_name' => 'University of Ljubljana, Biotechnical Faculty, Department of Animal Science',
                'country' => 'Slovenia',
                'city' => 'Ljubljana',
            ],
            [
                'full_name' => 'University of Zagreb, Faculty of Agriculture',
                'country' => 'Croatia',
                'city' => 'Zagreb',
            ],
            [
                'full_name' => 'Josip Juraj Strossmayer University of Osijek, Faculty of Agrobiotechnical Sciences Osijek',
                'country' => 'Croatia',
                'city' => 'Osijek',
            ],
            [
                'full_name' => 'BOKU University',
                'country' => 'Austria',
                'city' => 'Vienna',
            ],
            [
                'full_name' => 'University of Padua',
                'country' => 'Italy',
                'city' => 'Padua',
            ],
            [
                'full_name' => 'Czech University of Life Sciences Prague',
                'country' => 'Czech Republic',
                'city' => 'Prague',
            ],
            [
                'full_name' => 'Hungarian University of Agricultural and Life Sciences',
                'country' => 'Hungary',
                'city' => 'Budapest', 
            ],
            [
                'full_name' => 'Slovak University of Agriculture in Nitra',
                'country' => 'Slovakia',
                'city' => 'Nitra',
            ],
        ];

        foreach ($universities as $university) {
            University::create($university);
        }
    }
}
