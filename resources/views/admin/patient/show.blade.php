@extends('admin.layouts.master')

@section('title', $patient->name)

@section('master_content')
    <x-admin.page-header :title="$patient->name" icon="fas fa-user-injured" :subtitle="$patient->patient_code.' · '.$patient->mobile">
        <a href="{{ route('admin.patients.index') }}" class="btn btn-sm btn-light"><i class="fas fa-arrow-left me-1"></i> Patients</a>
        @if (in_array('invoice-create', $permissions))
            <a href="{{ route('admin.invoices.create', ['patient' => $patient->id]) }}" class="btn btn-sm btn-success"><i class="fas fa-cash-register me-1"></i> New bill</a>
        @endif
    </x-admin.page-header>

    @php
        $due = $invoices->where('status', '!=', 'cancelled')->sum('due_amount');
    @endphp
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5">Gender</dt><dd class="col-7">{{ \App\Models\Patient::GENDERS[$patient->gender] ?? '' }}</dd>
                        <dt class="col-5">Age</dt><dd class="col-7">{{ $patient->ageLabel() ?? '—' }} @if ($patient->date_of_birth && ! $patient->dob_estimated)<span class="text-muted">({{ $patient->date_of_birth->format('d M Y') }})</span>@endif</dd>
                        <dt class="col-5">Blood group</dt><dd class="col-7">{{ $patient->blood_group ?? '—' }}</dd>
                        <dt class="col-5">Email</dt><dd class="col-7">{{ $patient->email ?? '—' }}</dd>
                        <dt class="col-5">Address</dt><dd class="col-7">{{ $patient->address ?? '—' }}</dd>
                        <dt class="col-5">Registered</dt><dd class="col-7">{{ $patient->created_at->format('d M Y') }}</dd>
                    </dl>
                </div>
            </div>
            @php
                $account = $patient->user;
                $family = $account ? $account->patients()->where('id', '!=', $patient->id)->get(['id', 'name', 'patient_code']) : collect();
            @endphp
            <div class="card mt-3">
                <div class="card-header"><b><i class="fas fa-globe text-primary me-1"></i> Website account</b></div>
                <div class="card-body small">
                    @if ($account)
                        <div class="d-flex justify-content-between"><span class="text-muted">Login phone</span><b>{{ $account->phone }}</b></div>
                        <div class="d-flex justify-content-between"><span class="text-muted">Password</span>
                            <span>{!! $account->must_change_password ? '<span class="badge text-bg-warning">Default (phone number)</span>' : '<span class="badge text-bg-success">Changed by patient</span>' !!}</span></div>
                        <div class="d-flex justify-content-between"><span class="text-muted">Last login</span><span>{{ $account->last_login_at?->diffForHumans() ?? 'Never' }}</span></div>
                        @if ($family->isNotEmpty())
                            <div class="mt-2"><span class="text-muted">Family on this number:</span>
                                @foreach ($family as $member)<a class="badge text-bg-light border text-decoration-none ms-1" href="{{ route('admin.patients.show', $member) }}">{{ $member->name }}</a>@endforeach
                            </div>
                        @endif
                    @else
                        <span class="text-muted">No account yet. It is created with the first bill.</span>
                    @endif
                    @if (in_array('patient-edit', $permissions))
                        <form method="POST" action="{{ route('admin.patients.reset-password', $patient) }}" class="mt-3" onsubmit="return confirm('Reset the website password to the phone number?')">
                            @csrf
                            <button class="btn btn-sm btn-outline-warning w-100"><i class="fas fa-key me-1"></i> Reset password to phone number</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card mt-3 {{ $due > 0 ? 'border-danger' : '' }}">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <span class="text-muted">Total due</span>
                    <b class="fs-4 {{ $due > 0 ? 'text-danger' : 'text-success' }}">৳ {{ number_format($due) }}</b>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><b>Bills</b> <span class="badge text-bg-light border">{{ $invoices->count() }}</span></div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead><tr><th>Invoice</th><th>Date</th><th>Branch</th><th class="text-end">Net</th><th class="text-end">Due</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($invoices as $inv)
                                <tr>
                                    <td><a href="{{ route('admin.invoices.show', $inv) }}" class="fw-semibold text-decoration-none">{{ $inv->invoice_no }}</a></td>
                                    <td>{{ $inv->created_at->format('d M Y') }}</td>
                                    <td>{{ $inv->branch?->code }}</td>
                                    <td class="text-end">৳ {{ number_format((float) $inv->net_amount) }}</td>
                                    <td class="text-end {{ (float) $inv->due_amount > 0 ? 'text-danger fw-semibold' : '' }}">{{ (float) $inv->due_amount > 0 ? '৳ '.number_format((float) $inv->due_amount) : '—' }}</td>
                                    <td>{!! $inv->statusBadge() !!}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No bills yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
