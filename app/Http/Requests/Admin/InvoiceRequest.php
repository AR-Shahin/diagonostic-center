<?php

namespace App\Http\Requests\Admin;

use App\Models\Patient;
use App\Models\Payment;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // permission is checked in the controller
    }

    protected function prepareForValidation(): void
    {
        $mobile = Phone::normalize($this->input('patient.mobile'));
        $this->merge([
            'patient' => array_merge((array) $this->input('patient', []), ['mobile' => $mobile]),
            'items' => array_values((array) $this->input('items', [])),
        ]);
    }

    public function rules(): array
    {
        $new = fn () => blank($this->input('patient_id'));

        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'patient_id' => ['nullable', 'integer', 'exists:patients,id'],
            'patient.name' => [Rule::requiredIf($new), 'nullable', 'string', 'max:255'],
            'patient.mobile' => [Rule::requiredIf($new), 'nullable', 'regex:'.Phone::PATTERN],
            'as_family_member' => ['nullable', 'boolean'],
            'patient.gender' => [Rule::requiredIf($new), 'nullable', Rule::in(array_keys(Patient::GENDERS))],
            'patient.age_years' => ['nullable', 'integer', 'min:0', 'max:130'],
            'patient.age_months' => ['nullable', 'integer', 'min:0', 'max:11'],
            'patient.age_days' => ['nullable', 'integer', 'min:0', 'max:31'],
            'patient.address' => ['nullable', 'string', 'max:1000'],
            'referrer_id' => ['nullable', 'integer', 'exists:referrers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.type' => ['required', Rule::in(['test', 'package'])],
            'items.*.id' => ['required', 'integer'],
            'discount_type' => ['nullable', Rule::in(['percent', 'flat'])],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'discount_reason' => ['nullable', 'string', 'max:255'],
            'approver_id' => ['nullable', 'integer', 'exists:admins,id'],
            'approver_password' => ['nullable', 'string'],
            'home_collection' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:1000'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'transaction_ref' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient.name.required' => 'Enter the patient name or pick an existing patient.',
            'patient.mobile.required' => 'Enter the patient mobile number.',
            'patient.mobile.regex' => 'Enter a valid Bangladeshi mobile number (01XXXXXXXXX).',
            'patient.gender.required' => 'Choose the patient gender.',
            'items.required' => 'Add at least one test or package.',
        ];
    }

    public function attributes(): array
    {
        return ['patient.age_years' => 'age (years)', 'paid_amount' => 'paid amount'];
    }
}
