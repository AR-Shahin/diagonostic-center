<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\PermissionCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {

        $this->authorize('role-view');
        $roles = Role::whereGuardName('admin')->get();

        return view('admin.role.index', compact('roles'));
    }

    public function storeAndUpdate(Request $request, Role $role)
    {
        $this->authorize('role-create');
        if (! $role) {
            $request->validate([
                'name' => ['required', 'string', 'unique:roles,name'],
            ]);
        }

        $message = 'Role Updated!';
        if (! $role) {
            $role = new Role;
            $message = 'Role Created!';
        }
        $role->fill([
            'name' => $request->name,
            'guard_name' => 'admin',
        ]);

        $role->save();
        $this->successAlert($message);

        return redirect()->route('admin.roles.index');
    }

    public function delete(Role $role)
    {
        $this->authorize('role-delete');
        $message = 'Already Assigned in a Permission!';
        if (! $role->permissions()->exists()) {
            $role->delete();
            $message = 'Role Deleted!';
            $this->successAlert($message);
        } else {
            $this->warningAlert($message);
        }

        return redirect()->back();
    }

    public function assignPermission(Role $role)
    {
        $this->authorize('role-view');

        $permissions = Permission::whereGuardName('admin')->orderBy('name')->get();
        $otherRoles = Role::whereGuardName('admin')->whereKeyNot($role->id)->with('permissions:id')->orderBy('name')->get()
            ->mapWithKeys(fn (Role $r) => [$r->id => ['name' => $r->name, 'permissions' => $r->permissions->pluck('id')->all()]]);

        return view('admin.role.assign-permission', [
            'role' => $role,
            'areas' => PermissionCatalog::group($permissions),
            'alreadyGiven' => $role->permissions()->pluck('id')->all(),
            'otherRoles' => $otherRoles,
            'staffCount' => DB::table(config('permission.table_names.model_has_roles'))->where('role_id', $role->id)->count(),
            'isOwnRole' => (bool) auth('admin')->user()?->hasRole($role),
            'canSave' => in_array('role-create', $this->admin_permissions, true),
        ]);
    }

    public function assignPermissionStore(Request $request, Role $role)
    {
        $this->authorize('role-create');

        $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')->where('guard_name', 'admin')],
        ]);

        $permissions = Permission::whereIn('id', $request->input('permissions', []))->where('guard_name', 'admin')->get();

        // Never let someone remove their own way back to this screen.
        $admin = auth('admin')->user();
        if ($admin?->hasRole($role)) {
            $fromOtherRoles = $admin->roles->where('id', '!=', $role->id)->flatMap->permissions->pluck('name')
                ->merge($admin->getDirectPermissions()->pluck('name'));
            $after = $permissions->pluck('name')->merge($fromOtherRoles);
            if (! $after->contains('role-view') || ! $after->contains('role-create')) {
                return back()->withErrors(['permissions' => 'You have this role. Keep “Roles · See roles” and “Roles · Create roles & set permissions”, or you would lock yourself out of this page.']);
            }
        }

        $role->syncPermissions($permissions);
        $this->successAlert('Permissions saved for '.$role->name.'.');

        return redirect()->route('admin.roles.assign_permission', $role);
    }
}
