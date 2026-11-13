<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Models\LabTest;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PackageController extends Controller
{
    use CrudHelpers;

    public function index(Request $request)
    {
        $this->allow('package-view');

        if ($request->ajax()) {
            return $this->table(Package::query()->with('labTests:id,price')->withCount('labTests')->orderBy('sort'))
                ->editColumn('name_en', fn (Package $p) => '<i class="bi '.e($p->icon).' text-primary me-1"></i> '.e($p->name_en)
                    .($p->is_featured ? ' <span class="badge text-bg-warning">Featured</span>' : '')
                    .'<div class="small text-muted">'.e($p->name_bn).'</div>')
                ->editColumn('price', fn (Package $p) => '<b>৳ '.number_format((float) $p->price).'</b>')
                ->addColumn('regular', fn (Package $p) => '৳ '.number_format($p->regularPrice()))
                ->addColumn('saving', function (Package $p) {
                    $regular = $p->regularPrice();

                    return $regular > 0 ? '৳ '.number_format($p->savingAmount()).' <span class="text-muted small">('.round($p->savingAmount() / $regular * 100).'%)</span>' : '—';
                })
                ->addColumn('validity', fn (Package $p) => $p->isCurrentlyValid()
                    ? ($p->valid_until ? 'Until '.$p->valid_until->format('d M Y') : 'Always')
                    : '<span class="text-danger">Expired / not started</span>')
                ->addColumn('website', fn (Package $p) => $this->statusBadge($p->show_on_website, 'Shown', 'Hidden'))
                ->addColumn('state', fn (Package $p) => $this->statusBadge($p->status))
                ->addColumn('actions', fn (Package $p) => $this->rowActions('package', route('admin.packages.destroy', $p), route('admin.packages.edit', $p), null, $p->name_en))
                ->rawColumns(['name_en', 'price', 'saving', 'validity', 'website', 'state', 'actions'])
                ->make(true);
        }

        return view('admin.package.index');
    }

    public function create()
    {
        $this->allow('package-create');

        return view('admin.package.form', $this->formData(new Package(['status' => true, 'show_on_website' => true, 'gender' => 'any', 'icon' => 'bi-clipboard2-pulse'])));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->allow('package-create');
        $package = $this->save(new Package, $request);
        $this->createdAlert();

        return redirect()->route('admin.packages.edit', $package);
    }

    public function edit(Package $package)
    {
        $this->allow('package-edit');
        $package->load('labTests:id');

        return view('admin.package.form', $this->formData($package));
    }

    public function update(Request $request, Package $package): RedirectResponse
    {
        $this->allow('package-edit');
        $this->save($package, $request);
        $this->updatedAlert();

        return back();
    }

    public function destroy(Package $package): RedirectResponse
    {
        $this->allow('package-delete');

        return $this->safeDelete($package, 'This package is in use and cannot be deleted. Mark it inactive instead.');
    }

    private function formData(Package $package): array
    {
        return [
            'package' => $package,
            'tests' => LabTest::query()->with('department:id,name_en')->where('status', true)
                ->orderBy('department_id')->orderBy('sort')->orderBy('name_en')->get(['id', 'code', 'name_en', 'price', 'department_id']),
            'selected' => collect(old('tests', $package->exists ? $package->labTests->pluck('id')->all() : []))->map(fn ($v) => (int) $v)->all(),
        ];
    }

    private function save(Package $package, Request $request): Package
    {
        $request->merge([
            'code' => strtoupper(trim((string) $request->input('code'))),
            'slug' => Str::slug($request->input('slug') ?: $request->input('name_en')),
        ]);

        $validator = validator($request->all(), [
            'code' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('packages')->ignore($package->id)],
            'name_en' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('packages')->ignore($package->id)],
            'tagline_en' => ['nullable', 'string', 'max:255'],
            'tagline_bn' => ['nullable', 'string', 'max:255'],
            'icon' => ['required', Rule::in(Package::ICONS)],
            'price' => ['required', 'numeric', 'min:1'],
            'gender' => ['required', Rule::in(['any', 'male', 'female'])],
            'min_age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'max_age' => ['nullable', 'integer', 'min:0', 'max:120', 'gte:min_age'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:65000'],
            'tests' => ['required', 'array', 'min:2'],
            'tests.*' => ['integer', 'distinct', 'exists:lab_tests,id'],
        ], [
            'tests.min' => 'A package needs at least two tests.',
            'tests.required' => 'Choose the tests included in this package.',
        ]);

        $validator->after(function (Validator $v) use ($request) {
            $regular = (float) LabTest::whereIn('id', (array) $request->input('tests', []))->sum('price');
            if ($regular > 0 && (float) $request->input('price') > $regular) {
                $v->errors()->add('price', 'The package price (৳'.number_format((float) $request->input('price')).') is higher than buying the tests separately (৳'.number_format($regular).').');
            }
        });

        $data = $validator->validate();
        $tests = $data['tests'];
        unset($data['tests']);
        $data['sort'] = (int) ($data['sort'] ?? 0);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['show_on_website'] = $request->boolean('show_on_website');
        $data['status'] = $request->boolean('status');

        return DB::transaction(function () use ($package, $data, $tests) {
            if ($data['is_featured']) {
                Package::where('id', '!=', $package->id ?? 0)->update(['is_featured' => false]);
            }
            $package->fill($data)->save();
            $package->labTests()->sync($tests);

            return $package;
        });
    }
}
