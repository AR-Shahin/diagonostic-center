<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Phase 3 (lab) permissions and roles. Safe to re-run:
 * php artisan db:seed --class=LabPermissionSeeder
 */
class LabPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $lab = ['lab-view', 'lab-collect', 'lab-result', 'lab-verify', 'lab-deliver'];
        foreach ($lab as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
        }

        $roles = [
            'Super Admin' => $lab,
            'Manager' => $lab,
            'Receptionist' => ['lab-view', 'lab-collect', 'lab-deliver'],
            'Lab Technologist' => ['lab-view', 'lab-collect', 'lab-result', 'labtest-view'],
            'Consultant' => ['lab-view', 'lab-result', 'lab-verify', 'labtest-view', 'invoice-view'],
        ];
        foreach ($roles as $roleName => $permissions) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'admin'])
                ->givePermissionTo(Permission::whereIn('name', $permissions)->where('guard_name', 'admin')->get());
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
