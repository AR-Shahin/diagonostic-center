<?php

use App\Models\Admin;
use Database\Seeders\DatabaseSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('seeds every permission and role, and can run again safely', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    $expected = collect(DatabaseSeeder::PERMISSIONS)->sum(fn ($actions) => count($actions));
    expect(Permission::where('guard_name', 'admin')->count())->toBe($expected)
        ->and(Role::findByName('Super Admin', 'admin')->permissions()->count())->toBe($expected)
        ->and(Role::findByName('Viewer', 'admin')->permissions->pluck('name')->every(fn ($n) => str_ends_with($n, '-view')))->toBeTrue()
        ->and(Admin::where('email', 'admin@mail.com')->first()->hasRole('Super Admin'))->toBeTrue()
        ->and(Admin::count())->toBe(3);
});

it('keeps your changes to a role when run again', function () {
    DatabaseSeeder::rolesAndPermissions();
    $manager = Role::findByName('Manager', 'admin');
    $manager->revokePermissionTo('invoice-cancel');
    $manager->givePermissionTo('admin-view');

    DatabaseSeeder::rolesAndPermissions();

    $manager = $manager->fresh();
    expect($manager->hasPermissionTo('invoice-cancel', 'admin'))->toBeFalse()
        ->and($manager->hasPermissionTo('admin-view', 'admin'))->toBeTrue();
});

it('gives a newly added permission to the roles that list it', function () {
    DatabaseSeeder::rolesAndPermissions();
    Permission::findByName('report-profit', 'admin')->delete();   // as if this permission were new in an update

    DatabaseSeeder::rolesAndPermissions();

    expect(Role::findByName('Manager', 'admin')->hasPermissionTo('report-profit', 'admin'))->toBeTrue()
        ->and(Role::findByName('Accountant', 'admin')->hasPermissionTo('report-profit', 'admin'))->toBeFalse();
});
