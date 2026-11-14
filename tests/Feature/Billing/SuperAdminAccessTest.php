<?php

use App\Models\Admin;
use App\Models\Branch;
use App\Support\BranchAccess;
use Database\Seeders\DatabaseSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    DatabaseSeeder::rolesAndPermissions();

    $this->branch = Branch::create(['code' => 'B2', 'name_en' => 'Branch 2']);
    $this->superRole = Role::findByName('Super Admin', 'admin');
    $this->superRole->syncPermissions([]);                       // nothing ticked at all
    $this->boss = Admin::create(['name' => 'Boss', 'email' => 'boss@test.com', 'password' => 'secret', 'branch_id' => $this->branch->id, 'max_discount_percent' => 0]);
    $this->boss->assignRole($this->superRole);
});

it('gives Super Admin everything with no permission ticked', function () {
    foreach (['admin.reports.index', 'admin.doctors.index', 'admin.roles.index', 'admin.commission.index', 'admin.expenses.index', 'admin.web-requests.index', 'admin.admins.create', 'admin.dashboard'] as $route) {
        $this->actingAs($this->boss, 'admin')->get(route($route))->assertOk();
    }
    $this->actingAs($this->boss, 'admin')->get(route('admin.roles.assign_permission', $this->superRole))
        ->assertOk()->assertSee('This role has full access automatically');
});

it('includes permissions added later, every branch and any discount', function () {
    Permission::create(['name' => 'stock-view', 'guard_name' => 'admin']);

    expect($this->boss->fresh()->permissionNames())->toContain('stock-view')
        ->and($this->boss->can('stock-view'))->toBeTrue()
        ->and(BranchAccess::isRestricted($this->boss))->toBeFalse()
        ->and($this->boss->discountLimit())->toBe(100.0);
});

it('can be switched off in config', function () {
    config(['diagnostic.super_admin.full_access' => false]);

    expect($this->boss->isSuperAdmin())->toBeFalse()->and(BranchAccess::isRestricted($this->boss))->toBeTrue();
    $this->actingAs($this->boss, 'admin')->get(route('admin.reports.index'))->assertForbidden();
});

it('only a Super Admin can make or change a Super Admin', function () {
    $managerRole = Role::findByName('Manager', 'admin');
    $managerRole->givePermissionTo(['admin-create', 'admin-edit', 'admin-view']);
    $manager = Admin::create(['name' => 'Mgr', 'email' => 'mgr@test.com', 'password' => 'secret']);
    $manager->assignRole($managerRole);

    $this->actingAs($manager, 'admin')->get(route('admin.admins.create'))->assertOk()->assertDontSee('>Super Admin<', false);
    $this->actingAs($manager, 'admin')->post(route('admin.admins.store'), [
        'name' => 'Sneaky', 'email' => 'sneaky@test.com', 'password' => 'secret12', 'password_confirmation' => 'secret12', 'role_id' => $this->superRole->id,
    ])->assertSessionHasErrors('role_id');
    expect(Admin::where('email', 'sneaky@test.com')->exists())->toBeFalse();

    $this->actingAs($manager, 'admin')->get(route('admin.admins.edit', $this->boss))->assertForbidden();
});

it('protects the Super Admin role and the last Super Admin', function () {
    $this->actingAs($this->boss, 'admin')->post(route('admin.roles.delete', $this->superRole));
    $this->actingAs($this->boss, 'admin')->post(route('admin.roles.store', $this->superRole), ['name' => 'Owner']);
    expect(Role::where('name', 'Super Admin')->exists())->toBeTrue();

    $this->actingAs($this->boss, 'admin')->post(route('admin.admins.update', $this->boss), [
        'name' => 'Boss', 'email' => 'boss@test.com', 'role_id' => Role::findByName('Manager', 'admin')->id,
    ])->assertSessionHasErrors('role_id');
    expect($this->boss->fresh()->hasRole('Super Admin'))->toBeTrue();
});
