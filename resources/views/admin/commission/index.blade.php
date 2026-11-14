@extends('admin.layouts.master')

@section('title', 'Referral commission')

@push('css')
    <style>.card:hover { transform: none; } .num { font-variant-numeric: tabular-nums; }</style>
@endpush

@section('master_content')
    <x-admin.page-header title="Referral commission" icon="fas fa-hand-holding-usd" :subtitle="'Rule: '.$bearer.'. Earned when a bill is paid; cancelled bills are reversed.'">
        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.commission.rates') }}"><i class="fas fa-percent me-1"></i> Rates</a>
    </x-admin.page-header>

    @unless ($enabled)
        <div class="alert alert-warning">Commission is switched off in Business rules. Nothing new is being calculated.</div>
    @endunless

    <div class="card mb-3"><div class="card-body">@include('admin.commission._period')</div></div>

    <div class="row g-2 mb-3">
        <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted">Business from referrers</small><div class="fs-4 fw-bold num">৳ {{ number_format($referrers->sum('business')) }}</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted">Commission earned</small><div class="fs-4 fw-bold num">৳ {{ number_format($referrers->sum('earned')) }}</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted">Paid in period</small><div class="fs-4 fw-bold num text-success">৳ {{ number_format($referrers->sum('paid')) }}</div></div></div></div>
        <div class="col-md-3"><div class="card border-danger"><div class="card-body"><small class="text-muted">Owed now (all time)</small><div class="fs-4 fw-bold num text-danger">৳ {{ number_format($referrers->sum('unpaid')) }}</div></div></div></div>
    </div>

    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="small text-muted"><tr><th>Referrer</th><th class="text-end">Bills</th><th class="text-end">Business</th><th class="text-end">Earned</th><th class="text-end">Paid</th><th class="text-end">Owed now</th><th></th></tr></thead>
                <tbody>
                    @forelse ($referrers as $r)
                        <tr>
                            <td><b>{{ $r->name }}</b><div class="small text-muted">{{ \App\Models\Referrer::TYPES[$r->type] ?? '' }} {{ $r->commission_enabled ? '' : '· no commission' }}</div></td>
                            <td class="text-end num">{{ $r->bills }}</td>
                            <td class="text-end num">৳ {{ number_format((float) $r->business) }}</td>
                            <td class="text-end num">৳ {{ number_format((float) $r->earned) }}</td>
                            <td class="text-end num text-success">৳ {{ number_format((float) $r->paid) }}</td>
                            <td class="text-end num fw-bold {{ (float) $r->unpaid > 0 ? 'text-danger' : '' }}">৳ {{ number_format((float) $r->unpaid) }}</td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.commission.statement', [$r, 'from' => $from->toDateString(), 'to' => $to->toDateString()]) }}">Statement</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No referred bills in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
