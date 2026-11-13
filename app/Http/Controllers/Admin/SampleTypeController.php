<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Models\SampleType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SampleTypeController extends Controller
{
    use CrudHelpers;

    public function index(Request $request)
    {
        $this->allow('sampletype-view');

        if ($request->ajax()) {
            return $this->table(SampleType::query()->orderBy('name_en'))
                ->addColumn('state', fn (SampleType $s) => $this->statusBadge($s->status))
                ->addColumn('actions', fn (SampleType $s) => $this->rowActions(
                    'sampletype', route('admin.sample-types.destroy', $s), route('admin.sample-types.update', $s),
                    $s->only(['name_en', 'name_bn', 'container', 'status']), $s->name_en
                ))
                ->rawColumns(['state', 'actions'])
                ->make(true);
        }

        return view('admin.sample-type.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->allow('sampletype-create');
        SampleType::create($this->validated($request));
        $this->createdAlert();

        return back();
    }

    public function update(Request $request, SampleType $sampleType): RedirectResponse
    {
        $this->allow('sampletype-edit');
        $sampleType->update($this->validated($request));
        $this->updatedAlert();

        return back();
    }

    public function destroy(SampleType $sampleType): RedirectResponse
    {
        $this->allow('sampletype-delete');

        return $this->safeDelete($sampleType, 'This sample type is in use.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'container' => ['nullable', 'string', 'max:255'],
        ]) + ['status' => $request->boolean('status')];
    }
}
