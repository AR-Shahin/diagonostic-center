<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Plain-language names for permissions ("invoice-cancel" → Bills · Cancel & refund), grouped the way
 * the sidebar is. Permissions not listed here still appear, under "Other", with a tidied name.
 */
class PermissionCatalog
{
    public const AREAS = [
        'billing' => ['Billing & patients', 'fas fa-file-invoice', 'Counter work: bills, payments, patients and referring doctors.'],
        'lab' => ['Lab', 'fas fa-microscope', 'Samples, results, verification and report delivery.'],
        'accounts' => ['Accounts & reports', 'fas fa-chart-pie', 'Commission, expenses, cash closing and business reports.'],
        'website' => ['Website', 'fas fa-globe', 'Requests from the website and the content shown on it.'],
        'setup' => ['Diagnostic setup', 'fas fa-flask', 'Tests, packages, departments, branches and business rules.'],
        'system' => ['Staff & access', 'fas fa-user-shield', 'Staff accounts, roles and permissions.'],
        'other' => ['Other', 'fas fa-ellipsis-h', 'Permissions added later that have no group yet.'],
    ];

    /** module => [area, label, description, [action => label]] ; a "!" before a label marks it as sensitive. */
    public const MODULES = [
        'invoice' => ['billing', 'Bills', 'Billing counter and invoice list.', ['view' => 'See bills & dues', 'create' => 'Make new bills', 'collect' => 'Take payments', 'cancel' => '!Cancel & refund']],
        'patient' => ['billing', 'Patients', 'Patient records and phone accounts.', []],
        'referrer' => ['billing', 'Referrers', 'Referring doctors and agents.', []],
        'lab' => ['lab', 'Lab work', 'The lab work list.', ['view' => 'See work list', 'collect' => 'Collect & receive samples', 'result' => 'Enter results', 'verify' => '!Verify & sign reports', 'deliver' => 'Hand over reports']],
        'commission' => ['accounts', 'Referral commission', 'What referrers earn and are paid.', ['view' => 'See commission', 'rates' => '!Set rates', 'pay' => '!Pay commission']],
        'expense' => ['accounts', 'Expenses', 'Rent, salary and other costs.', []],
        'cashclosing' => ['accounts', 'Cash closing', 'End-of-day cash count.', ['create' => 'Close own day', 'view' => 'See everyone’s closings', 'verify' => '!Check closings']],
        'report' => ['accounts', 'Reports', 'Sales, collection, staff and turnaround reports.', ['view' => 'Business reports', 'profit' => '!Profit & loss']],
        'webrequest' => ['website', 'Website requests', 'Appointments and messages sent from the website.', ['view' => 'See requests', 'handle' => 'Update requests']],
        'webcontent' => ['website', 'Website content', 'Doctors, articles, FAQ, reviews and home page text.', ['edit' => 'Add & edit content', 'delete' => 'Delete content']],
        'labtest' => ['setup', 'Tests', 'Test list, prices and reference ranges.', []],
        'package' => ['setup', 'Health packages', 'Package prices and contents.', []],
        'department' => ['setup', 'Departments', 'Pathology, Radiology and other groups.', []],
        'sampletype' => ['setup', 'Sample types', 'Blood, urine and other samples.', []],
        'unit' => ['setup', 'Units', 'Units for results (mg/dL…).', []],
        'branch' => ['setup', 'Branches', 'Branch addresses and services.', []],
        'businesssetting' => ['setup', 'Business rules', 'VAT, discount and commission rules.', ['view' => 'See rules', 'edit' => '!Change rules']],
        'admin' => ['system', 'Staff accounts', 'Who can log in to the admin panel.', ['create' => '!Add staff', 'edit' => '!Edit staff', 'delete' => '!Remove staff']],
        'role' => ['system', 'Roles', 'Roles and what each one may do.', ['view' => 'See roles', 'create' => '!Create roles & set permissions', 'delete' => '!Delete roles']],
        'permission' => ['system', 'Permission list', 'The master list of permissions (developers).', ['view' => 'See list', 'create' => '!Add', 'update' => '!Edit', 'delete' => '!Delete']],
    ];

    private const ACTION_LABELS = ['view' => 'View', 'create' => 'Add', 'edit' => 'Edit', 'update' => 'Edit', 'delete' => 'Delete'];

    private const ACTION_ORDER = ['view', 'create', 'edit', 'update', 'collect', 'result', 'handle', 'rates', 'pay', 'verify', 'deliver', 'profit', 'cancel', 'delete'];

    /**
     * @param  Collection  $permissions  Permission models
     * @return array<string, array{label:string, icon:string, description:string, modules:array}>
     */
    public static function group(Collection $permissions): array
    {
        $modules = [];
        foreach ($permissions as $permission) {
            [$module, $action] = array_pad(explode('-', $permission->name, 2), 2, 'view');
            $def = self::MODULES[$module] ?? ['other', Str::headline($module), '', []];
            $label = $def[3][$action] ?? (self::ACTION_LABELS[$action] ?? Str::headline($action));
            $sensitive = str_starts_with($label, '!') || $action === 'delete';

            $modules[$def[0]][$module] ??= ['key' => $module, 'label' => $def[1], 'description' => $def[2], 'actions' => []];
            $modules[$def[0]][$module]['actions'][] = [
                'id' => $permission->id,
                'name' => $permission->name,
                'action' => $action,
                'label' => ltrim($label, '!'),
                'sensitive' => $sensitive,
            ];
        }

        $areas = [];
        foreach (self::AREAS as $key => [$label, $icon, $description]) {
            if (empty($modules[$key])) {
                continue;
            }
            $list = $modules[$key];
            uksort($list, fn ($a, $b) => self::position($a) <=> self::position($b));
            foreach ($list as &$module) {
                usort($module['actions'], fn ($a, $b) => self::actionPosition($a['action']) <=> self::actionPosition($b['action']));
            }
            $areas[$key] = ['label' => $label, 'icon' => $icon, 'description' => $description, 'modules' => array_values($list)];
        }

        return $areas;
    }

    private static function position(string $module): int
    {
        $index = array_search($module, array_keys(self::MODULES), true);

        return $index === false ? 999 : $index;
    }

    private static function actionPosition(string $action): int
    {
        $index = array_search($action, self::ACTION_ORDER, true);

        return $index === false ? 50 : $index;
    }
}
