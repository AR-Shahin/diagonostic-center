<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync permissions based on admin routes and controllers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $routes = Route::getRoutes();
        $permissions = [];

        foreach ($routes as $route) {
            $name = $route->getName();
            $action = $route->getActionName();

            // Only process routes that have a name starting with 'admin.'
            if ($name && str_starts_with($name, 'admin.') && $action !== 'Closure') {
                $parts = explode('@', $action);
                if (count($parts) < 2) {
                    // Check for controller array syntax [Controller::class, 'method']
                    $controllerAction = $route->getAction('controller');
                    if ($controllerAction) {
                        $parts = explode('@', $controllerAction);
                    }
                }

                if (count($parts) === 2) {
                    $controller = $parts[0];
                    $method = $parts[1];

                    // Extract module name from controller
                    $className = class_basename($controller);
                    $module = Str::lower(str_replace('Controller', '', $className));

                    // Skip some generic modules if necessary
                    if (in_array($module, ['dashboard', 'login', 'forgotpassword', 'resetpassword'])) {
                        continue;
                    }

                    // Map methods to standardized actions
                    $permissionAction = $this->mapMethodToAction($method);

                    if ($permissionAction) {
                        $permissionName = "{$module}-{$permissionAction}";
                        $permissions[$permissionName] = [
                            'name' => $permissionName,
                            'guard_name' => 'admin',
                        ];
                    }
                }
            }
        }

        $this->info('Syncing ' . count($permissions) . ' permissions...');

        foreach ($permissions as $perm) {
            Permission::firstOrCreate($perm);
        }

        $this->info('Permissions synced successfully!');
    }

    /**
     * Map controller methods to standardized permission actions.
     */
    private function mapMethodToAction($method)
    {
        $map = [
            'index' => 'view',
            'data_table' => 'view',
            'show' => 'view',
            'create' => 'create',
            'store' => 'create',
            'edit' => 'edit',
            'update' => 'edit',
            'storeAndUpdate' => 'edit',
            'delete' => 'delete',
            'destroy' => 'delete',
            'assignPermission' => 'assign-permission',
            'assignPermissionStore' => 'assign-permission',
        ];

        return $map[$method] ?? null;
    }
}
