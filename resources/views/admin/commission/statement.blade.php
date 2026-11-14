@extends($print ? 'admin.commission.print-layout' : 'admin.layouts.master')

@section('title', 'Commission · '.$referrer->name)

@push('css')
    <style>.card:hover { transform: none; } .num { font-variant-numeric: tabular-nums; white-space: nowrap; }</style>
@endpush

@section('master_content')
    @unless ($print)
        <x-admin.page-header :title="$referrer->displayName()" icon="fas fa-user-md" :subtitle="trim(($referrer->organisation ?? '').' '.($referrer->mobile ?? ''))">
            <a class="btn btn-sm btn-light" href="{{ route('admin.commission.index', ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}"><i class="fas fa-arrow-left me-1"></i> All referrers</a>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.commission.rates', ['referrer' => $referrer->id]) }}"><i class="fas fa-percent me-1"></i> Rates</a>
            <a class="btn btn-sm btn-outline-secondary" target="_blank" href="{{ request()->fullUrlWithQuery(['print' => 1]) }}"><i class="fas fa-print me-1"></i> Print statement</a>
            @if ($unpaid > 0 && in_array('commission-pay', $permissions))
                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#payModal"><i class="fas fa-money-bill-wave me-1"></i> Pay ৳ {{ number_format($unpaid) }}</button>
            @endif
        </x-admin.page-header>
        <div class="card mb-3"><div class="card-body">@include('admin.commission._period')</div></div>
    @else
        <h2 style="margin:0 0 4px">Commission statement — {{ $referrer->displayName() }}</h2>
        <p style="margin:0 0 10px">{{ $from->format('d M Y') }} – {{ $to->format('d M Y') }} · Owed now: <b>৳ {{ number_format($unpaid, 2) }}</b></p>
    @endunless

    @php
        $credits = $entries->where('type', 'credit');
    @endphp
    <div @class(['card mb-3' => ! $print])>
        <div @class(['card-body p-0 table-responsive' => ! $print])>
            <table class="table table-sm align-middle mb-0" @if ($print) style="width:100%;border-collapse:collapse" border="1" cellpadding="4" @endif>
                <thead class="small text-muted"><tr><th>Date</th><th>Bill</th><th>Patient</th><th>Test</th><th class="text-end">Price</th><th>Rate</th><th class="text-end">Less discount</th><th class="text-end">Commission</th><th>Paid in</th></tr></thead>
                <tbody>
                    @forelse ($entries as $e)
                        <tr @class(['table-danger' => $e->type === 'reversal'])>
                            <td class="num">{{ $e->created_at->format('d M Y') }}</td>
                            <td>{{ $e->invoice?->invoice_no }}</td>
                            <td>{{ $e->invoice?->patient_name }}</td>
                            <td>{{ $e->type === 'reversal' ? $e->note : $e->item?->name }}</td>
                            <td class="text-end num">{{ $e->type === 'credit' ? number_format((float) $e->base_amount, 2) : '' }}</td>
                            <td>{{ $e->rate_type === 'percent' ? (float) $e->rate_value.'%' : ($e->rate_type ? '৳'.(float) $e->rate_value : '') }}</td>
                            <td class="text-end num">{{ (float) $e->discount_deducted > 0 ? number_format((float) $e->discount_deducted, 2) : '' }}</td>
                            <td class="text-end num fw-semibold">{{ number_format((float) $e->amount, 2) }}</td>
                            <td class="small">{{ $e->payout?->payout_no ?? 'Unpaid' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No commission in this period.</td></tr>
                    @endforelse
                </tbody>
                @if ($entries->isNotEmpty())
                    <tfoot><tr class="fw-bold"><td colspan="7" class="text-end">Total in period ({{ $credits->count() }} tests)</td><td class="text-end num">৳ {{ number_format((float) $entries->sum('amount'), 2) }}</td><td></td></tr></tfoot>
                @endif
            </table>
        </div>
    </div>

    @unless ($print)
        <div class="card">
            <div class="card-header"><b>Payouts</b></div>
            <div class="card-body p-0">
                <table class="table table-sm align-middle mb-0">
                    <thead class="small text-muted"><tr><th>Voucher</th><th>Date</th><th>Up to</th><th>Method</th><th>By</th><th class="text-end">Amount</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($payouts as $p)
                            <tr>
                                <td>{{ $p->payout_no }}</td><td>{{ $p->created_at->format('d M Y') }}</td><td>{{ $p->period_to->format('d M Y') }}</td>
                                <td>{{ \App\Models\Payment::METHODS[$p->method] ?? $p->method }} {{ $p->reference ? '('.$p->reference.')' : '' }}</td>
                                <td>{{ $p->payer->name ?? '—' }}</td>
                                <td class="text-end num fw-semibold">৳ {{ number_format((float) $p->amount, 2) }}</td>
                                <td class="text-end"><a class="btn btn-sm btn-light" target="_blank" href="{{ route('admin.commission.voucher', $p) }}"><i class="fas fa-print"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-3">No payouts yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($unpaid > 0 && in_array('commission-pay', $permissions))
            <div class="modal fade" id="payModal" tabindex="-1" aria-labelledby="payTitle" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('admin.commission.pay', $referrer) }}">
                        @csrf
                        <div class="modal-header"><h5 class="modal-title" id="payTitle">Pay commission</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                        <div class="modal-body">
                            <p>All unpaid commission earned up to the date below is paid together. Owed now: <b>৳ {{ number_format($unpaid, 2) }}</b>.</p>
                            <x-admin.input name="period_to" label="Pay commission earned up to" type="date" :value="now()->toDateString()" required />
                            <x-admin.select name="method" label="Method" :options="\App\Models\Payment::METHODS" default="cash" />
                            <x-admin.input name="reference" label="Reference (cheque / bKash ID)" />
                            <x-admin.input name="note" label="Note" />
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button><button class="btn btn-success">Pay and print voucher</button></div>
                    </form>
                </div>
            </div>
        @endif
    @endunless
@endsection
