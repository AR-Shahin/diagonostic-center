<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = ['admin-create', 'admin-view', 'admin-edit', 'admin-delete', 'role-create', 'role-view', 'role-delete', 'permission-create', 'permission-view', 'permission-update', 'permission-delete'];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
            ]);
        }

    }
}
