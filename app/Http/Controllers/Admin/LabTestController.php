<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LabTestRequest;
use App\Models\Branch;
use App\Models\Department;
use App\Models\LabTest;
use App\Models\SampleType;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabTestController extends Controller
{
    use CrudHelpers;

    public function index(Request $request)
    {
        $this->allow('labtest-view');

        if ($request->ajax()) {
            $query = LabTest::query()->with('department:id,name_en')
                ->when($request->integer('department_id'), fn ($q, $id) => $q->where('department_id', $id))
                ->when($request->filled('state'), fn ($q) => $q->where('status', $request->input('state') === 'active'))
                ->orderBy('sort')->orderBy('name_en');

            return $this->table($query)
                ->editColumn('name_en', fn (LabTest $t) => e($t->name_en).($t->is_popular ? ' <i class="fas fa-star text-warning small" title="Popular"></i>' : '')
                    .'<div class="small text-muted">'.e($t->name_bn).'</div>')
                ->addColumn('department', fn (LabTest $t) => e($t->department?->name_en))
                ->editColumn('price', function (LabTest $t) {
                    $price = '৳ '.number_format((float) $t->price);

                    return $t->offer_price !== null
                        ? '<s class="text-muted small">'.$price.'</s> <b>৳ '.number_format((float) $t->offer_price).'</b>'
                        : '<b>'.$price.'</b>';
                })
                ->addColumn('type', fn (LabTest $t) => '<span class="badge text-bg-light border">'.e(ucfirst($t->report_type)).'</span>')
                ->addColumn('website', fn (LabTest $t) => $this->statusBadge($t->show_on_website, 'Shown', 'Hidden'))
                ->addColumn('state', fn (LabTest $t) => $this->statusBadge($t->status))
                ->addColumn('actions', fn (LabTest $t) => $this->rowActions('labtest', route('admin.lab-tests.destroy', $t), route('admin.lab-tests.edit', $t), null, $t->name_en))
                ->filterColumn('name_en', fn ($q, $kw) => $q->where(fn ($w) => $w->where('name_en', 'like', "%$kw%")->orWhere('name_bn', 'like', "%$kw%")->orWhere('code', 'like', "%$kw%")))
                ->rawColumns(['name_en', 'price', 'type', 'website', 'state', 'actions'])
                ->make(true);
        }

        return view('admin.lab-test.index', ['departments' => Department::orderBy('sort')->pluck('name_en', 'id')]);
    }

    public function create()
    {
        $this->allow('labtest-create');

        return view('admin.lab-test.form', $this->formData(new LabTest([
            'report_type' => 'parameter', 'status' => true, 'show_on_website' => true, 'delivery_hours' => 24,
        ])));
    }

    public function store(LabTestRequest $request): RedirectResponse
    {
        $this->allow('labtest-create');
        $test = $this->save(new LabTest, $request);
        $this->createdAlert();

        return redirect()->route('admin.lab-tests.edit', $test);
    }

    public function edit(LabTest $labTest)
    {
        $this->allow('labtest-edit');
        $labTest->load(['parameters.referenceRanges', 'branches:id', 'reportTemplates']);

        return view('admin.lab-test.form', $this->formData($labTest));
    }

    public function update(LabTestRequest $request, LabTest $labTest): RedirectResponse
    {
        $this->allow('labtest-edit');
        $this->save($labTest, $request);
        $this->updatedAlert();

        return back();
    }

    public function destroy(LabTest $labTest): RedirectResponse
    {
        $this->allow('labtest-delete');

        if ($labTest->packages()->exists()) {
            $this->errorAlert('This test is part of a package. Remove it from the package first, or mark the test inactive.');

            return back();
        }

        return $this->safeDelete($labTest, 'This test is in use and cannot be deleted. Mark it inactive instead.');
    }

    private function formData(LabTest $test): array
    {
        return [
            'test' => $test,
            'departments' => Department::orderBy('sort')->pluck('name_en', 'id'),
            'sampleTypes' => SampleType::where('status', true)->orderBy('name_en')->pluck('name_en', 'id'),
            'units' => Unit::where('status', true)->orderBy('name')->pluck('name', 'id'),
            'branches' => Branch::where('status', true)->orderBy('sort')->get(['id', 'name_en', 'code']),
            'parameters' => $this->parametersForForm($test),
            'template' => $test->exists ? $test->reportTemplates->firstWhere('is_default', true) ?? $test->reportTemplates->first() : null,
        ];
    }

    /** Old input after a failed save, otherwise what is stored (ages converted back to a friendly unit). */
    private function parametersForForm(LabTest $test): array
    {
        if (old('parameters') !== null) {
            return old('parameters', []);
        }
        if (! $test->exists) {
            return [];
        }

        return $test->parameters->map(fn ($p) => [
            'id' => $p->id, 'group_name' => $p->group_name, 'code' => $p->code, 'name' => $p->name, 'unit_id' => $p->unit_id,
            'input_type' => $p->input_type, 'options' => $p->options, 'decimal_places' => $p->decimal_places,
            'formula' => $p->formula, 'default_value' => $p->default_value,
            'ranges' => $p->referenceRanges->map(function ($r) {
                $unit = $this->ageUnit($r->age_min_days, $r->age_max_days);
                $div = ['years' => 365, 'months' => 30, 'days' => 1][$unit];

                return [
                    'gender' => $r->gender, 'age_unit' => $unit,
                    'age_min' => $r->age_min_days === null ? null : $r->age_min_days / $div,
                    'age_max' => $r->age_max_days === null ? null : $r->age_max_days / $div,
                    'low' => $r->low, 'high' => $r->high, 'normal_text' => $r->normal_text,
                ];
            })->all(),
        ])->all();
    }

    private function ageUnit(?int $min, ?int $max): string
    {
        $values = array_filter([$min, $max], fn ($v) => $v !== null && $v !== 0);
        foreach (['years' => 365, 'months' => 30] as $unit => $div) {
            if (collect($values)->every(fn ($v) => $v % $div === 0)) {
                return $unit;
            }
        }

        return 'days';
    }

    private function save(LabTest $test, LabTestRequest $request): LabTest
    {
        $data = $request->safe()->except(['branches', 'parameters', 'template_body']);
        $data['cost'] = $data['cost'] ?? 0;
        $data['sort'] = (int) ($data['sort'] ?? 0);
        $data['is_popular'] = $request->boolean('is_popular');
        $data['show_on_website'] = $request->boolean('show_on_website');
        $data['status'] = $request->boolean('status');

        return DB::transaction(function () use ($test, $data, $request) {
            $test->fill($data)->save();
            $test->branches()->sync($request->input('branches', []));

            if ($test->report_type === 'parameter') {
                $this->syncParameters($test, $request->validated('parameters', []));
            }
            if ($test->report_type === 'template' && $request->filled('template_body')) {
                $test->reportTemplates()->updateOrCreate(
                    ['is_default' => true],
                    ['name' => 'Default', 'body' => $request->input('template_body')]
                );
            }

            return $test;
        });
    }

    private function syncParameters(LabTest $test, array $rows): void
    {
        $keepIds = [];
        $toDays = fn ($value, ?string $unit) => $value === null || $value === ''
            ? null
            : (int) round((float) $value * ['years' => 365, 'months' => 30, 'days' => 1][$unit ?: 'years']);

        foreach (array_values($rows) as $i => $row) {
            $attributes = [
                'group_name' => $row['group_name'] ?? null,
                'code' => $row['code'],
                'name' => $row['name'],
                'unit_id' => $row['unit_id'] ?? null,
                'input_type' => $row['input_type'],
                'options' => $row['input_type'] === 'option' ? ($row['options'] ?? null) : null,
                'decimal_places' => (int) ($row['decimal_places'] ?? 2),
                'formula' => filled($row['formula'] ?? null) ? strtoupper($row['formula']) : null,
                'default_value' => $row['default_value'] ?? null,
                'sort' => $i + 1,
            ];

            $parameter = filled($row['id'] ?? null)
                ? $test->parameters()->whereKey($row['id'])->first()
                : null;
            if ($parameter) {
                $parameter->update($attributes);
            } else {
                $parameter = $test->parameters()->create($attributes);
            }
            $keepIds[] = $parameter->id;

            $parameter->referenceRanges()->delete();
            foreach (array_values($row['ranges'] ?? []) as $j => $range) {
                $parameter->referenceRanges()->create([
                    'gender' => $range['gender'],
                    'age_min_days' => $toDays($range['age_min'] ?? null, $range['age_unit'] ?? null),
                    'age_max_days' => $toDays($range['age_max'] ?? null, $range['age_unit'] ?? null),
                    'low' => $range['low'] ?? null,
                    'high' => $range['high'] ?? null,
                    'normal_text' => $range['normal_text'] ?? null,
                    'sort' => $j,
                ]);
            }
        }

        // Removed rows. When results exist (phase 3) this becomes "deactivate" instead of delete.
        $test->parameters()->whereNotIn('id', $keepIds)->delete();
    }
}
