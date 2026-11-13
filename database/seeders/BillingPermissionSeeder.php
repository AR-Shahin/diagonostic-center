<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Phase 2 permissions + the counter roles. Safe to re-run on an existing database:
 * php artisan db:seed --class=BillingPermissionSeeder
 */
class BillingPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $billing = [
            'patient-view', 'patient-create', 'patient-edit', 'patient-delete',
            'referrer-view', 'referrer-create', 'referrer-edit', 'referrer-delete',
            'invoice-view', 'invoice-create', 'invoice-collect', 'invoice-cancel',
        ];
        foreach ($billing as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
        }

        $catalogView = ['labtest-view', 'package-view', 'department-view', 'branch-view'];
        $roles = [
            // everything in setup, catalog and billing
            'Super Admin' => Permission::where('guard_name', 'admin')->pluck('name')->all(),
            'Manager' => array_merge($billing, $catalogView, [
                'labtest-create', 'labtest-edit', 'package-create', 'package-edit', 'businesssetting-view',
            ]),
            'Receptionist' => array_merge($catalogView, [
                'patient-view', 'patient-create', 'patient-edit', 'referrer-view', 'referrer-create',
                'invoice-view', 'invoice-create', 'invoice-collect',
            ]),
            'Accountant' => ['invoice-view', 'invoice-collect', 'patient-view', 'referrer-view', 'branch-view'],
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'admin']);
            $role->givePermissionTo(Permission::whereIn('name', $permissions)->where('guard_name', 'admin')->get());
        }

        // Super Admins can approve any discount unless a limit was already set.
        Admin::role('Super Admin')->where('max_discount_percent', 0)->update(['max_discount_percent' => 100]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
