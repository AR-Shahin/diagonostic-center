<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\Patients\PatientAccounts;
use App\Support\Phone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    use CrudHelpers;

    public function index(Request $request)
    {
        $this->allow('patient-view');

        if ($request->ajax()) {
            $query = Patient::query()
                ->withCount(['invoices' => fn ($q) => $q->where('status', '!=', 'cancelled')])
                ->withSum(['invoices as due_total' => fn ($q) => $q->where('status', '!=', 'cancelled')], 'due_amount')
                ->latest('id');

            return $this->table($query)
                ->editColumn('name', fn (Patient $p) => '<a href="'.route('admin.patients.show', $p).'" class="fw-semibold text-decoration-none">'.e($p->name).'</a>')
                ->addColumn('gender_age', fn (Patient $p) => e(Patient::GENDERS[$p->gender] ?? '').' · '.e($p->ageLabel() ?? '—'))
                ->addColumn('due', fn (Patient $p) => (float) $p->due_total > 0 ? '<span class="text-danger fw-semibold">৳ '.number_format((float) $p->due_total).'</span>' : '—')
                ->addColumn('actions', function (Patient $p) {
                    $html = '<a class="btn btn-sm btn-outline-secondary me-1" href="'.route('admin.patients.show', $p).'" title="History"><i class="fas fa-folder-open"></i></a>';
                    if ($this->can('invoice-create')) {
                        $html .= '<a class="btn btn-sm btn-outline-success me-1" href="'.route('admin.invoices.create', ['patient' => $p->id]).'" title="New bill"><i class="fas fa-file-invoice"></i></a>';
                    }

                    return $html.$this->rowActions('patient', route('admin.patients.destroy', $p), route('admin.patients.update', $p), $this->record($p), $p->name);
                })
                ->filterColumn('name', fn ($q, $kw) => $q->where(fn ($w) => $w->where('name', 'like', "%$kw%")->orWhere('mobile', 'like', "%$kw%")->orWhere('patient_code', 'like', "%$kw%")))
                ->rawColumns(['name', 'due', 'actions'])
                ->make(true);
        }

        return view('admin.patient.index');
    }

    public function show(Patient $patient)
    {
        $this->allow('patient-view');
        $invoices = $patient->invoices()->visibleTo(auth('admin')->user())->with('branch:id,code')->latest('id')->get();

        return view('admin.patient.show', compact('patient', 'invoices'));
    }

    public function store(Request $request, PatientAccounts $accounts): RedirectResponse
    {
        $this->allow('patient-create');
        $data = $this->validated($request);
        $patient = $accounts->findOrCreate($data, auth('admin')->user()->branch_id, auth('admin')->user(), $request->boolean('as_family_member'));
        $this->successAlert($patient->wasRecentlyCreated ? 'Patient registered.' : "{$patient->name} is already registered on this number.");

        return redirect()->route('admin.patients.show', $patient);
    }

    public function update(Request $request, Patient $patient, PatientAccounts $accounts): RedirectResponse
    {
        $this->allow('patient-edit');
        $data = $this->validated($request);
        $patient->update(collect($data)->except(['age_years', 'age_months', 'age_days'])->all());
        $accounts->relink($patient);
        $this->updatedAlert();

        return back();
    }

    /** Set the website password back to the phone number (patient forgot it). */
    public function resetPassword(Patient $patient, PatientAccounts $accounts): RedirectResponse
    {
        $this->allow('patient-edit');
        $accounts->ensureAccount($patient);
        $accounts->resetPassword($patient->user()->firstOrFail());
        $this->successAlert("Website password reset. {$patient->mobile} can log in with the phone number as password.");

        return back();
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        $this->allow('patient-delete');
        if ($patient->invoices()->exists()) {
            $this->errorAlert('This patient has bills and cannot be deleted.');

            return back();
        }

        return $this->safeDelete($patient, 'This patient is in use.');
    }

    /** Typeahead for the billing counter: mobile, name or patient ID. */
    public function search(Request $request): JsonResponse
    {
        $this->allow('invoice-create');
        $q = trim((string) $request->input('q'));
        if (preg_match('/^[\d\s+\-]{4,}$/', $q)) {
            $q = ltrim(Phone::normalize($q), '+');
        }
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $rows = Patient::query()
            ->where(fn ($w) => $w->where('mobile', 'like', "$q%")->orWhere('patient_code', 'like', "%$q%")->orWhere('name', 'like', "%$q%"))
            ->withSum(['invoices as due_total' => fn ($x) => $x->where('status', '!=', 'cancelled')], 'due_amount')
            ->latest('id')->limit(12)->get();

        return response()->json($rows->map(fn (Patient $p) => self::summary($p)));
    }

    public static function summary(Patient $p): array
    {
        return [
            'id' => $p->id, 'code' => $p->patient_code, 'name' => $p->name, 'mobile' => $p->mobile,
            'gender' => $p->gender, 'age' => $p->ageLabel(), 'address' => $p->address,
            'due' => (float) ($p->due_total ?? $p->totalDue()),
        ];
    }

    private function record(Patient $p): array
    {
        $diff = $p->date_of_birth?->diff(now());

        return $p->only(['name', 'mobile', 'gender', 'blood_group', 'email', 'address', 'notes']) + [
            'age_years' => $diff?->y, 'age_months' => $diff?->m, 'date_of_birth' => $p->dob_estimated ? null : $p->date_of_birth?->toDateString(),
        ];
    }

    private function validated(Request $request): array
    {
        $request->merge(['mobile' => Phone::normalize($request->input('mobile'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'regex:'.Phone::PATTERN],
            'gender' => ['required', Rule::in(array_keys(Patient::GENDERS))],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'age_years' => ['nullable', 'integer', 'min:0', 'max:130'],
            'age_months' => ['nullable', 'integer', 'min:0', 'max:11'],
            'blood_group' => ['nullable', Rule::in(Patient::BLOOD_GROUPS)],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], ['mobile.regex' => 'Enter a valid Bangladeshi mobile number (01XXXXXXXXX).']);

        if (blank($data['date_of_birth'] ?? null)) {
            $data['date_of_birth'] = Patient::dobFromAge((int) ($data['age_years'] ?? 0), (int) ($data['age_months'] ?? 0))->toDateString();
            $data['dob_estimated'] = true;
        } else {
            $data['dob_estimated'] = false;
        }

        return $data;
    }
}
