<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Category;
use App\Models\ExpenseCategory;
use App\Models\LabTest;
use App\Models\WebsiteInfo;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * php artisan db:seed — safe to run again at any time, also on a live database:
 *  - new permissions in PERMISSIONS are added and given to the roles listed for them in ROLES;
 *  - a role you created or changed on the permission page keeps your choices (nothing is taken away,
 *    and an existing permission you removed from a role is not put back);
 *  - the starter accounts, expense categories and FAQ are only added when missing.
 */
class DatabaseSeeder extends Seeder
{
    /** Every admin-panel permission, as module => actions. A permission is "module-action", e.g. invoice-cancel. */
    public const PERMISSIONS = [
        // Staff & access
        'admin' => ['view', 'create', 'edit', 'delete'],
        'role' => ['view', 'create', 'delete'],
        'permission' => ['view', 'create', 'update', 'delete'],
        // Diagnostic setup
        'branch' => ['view', 'create', 'edit', 'delete'],
        'department' => ['view', 'create', 'edit', 'delete'],
        'sampletype' => ['view', 'create', 'edit', 'delete'],
        'unit' => ['view', 'create', 'edit', 'delete'],
        'labtest' => ['view', 'create', 'edit', 'delete'],
        'package' => ['view', 'create', 'edit', 'delete'],
        'businesssetting' => ['view', 'edit'],
        // Billing & patients
        'patient' => ['view', 'create', 'edit', 'delete'],
        'referrer' => ['view', 'create', 'edit', 'delete'],
        'invoice' => ['view', 'create', 'collect', 'cancel'],
        // Lab
        'lab' => ['view', 'collect', 'result', 'verify', 'deliver'],
        // Accounts & reports
        'commission' => ['view', 'rates', 'pay'],
        'expense' => ['view', 'create', 'edit', 'delete'],
        'cashclosing' => ['create', 'view', 'verify'],
        'report' => ['view', 'profit'],
        // Website
        'webrequest' => ['view', 'handle'],
        'webcontent' => ['edit', 'delete'],
    ];

    /**
     * Default permissions of each role. '*' = everything, 'module' = all of that module, '*-view' = every view permission.
     * Super Admin also has full access by itself (config/diagnostic.php → super_admin); its ticks matter only when that is off.
     */
    public const ROLES = [
        'Super Admin' => ['*'],
        'Admin' => [],
        'Viewer' => ['*-view'],
        'Manager' => [
            'patient', 'referrer', 'invoice', 'lab', 'commission', 'expense', 'cashclosing', 'report', 'webrequest', 'webcontent',
            'labtest-view', 'labtest-create', 'labtest-edit', 'package-view', 'package-create', 'package-edit',
            'department-view', 'branch-view', 'businesssetting-view',
        ],
        'Receptionist' => [
            'patient-view', 'patient-create', 'patient-edit', 'referrer-view', 'referrer-create',
            'invoice-view', 'invoice-create', 'invoice-collect',
            'labtest-view', 'package-view', 'department-view', 'branch-view',
            'lab-view', 'lab-collect', 'lab-deliver',
            'expense-view', 'expense-create', 'cashclosing-create',
            'webrequest-view', 'webrequest-handle',
        ],
        'Accountant' => [
            'invoice-view', 'invoice-collect', 'patient-view', 'referrer-view', 'branch-view',
            'commission-view', 'commission-pay', 'expense-view', 'expense-create', 'expense-edit',
            'cashclosing-create', 'cashclosing-view', 'cashclosing-verify', 'report-view',
        ],
        'Lab Technologist' => ['lab-view', 'lab-collect', 'lab-result', 'labtest-view'],
        'Consultant' => ['lab-view', 'lab-result', 'lab-verify', 'labtest-view', 'invoice-view'],
    ];

    public function run(): void
    {
        self::rolesAndPermissions();

        $this->starterAccounts();
        WebsiteInfo::query()->exists() || WebsiteInfo::create([]);
        foreach (['Salary', 'Rent', 'Electricity', 'Reagents & kits', 'Consumables', 'Equipment maintenance', 'Transport', 'Office & stationery', 'Marketing', 'Others'] as $name) {
            ExpenseCategory::firstOrCreate(['name' => $name]);
        }
        if (! Category::query()->exists()) {
            $this->call(CategorySeeder::class);
        }
        $this->call(WebsiteContentSeeder::class);

        if (app()->isLocal() && ! LabTest::query()->exists()) {
            $this->call([DemoCatalogSeeder::class, DemoReferrerSeeder::class, DemoWebsiteSeeder::class]);
        }
    }

    /** Creates missing permissions and roles and gives roles their defaults. Also used by the tests. */
    public static function rolesAndPermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $all = [];
        $new = [];
        foreach (self::PERMISSIONS as $module => $actions) {
            foreach ($actions as $action) {
                $permission = Permission::firstOrCreate(['name' => "$module-$action", 'guard_name' => 'admin']);
                $all[] = $permission->name;
                if ($permission->wasRecentlyCreated) {
                    $new[] = $permission->name;
                }
            }
        }

        foreach (self::ROLES as $roleName => $rules) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'admin']);
            $defaults = self::expand($rules, $all);
            // A brand-new role gets all its defaults; an existing one only the permissions that did not exist before.
            $give = $role->wasRecentlyCreated ? $defaults : array_values(array_intersect($defaults, $new));
            if ($give) {
                $role->givePermissionTo($give);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private static function expand(array $rules, array $all): array
    {
        $names = [];
        foreach ($rules as $rule) {
            $names = array_merge($names, match (true) {
                $rule === '*' => $all,
                $rule === '*-view' => array_filter($all, fn ($n) => str_ends_with($n, '-view')),
                str_contains($rule, '-') => [$rule],
                default => array_filter($all, fn ($n) => str_starts_with($n, $rule.'-')),
            });
        }

        return array_values(array_unique(array_intersect($names, $all)));
    }

    /** Login accounts for a fresh install. Change these passwords after the first login. */
    private function starterAccounts(): void
    {
        $accounts = [
            ['Super Admin', 'admin@mail.com', 'Super Admin'],
            ['Viewer', 'viewer@mail.com', 'Viewer'],
            ['Shahin', 'mdshahinmije96@gmail.com', 'Super Admin'],
        ];
        foreach ($accounts as [$name, $email, $role]) {
            $admin = Admin::firstOrCreate(['email' => $email], ['name' => $name, 'password' => bcrypt('password')]);
            if ($admin->roles()->doesntExist()) {
                $admin->assignRole(Role::findByName($role, 'admin'));
            }
        }
    }
}
