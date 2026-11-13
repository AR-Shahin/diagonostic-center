<?php

namespace App\Http\Controllers\Admin;

use App\Helper\File\File;
use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    use CrudHelpers;

    public function index(Request $request)
    {
        $this->allow('branch-view');

        if ($request->ajax()) {
            return $this->table(Branch::query()->orderBy('sort')->orderBy('name_en'))
                ->editColumn('name_en', fn (Branch $b) => e($b->name_en).($b->is_main ? ' <span class="badge text-bg-primary ms-1">Main</span>' : '')
                    .'<div class="small text-muted">'.e($b->name_bn).'</div>')
                ->addColumn('website', fn (Branch $b) => $this->statusBadge($b->show_on_website, 'Shown', 'Hidden'))
                ->addColumn('state', fn (Branch $b) => $this->statusBadge($b->status))
                ->addColumn('actions', fn (Branch $b) => $this->rowActions(
                    'branch', route('admin.branches.destroy', $b), route('admin.branches.update', $b),
                    $b->only($this->fields()), $b->name_en
                ))
                ->rawColumns(['name_en', 'website', 'state', 'actions'])
                ->make(true);
        }

        return view('admin.branch.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->allow('branch-create');
        $this->save(new Branch, $request);
        $this->createdAlert();

        return back();
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $this->allow('branch-edit');
        $this->save($branch, $request);
        $this->updatedAlert();

        return back();
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $this->allow('branch-delete');

        return $this->safeDelete($branch, 'This branch is in use and cannot be deleted. Mark it inactive instead.');
    }

    private function save(Branch $branch, Request $request): void
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('branches')->ignore($branch->id)],
            'name_en' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address_en' => ['nullable', 'string', 'max:1000'],
            'address_bn' => ['nullable', 'string', 'max:1000'],
            'opening_hours_en' => ['nullable', 'string', 'max:255'],
            'opening_hours_bn' => ['nullable', 'string', 'max:255'],
            'facilities_en' => ['nullable', 'string', 'max:2000'],
            'facilities_bn' => ['nullable', 'string', 'max:2000'],
            'map_url' => ['nullable', 'url', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:65000'],
        ]);

        $data['code'] = strtoupper($data['code']);
        $data['sort'] = (int) ($data['sort'] ?? 0);
        $data['is_main'] = $request->boolean('is_main');
        $data['show_on_website'] = $request->boolean('show_on_website');
        $data['status'] = $request->boolean('status');
        unset($data['image']);

        if ($request->hasFile('image')) {
            if ($branch->image) {
                File::deleteFile($branch->image);
            }
            $data['image'] = File::upload($request->file('image'), 'branches');
        }

        DB::transaction(function () use ($branch, $data) {
            if ($data['is_main']) {
                Branch::where('id', '!=', $branch->id ?? 0)->update(['is_main' => false]);
            }
            $branch->fill($data)->save();
        });
    }

    private function fields(): array
    {
        return ['code', 'name_en', 'name_bn', 'phone', 'email', 'address_en', 'address_bn', 'opening_hours_en', 'opening_hours_bn',
            'facilities_en', 'facilities_bn', 'map_url', 'sort', 'is_main', 'show_on_website', 'status'];
    }
}
