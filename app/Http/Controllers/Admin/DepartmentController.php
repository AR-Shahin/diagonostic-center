<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    use CrudHelpers;

    public const ICONS = [
        'bi-eyedropper' => 'Eyedropper', 'bi-droplet-half' => 'Droplet half', 'bi-droplet-fill' => 'Droplet',
        'bi-virus' => 'Virus', 'bi-radioactive' => 'Radiology', 'bi-heart-pulse' => 'Heart pulse', 'bi-soundwave' => 'Sound wave',
        'bi-lungs' => 'Lungs', 'bi-capsule' => 'Capsule', 'bi-clipboard2-pulse' => 'Clipboard pulse', 'bi-activity' => 'Activity',
        'bi-gender-female' => 'Women', 'bi-person-standing' => 'Person', 'bi-bandaid' => 'Bandaid', 'bi-hospital' => 'Hospital',
    ];

    public function index(Request $request)
    {
        $this->allow('department-view');

        if ($request->ajax()) {
            return $this->table(Department::query()->withCount('labTests')->orderBy('sort')->orderBy('name_en'))
                ->editColumn('name_en', fn (Department $d) => '<i class="bi '.e($d->icon).' text-primary me-1"></i> '.e($d->name_en)
                    .'<div class="small text-muted">'.e($d->name_bn).'</div>')
                ->addColumn('website', fn (Department $d) => $this->statusBadge($d->show_on_website, 'Shown', 'Hidden'))
                ->addColumn('state', fn (Department $d) => $this->statusBadge($d->status))
                ->addColumn('actions', fn (Department $d) => $this->rowActions(
                    'department', route('admin.departments.destroy', $d), route('admin.departments.update', $d),
                    $d->only(['name_en', 'name_bn', 'slug', 'icon', 'description_en', 'description_bn', 'highlights_en', 'highlights_bn', 'show_on_website', 'status', 'sort']),
                    $d->name_en
                ))
                ->rawColumns(['name_en', 'website', 'state', 'actions'])
                ->make(true);
        }

        return view('admin.department.index', ['icons' => self::ICONS]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->allow('department-create');
        $this->save(new Department, $request);
        $this->createdAlert();

        return back();
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $this->allow('department-edit');
        $this->save($department, $request);
        $this->updatedAlert();

        return back();
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->allow('department-delete');

        return $this->safeDelete($department, 'This department still has tests. Move or delete them first, or mark the department inactive.');
    }

    private function save(Department $department, Request $request): void
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('name_en'))]);

        $data = $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('departments')->ignore($department->id)],
            'icon' => ['required', Rule::in(array_keys(self::ICONS))],
            'description_en' => ['nullable', 'string', 'max:2000'],
            'description_bn' => ['nullable', 'string', 'max:2000'],
            'highlights_en' => ['nullable', 'string', 'max:2000'],
            'highlights_bn' => ['nullable', 'string', 'max:2000'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:65000'],
        ]);

        $data['sort'] = (int) ($data['sort'] ?? 0);
        $data['show_on_website'] = $request->boolean('show_on_website');
        $data['status'] = $request->boolean('status');

        $department->fill($data)->save();
    }
}
