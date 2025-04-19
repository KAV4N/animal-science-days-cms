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

        $accessSuper = Permission::create(['name' => 'access.super_admin']);
        $accessAdmin = Permission::create(['name' => 'access.admin']);  
        $accessEditor = Permission::create(['name' => 'access.editor']);

        $manageEditor = Permission::create(['name' => 'manage.editor']);
        $manageAdmin = Permission::create(['name' => 'manage.admin']);

        $superAdmin->givePermissionTo([
            $accessSuper,
            $accessAdmin,
            $accessEditor,
            $manageEditor,
            $manageAdmin,
        ]);

        $admin->givePermissionTo([
            $accessAdmin,
            $accessEditor,
            $manageEditor,
        ]);

        $editor->givePermissionTo($accessEditor);
    }
}
