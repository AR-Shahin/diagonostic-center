<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Phase 6 (reports) permissions. Safe to re-run: php artisan db:seed --class=ReportPermissionSeeder
 * report-view   = sales, collection, department, test, staff, referrer, turnaround, dues reports
 * report-profit = profit & loss (owners and managers only by default)
 */
class ReportPermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['report-view', 'report-profit'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
        }

        foreach ([
            'Super Admin' => ['report-view', 'report-profit'],
            'Manager' => ['report-view', 'report-profit'],
            'Accountant' => ['report-view'],
        ] as $roleName => $permissions) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'admin'])->givePermissionTo($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
