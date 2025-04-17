<?php

namespace Database\Seeders;

use App\Models\University;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the UniversitySeeder first
        $this->call([
            UniversitySeeder::class,
            RolePermissionSeeder::class,
        ]);

        // Get all universities to assign to users
        $universities = University::all();
        
        // Create Super Admin user and assign to first university
        $superAdmin = User::create([
            'name' => 'Super Admin', 
            'email' => 'super@example.com', 
            'password' => Hash::make('password'),
            'university_id' => $universities[0]->id
        ]);
        $superAdmin->assignRole('super_admin');
        
        // Create Admin user and assign to second university
        $admin = User::create([
            'name' => 'Admin', 
            'email' => 'admin@example.com', 
            'password' => Hash::make('password'),
            'university_id' => $universities[1]->id
        ]);
        $admin->assignRole('admin');
        
        // Create Editor user and assign to third university
        $editor = User::create([
            'name' => 'Editor', 
            'email' => 'editor@example.com', 
            'password' => Hash::make('password'),
            'university_id' => $universities[2]->id
        ]);
        $editor->assignRole('editor');
        
        // Create regular user and assign to fourth university
        $user = User::create([
            'name' => 'Regular User', 
            'email' => 'user@example.com', 
            'password' => Hash::make('password'),
            'university_id' => $universities[3]->id
        ]);
    }
}