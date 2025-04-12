<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Role::create(['name' => 'super_admin']);
        $admin = Role::create(['name' => 'admin']);
        $editor = Role::create(['name' => 'editor']);
        

        Permission::create(['name' => 'access.super_admin']);
        Permission::create(['name' => 'access.admin']);  
        Permission::create(['name' => 'access.editor']);
        

        $superAdmin->givePermissionTo(['access.super_admin', 'access.admin', 'access.editor']); 
        $admin->givePermissionTo(['access.admin', 'access.editor']);
        $editor->givePermissionTo('access.editor');
    }
}
