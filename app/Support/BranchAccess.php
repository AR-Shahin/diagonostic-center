<?php

namespace App\Support;

use App\Models\Admin;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Staff tied to a branch only see and bill for that branch. Staff without a branch (head office) and
 * Super Admins see all.
 */
class BranchAccess
{
    public static function isRestricted(?Admin $admin): bool
    {
        return $admin !== null && $admin->branch_id !== null && ! $admin->isSuperAdmin();
    }

    public static function scope(Builder $query, ?Admin $admin, string $column = 'branch_id'): Builder
    {
        return self::isRestricted($admin) ? $query->where($column, $admin->branch_id) : $query;
    }

    /** Branches this person can choose on a form. */
    public static function branches(?Admin $admin): Collection
    {
        return Branch::query()->where('status', true)
            ->when(self::isRestricted($admin), fn ($q) => $q->whereKey($admin->branch_id))
            ->orderByDesc('is_main')->orderBy('sort')->get(['id', 'code', 'name_en']);
    }

    public static function canUse(?Admin $admin, int $branchId): bool
    {
        return ! self::isRestricted($admin) || $admin->branch_id === $branchId;
    }
}
