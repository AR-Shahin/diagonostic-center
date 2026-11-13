<?php

namespace App\Services\Patients;

use App\Models\Admin;
use App\Models\Patient;
use App\Models\User;
use App\Services\Billing\NumberGenerator;
use App\Support\Phone;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Phone number = patient account (website login). Default password = the phone number.
 *
 * - New phone           → create the account and the patient.
 * - Known phone, same name → reuse that patient (a returning patient).
 * - Known phone, new name  → only when staff ticks "family member"; otherwise ask them to pick the patient,
 *   so a report never carries someone else's name, age or sex.
 */
class PatientAccounts
{
    public function findOrCreate(array $data, ?int $branchId, ?Admin $by, bool $asFamilyMember = false): Patient
    {
        $phone = Phone::normalize($data['mobile'] ?? null);
        if (! preg_match(Phone::PATTERN, $phone)) {
            throw ValidationException::withMessages(['patient.mobile' => 'Enter a valid Bangladeshi mobile number (01XXXXXXXXX).']);
        }

        return DB::transaction(function () use ($data, $phone, $branchId, $by, $asFamilyMember) {
            $account = User::where('phone', $phone)->lockForUpdate()->first();

            if ($account) {
                $patients = $account->patients()->get();
                $sameName = $patients->first(fn (Patient $p) => self::sameName($p->name, $data['name'] ?? ''));
                if ($sameName) {
                    return $sameName;
                }
                if ($patients->isNotEmpty() && ! $asFamilyMember) {
                    throw ValidationException::withMessages([
                        'patient.mobile' => "{$phone} is already registered to ".$patients->pluck('name')->implode(', ')
                            .'. Pick the patient from the search, or tick “Add as a family member on this number”.',
                    ]);
                }
            } else {
                $account = $this->createAccount($phone, $data['name']);
            }

            return $this->createPatient($account, $data, $phone, $branchId, $by);
        });
    }

    /** After a patient's mobile number is edited, move them to the account of the new number. */
    public function relink(Patient $patient): void
    {
        $phone = Phone::normalize($patient->mobile);
        if ($patient->user?->phone === $phone) {
            return;
        }
        $account = User::where('phone', $phone)->first() ?? $this->createAccount($phone, $patient->name);
        $patient->forceFill(['user_id' => $account->id, 'mobile' => $phone])->save();
    }

    /** Make sure an older patient row has an account (used before billing). */
    public function ensureAccount(Patient $patient): Patient
    {
        if (! $patient->user_id) {
            $this->relink($patient);
        }

        return $patient;
    }

    public function resetPassword(User $account): void
    {
        $account->forceFill(['password' => $account->phone, 'must_change_password' => true])->save();
    }

    private function createAccount(string $phone, string $name): User
    {
        return User::create([
            'name' => $name,
            'phone' => $phone,
            'password' => $phone, // hashed by the model cast; patient is asked to change it after first login
            'must_change_password' => true,
        ]);
    }

    private function createPatient(User $account, array $data, string $phone, ?int $branchId, ?Admin $by): Patient
    {
        $dob = filled($data['date_of_birth'] ?? null)
            ? $data['date_of_birth']
            : Patient::dobFromAge((int) ($data['age_years'] ?? 0), (int) ($data['age_months'] ?? 0), (int) ($data['age_days'] ?? 0));

        return Patient::create([
            'patient_code' => NumberGenerator::patientCode(),
            'user_id' => $account->id,
            'name' => trim($data['name']),
            'mobile' => $phone,
            'gender' => $data['gender'],
            'date_of_birth' => $dob,
            'dob_estimated' => blank($data['date_of_birth'] ?? null),
            'blood_group' => $data['blood_group'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'notes' => $data['notes'] ?? null,
            'branch_id' => $branchId,
            'created_by' => $by?->id,
        ]);
    }

    private static function sameName(string $a, string $b): bool
    {
        $clean = fn ($s) => mb_strtolower(preg_replace('/\s+/u', ' ', trim($s)));

        return $clean($a) !== '' && $clean($a) === $clean($b);
    }
}
