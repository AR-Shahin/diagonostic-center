<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    use CrudHelpers;

    public function index(Request $request)
    {
        $this->allow('unit-view');

        if ($request->ajax()) {
            return $this->table(Unit::query()->orderBy('name'))
                ->addColumn('state', fn (Unit $u) => $this->statusBadge($u->status))
                ->addColumn('actions', fn (Unit $u) => $this->rowActions(
                    'unit', route('admin.units.destroy', $u), route('admin.units.update', $u), $u->only(['name', 'status']), $u->name
                ))
                ->rawColumns(['state', 'actions'])
                ->make(true);
        }

        return view('admin.unit.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->allow('unit-create');
        Unit::create($this->validated($request));
        $this->createdAlert();

        return back();
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $this->allow('unit-edit');
        $unit->update($this->validated($request, $unit));
        $this->updatedAlert();

        return back();
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $this->allow('unit-delete');

        return $this->safeDelete($unit, 'This unit is in use.');
    }

    private function validated(Request $request, ?Unit $unit = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('units')->ignore($unit?->id)],
        ]) + ['status' => $request->boolean('status')];
    }
}
