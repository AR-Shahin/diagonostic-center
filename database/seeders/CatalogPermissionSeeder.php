<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Phase 1 permissions (setup + catalog). Safe to run on an existing database:
 * php artisan db:seed --class=CatalogPermissionSeeder
 */
class CatalogPermissionSeeder extends Seeder
{
    public const MODULES = ['branch', 'department', 'sampletype', 'unit', 'labtest', 'package'];

    public function run(): void
    {
        $names = [];
        foreach (self::MODULES as $module) {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                $names[] = "$module-$action";
            }
        }
        $names[] = 'businesssetting-view';
        $names[] = 'businesssetting-edit';

        foreach ($names as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
        }

        // Super Admin always gets everything.
        Role::where('name', 'Super Admin')->first()?->givePermissionTo($names);

        // Viewer role (from the starter) can look but not change.
        Role::where('name', 'Viewer')->first()?->givePermissionTo(array_filter($names, fn ($n) => str_ends_with($n, '-view')));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
