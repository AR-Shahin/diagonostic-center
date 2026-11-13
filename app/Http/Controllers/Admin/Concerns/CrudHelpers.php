<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;

trait CrudHelpers
{
    /** Abort with 403 unless the signed-in admin has the permission (e.g. "branch-edit"). */
    protected function allow(string $permission): void
    {
        abort_unless(auth('admin')->user()?->can($permission), 403);
    }

    protected function can(string $permission): bool
    {
        return (bool) auth('admin')->user()?->can($permission);
    }

    protected function statusBadge(bool $active, string $on = 'Active', string $off = 'Inactive'): string
    {
        return $active
            ? '<span class="badge text-bg-success">'.e($on).'</span>'
            : '<span class="badge text-bg-secondary">'.e($off).'</span>';
    }

    /**
     * Edit + delete buttons for a datatable row.
     * Pass $record to open the shared modal, or $editUrl to go to an edit page.
     */
    protected function rowActions(string $module, string $deleteUrl, ?string $editUrl = null, ?array $record = null, string $label = ''): string
    {
        $html = '<div class="d-inline-flex gap-1">';

        if ($this->can("$module-edit")) {
            if ($record !== null) {
                $html .= '<button type="button" class="btn btn-sm btn-outline-primary" data-crud-edit'
                    .' data-action="'.e($editUrl).'" data-title="'.e('Edit '.$label).'"'
                    .' data-record="'.e(json_encode($record, JSON_UNESCAPED_UNICODE)).'" title="Edit"><i class="fas fa-pen"></i></button>';
            } else {
                $html .= '<a class="btn btn-sm btn-outline-primary" href="'.e($editUrl).'" title="Edit"><i class="fas fa-pen"></i></a>';
            }
        }

        if ($this->can("$module-delete")) {
            $html .= '<form method="POST" action="'.e($deleteUrl).'" class="d-inline" onsubmit="return confirm(\'Delete '.e(addslashes($label)).'?\')">'
                .csrf_field().method_field('DELETE')
                .'<button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button></form>';
        }

        return $html.'</div>';
    }

    /** Delete, but explain instead of crashing when other records still use it. */
    protected function safeDelete(Model $model, string $inUseMessage): RedirectResponse
    {
        try {
            $model->delete();
            $this->deletedAlert();
        } catch (QueryException) {
            $this->errorAlert($inUseMessage);
        }

        return back();
    }
}
