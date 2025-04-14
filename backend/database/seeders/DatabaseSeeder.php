<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
        ]);
        $superAdmin = User::create(['name' => 'Super Admin', 'email' => 'super@example.com', 'password' => Hash::make('password')]);
        $superAdmin->assignRole('super_admin');
        
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => Hash::make('password')]);
        $admin->assignRole('admin');
        
        $editor = User::create(['name' => 'Editor', 'email' => 'editor@example.com', 'password' => Hash::make('password')]);
        $editor->assignRole('editor');
    }
}
