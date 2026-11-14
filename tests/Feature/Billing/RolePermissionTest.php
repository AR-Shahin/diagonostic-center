<?php

use App\Models\Admin;
use Database\Seeders\AccountsPermissionSeeder;
use Database\Seeders\BillingPermissionSeeder;
use Database\Seeders\CatalogPermissionSeeder;
use Database\Seeders\LabPermissionSeeder;
use Database\Seeders\ReportPermissionSeeder;
use Database\Seeders\WebsiteContentSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['admin-create', 'admin-view', 'admin-edit', 'admin-delete', 'role-create', 'role-view', 'role-delete', 'permission-create', 'permission-view', 'permission-update', 'permission-delete'] as $name) {
        Permission::create(['name' => $name, 'guard_name' => 'admin']);
    }
    $this->seed([CatalogPermissionSeeder::class, BillingPermissionSeeder::class, LabPermissionSeeder::class,
        AccountsPermissionSeeder::class, ReportPermissionSeeder::class, WebsiteContentSeeder::class]);
    $this->owner = Admin::create(['name' => 'Owner', 'email' => 'owner@test.com', 'password' => 'secret']);
    $this->superRole = Role::findByName('Super Admin', 'admin');
    $this->superRole->givePermissionTo(Permission::where('guard_name', 'admin')->get());
    $this->owner->assignRole($this->superRole);
    $this->desk = Role::findByName('Receptionist', 'admin');
    $this->ids = fn (array $names) => Permission::whereIn('name', $names)->where('guard_name', 'admin')->pluck('id')->all();
});

it('shows permissions in plain language, grouped', function () {
    $this->actingAs($this->owner, 'admin')->get(route('admin.roles.assign_permission', $this->desk))
        ->assertOk()->assertSee('Billing &amp; patients', false)->assertSee('Cancel &amp; refund', false)->assertSee('Verify &amp; sign reports', false);
});

it('saves the chosen permissions', function () {
    $this->actingAs($this->owner, 'admin')->post(route('admin.roles.assign__permission', $this->desk), ['permissions' => ($this->ids)(['invoice-view', 'lab-view'])])
        ->assertRedirect(route('admin.roles.assign_permission', $this->desk));

    expect($this->desk->fresh()->permissions->pluck('name')->sort()->values()->all())->toBe(['invoice-view', 'lab-view']);
});

it('stops you removing your own access to this page', function () {
    $this->actingAs($this->owner, 'admin')->post(route('admin.roles.assign__permission', $this->superRole), ['permissions' => ($this->ids)(['invoice-view'])])
        ->assertSessionHasErrors('permissions');

    expect($this->superRole->fresh()->permissions()->count())->toBeGreaterThan(50);
});

it('does not let staff without role permissions open or change roles', function () {
    $clerk = Admin::create(['name' => 'Clerk', 'email' => 'clerk@test.com', 'password' => 'secret']);
    $clerk->assignRole($this->desk);

    $this->actingAs($clerk, 'admin')->get(route('admin.roles.assign_permission', $this->desk))->assertForbidden();
    $this->actingAs($clerk, 'admin')->post(route('admin.roles.assign__permission', $this->desk), ['permissions' => ($this->ids)(['role-create', 'admin-create'])])->assertForbidden();
    expect($this->desk->fresh()->hasPermissionTo('admin-create', 'admin'))->toBeFalse();
});
