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
        // Call the required seeders first
        $this->call([
            UniversitySeeder::class,
            RolePermissionSeeder::class,
        ]);

        // Get all universities to assign to users
        $universities = University::all();
        
        // Ensure we have enough universities
        if ($universities->count() < 3) {
            throw new \Exception('At least 3 universities are required for seeding users.');
        }

        // Define user configurations
        $userConfigs = [
            // Predefined admin users
            [
                'name' => 'Super Admin',
                'email' => 'super@example.com',
                'role' => 'super_admin',
                'university_index' => 0
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'role' => 'admin',
                'university_index' => 1
            ],
            [
                'name' => 'Editor User',
                'email' => 'editor@example.com',
                'role' => 'editor',
                'university_index' => 2
            ],
            // Additional users with various roles
            [
                'name' => 'John Smith',
                'email' => 'john.smith@example.com',
                'role' => 'admin',
                'university_index' => 0
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@example.com',
                'role' => 'editor',
                'university_index' => 1
            ],
            [
                'name' => 'Michael Brown',
                'email' => 'michael.brown@example.com',
                'role' => 'editor',
                'university_index' => 2
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emily.davis@example.com',
                'role' => 'admin',
                'university_index' => 0
            ],
            [
                'name' => 'David Wilson',
                'email' => 'david.wilson@example.com',
                'role' => 'editor',
                'university_index' => 1
            ],
            [
                'name' => 'Lisa Anderson',
                'email' => 'lisa.anderson@example.com',
                'role' => 'editor',
                'university_index' => 2
            ],
            [
                'name' => 'Robert Taylor',
                'email' => 'robert.taylor@example.com',
                'role' => 'admin',
                'university_index' => 0
            ],
            [
                'name' => 'Jennifer Martinez',
                'email' => 'jennifer.martinez@example.com',
                'role' => 'editor',
                'university_index' => 1
            ],
            [
                'name' => 'William Garcia',
                'email' => 'william.garcia@example.com',
                'role' => 'editor',
                'university_index' => 2
            ]
        ];

        // Create the first 12 users with predefined data
        foreach ($userConfigs as $config) {
            $user = User::factory()
                ->withCredentials($config['name'], $config['email'])
                ->forUniversity($universities[$config['university_index']]->id)
                ->create();
            
            $user->assignRole($config['role']);
        }

        // Create 3 additional random users to reach 15 total
        for ($i = 0; $i < 3; $i++) {
            $randomUniversity = $universities->random();
            $roles = ['admin', 'editor'];
            $randomRole = $roles[array_rand($roles)];
            
            $user = User::factory()
                ->forUniversity($randomUniversity->id)
                ->create();
            
            $user->assignRole($randomRole);
        }

        $this->command->info('Created 15 users with predefined universities and roles.');
    }
}