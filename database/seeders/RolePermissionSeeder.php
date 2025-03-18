<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    protected $modules = [
        'conference' => [
            'create_conference',
            'edit_conference',
            'delete_conference',
            'view_conference',
            'assign_editor',
        ],
        'user' => [
            'create_user',
            'edit_user',
            'delete_user',
            'view_user',
            'assign_role',
        ],
        'page' => [
            'create_page',
            'edit_page',
            'delete_page',
            'view_page',
        ],
        'file' => [
            'upload_file',
            'delete_file',
        ],
    ];

    protected $rolePermissions = [
        'admin' => ['conference', 'user', 'page', 'file'],
        'editor' => ['page', 'file'],
    ];

    public function run()
    {
        $this->createPermissions();
        $this->createRoles();
    }

    protected function createPermissions()
    {
        foreach ($this->modules as $module => $permissions) {
            foreach ($permissions as $permissionName) {
                Permission::firstOrCreate(['name' => $permissionName]);
            }
        }
    }

    protected function createRoles()
    {
        foreach ($this->rolePermissions as $roleName => $allowedModules) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            $permissions = collect($allowedModules)
                ->flatMap(fn($module) => $this->modules[$module])
                ->all();

            $role->syncPermissions($permissions);
        }
    }
}