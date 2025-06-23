<?php

namespace Database\Seeders;

use App\Models\University;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UniversitySeeder::class,
            RolePermissionSeeder::class,
        ]);

        $universities = University::all();
        
        $superAdmin = User::factory()
            ->withCredentials('Super Admin', 'super@example.com')
            ->forUniversity($universities->first()->id)
            ->create();
        
        $superAdmin->assignRole('super_admin');

        $this->command->info('Created the super_admin user.');
    }
}