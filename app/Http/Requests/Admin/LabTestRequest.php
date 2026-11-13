<?php

namespace App\Http\Requests\Admin;

use App\Models\LabTest;
use App\Models\ReferenceRange;
use App\Models\TestParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class LabTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // permission is checked in the controller
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'slug' => Str::slug($this->input('slug') ?: $this->input('name_en')),
            'parameters' => array_values(array_map(function ($p) {
                $p['code'] = strtoupper(trim((string) ($p['code'] ?? '')));
                $p['ranges'] = array_values($p['ranges'] ?? []);

                return $p;
            }, $this->input('parameters', []))),
        ]);
    }

    public function rules(): array
    {
        $test = $this->route('lab_test');

        return [
            'department_id' => ['required', 'exists:departments,id'],
            'sample_type_id' => ['nullable', 'exists:sample_types,id'],
            'code' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('lab_tests')->ignore($test?->id)],
            'name_en' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('lab_tests')->ignore($test?->id)],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'offer_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'report_type' => ['required', Rule::in(array_keys(LabTest::REPORT_TYPES))],
            'delivery_hours' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'preparation_en' => ['nullable', 'string', 'max:1000'],
            'preparation_bn' => ['nullable', 'string', 'max:1000'],
            'description_en' => ['nullable', 'string', 'max:5000'],
            'description_bn' => ['nullable', 'string', 'max:5000'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:65000'],
            'branches' => ['nullable', 'array'],
            'branches.*' => ['integer', 'exists:branches,id'],
            'template_body' => ['nullable', 'string'],

            'parameters' => ['array'],
            'parameters.*.id' => ['nullable', 'integer'],
            'parameters.*.group_name' => ['nullable', 'string', 'max:100'],
            'parameters.*.code' => ['required', 'string', 'max:30', 'regex:/^[A-Z][A-Z0-9_]*$/', 'distinct'],
            'parameters.*.name' => ['required', 'string', 'max:255'],
            'parameters.*.unit_id' => ['nullable', 'exists:units,id'],
            'parameters.*.input_type' => ['required', Rule::in(array_keys(TestParameter::INPUT_TYPES))],
            'parameters.*.options' => ['nullable', 'required_if:parameters.*.input_type,option', 'string', 'max:1000'],
            'parameters.*.decimal_places' => ['nullable', 'integer', 'min:0', 'max:4'],
            'parameters.*.formula' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9_+\-*\/(). ]+$/'],
            'parameters.*.default_value' => ['nullable', 'string', 'max:255'],
            'parameters.*.ranges' => ['array'],
            'parameters.*.ranges.*.gender' => ['required', Rule::in(array_keys(ReferenceRange::GENDERS))],
            'parameters.*.ranges.*.age_min' => ['nullable', 'numeric', 'min:0'],
            'parameters.*.ranges.*.age_max' => ['nullable', 'numeric', 'min:0'],
            'parameters.*.ranges.*.age_unit' => ['nullable', Rule::in(['years', 'months', 'days'])],
            'parameters.*.ranges.*.low' => ['nullable', 'numeric'],
            'parameters.*.ranges.*.high' => ['nullable', 'numeric'],
            'parameters.*.ranges.*.normal_text' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'offer_price.lt' => 'The offer price must be lower than the regular price.',
            'parameters.*.code.regex' => 'Parameter codes use capital letters, numbers and _ only (e.g. HB, TOTAL_WBC).',
            'parameters.*.code.distinct' => 'Each parameter needs its own code.',
            'parameters.*.options.required_if' => 'List the choices for an option parameter (comma separated).',
            'parameters.*.formula.regex' => 'A formula can only use parameter codes, numbers and + - * / ( ).',
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($this->input('report_type') !== 'parameter') {
                return;
            }
            $params = $this->input('parameters', []);
            $codes = collect($params)->pluck('code')->filter()->all();

            foreach ($params as $i => $p) {
                if (blank($p['formula'] ?? null)) {
                    foreach ($p['ranges'] ?? [] as $j => $r) {
                        if (filled($r['low'] ?? null) && filled($r['high'] ?? null) && (float) $r['low'] > (float) $r['high']) {
                            $validator->errors()->add("parameters.$i.ranges.$j.low", 'Low must not be higher than high.');
                        }
                    }

                    continue;
                }
                preg_match_all('/[A-Za-z_][A-Za-z0-9_]*/', $p['formula'], $m);
                foreach ($m[0] as $token) {
                    if (! in_array(strtoupper($token), $codes, true)) {
                        $validator->errors()->add("parameters.$i.formula", "Unknown code \"$token\" in formula.");
                    }
                    if (strtoupper($token) === ($p['code'] ?? null)) {
                        $validator->errors()->add("parameters.$i.formula", 'A formula cannot use its own code.');
                    }
                }
            }
        }];
    }
}
