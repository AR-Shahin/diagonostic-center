<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        $permissions = Permission::whereGuardName('admin')->orderBy('name')->get();
        $alreadyGiven = $role->permissions()->pluck('id')->toArray();

        $modules = [];

        foreach ($permissions as $item) {
            $parts = explode('-', $item->name);
            $moduleName = $parts[0] ?? 'other';
            $action = $parts[1] ?? 'view';

            if (!isset($modules[$moduleName])) {
                $modules[$moduleName] = [];
            }

            $modules[$moduleName][] = [
                'id' => $item->id,
                'name' => $item->name,
                'action' => $action,
                'display_name' => ucwords(str_replace('-', ' ', $item->name)),
            ];
        }

        return view('admin.role.assign-permission', compact('modules', 'role', 'alreadyGiven'));
    }

    public function assignPermissionStore(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $permissions = Permission::whereIn('id', is_null($request->permissions) ? [] : $request->permissions)->get();
        $role->syncPermissions($permissions);
        $this->successAlert('Assigned Permission to this Role.');

        return redirect()->back();
    }
}
