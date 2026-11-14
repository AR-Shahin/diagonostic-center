<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Phase 5 (commission, expenses, cash closing) permissions + default expense categories.
 * Safe to re-run: php artisan db:seed --class=AccountsPermissionSeeder
 */
class AccountsPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $all = [
            'commission-view', 'commission-rates', 'commission-pay',
            'expense-view', 'expense-create', 'expense-edit', 'expense-delete',
            'cashclosing-create', 'cashclosing-view', 'cashclosing-verify',
        ];
        foreach ($all as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
        }

        $roles = [
            'Super Admin' => $all,
            'Manager' => $all,
            'Accountant' => ['commission-view', 'commission-pay', 'expense-view', 'expense-create', 'expense-edit', 'cashclosing-create', 'cashclosing-view', 'cashclosing-verify'],
            'Receptionist' => ['expense-view', 'expense-create', 'cashclosing-create'],
        ];
        foreach ($roles as $roleName => $permissions) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'admin'])
                ->givePermissionTo(Permission::whereIn('name', $permissions)->where('guard_name', 'admin')->get());
        }

        foreach (['Salary', 'Rent', 'Electricity', 'Reagents & kits', 'Consumables', 'Equipment maintenance', 'Transport', 'Office & stationery', 'Marketing', 'Others'] as $name) {
            ExpenseCategory::firstOrCreate(['name' => $name]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
