@extends('admin.layouts.master')

@section('title', 'Cash closing')

@push('css')
    <style>
        .card:hover { transform: none; }
        .num { font-variant-numeric: tabular-nums; white-space: nowrap; }
        .calc td { padding: .35rem .5rem; }
        .calc .total td { border-top: 2px solid var(--bs-border-color); font-weight: 700; }
    </style>
@endpush

@section('master_content')
    <x-admin.page-header title="Cash closing" icon="fas fa-cash-register" subtitle="At the end of your shift: count the cash in your drawer and close the day. The system tells you what it should be.">
        <form method="GET" class="d-flex gap-2 align-items-center">
            <label class="small text-muted" for="cDate">Day</label>
            <input type="date" class="form-control form-control-sm" id="cDate" name="date" value="{{ $date->toDateString() }}" max="{{ now()->toDateString() }}" onchange="this.form.submit()">
        </form>
    </x-admin.page-header>

    @php
        $s = $summary;
        $methods = \App\Models\Payment::METHODS;
    @endphp

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><b>My cash for {{ $date->format('d M Y') }}</b> <span class="text-muted small">· {{ $s['transactions'] }} payments</span></div>
                <div class="card-body">
                    <table class="table table-borderless calc mb-0">
                        <tr><td>Opening cash</td><td class="text-end num" data-opening-out>৳ {{ number_format($mine ? (float) $mine->opening_cash : $suggestedOpening, 2) }}</td></tr>
                        <tr><td>+ Cash received from patients</td><td class="text-end num text-success">৳ {{ number_format($s['cash_received'], 2) }}</td></tr>
                        <tr><td>− Cash refunded</td><td class="text-end num text-danger">৳ {{ number_format($s['cash_refunded'], 2) }}</td></tr>
                        <tr><td>− Cash expenses I entered</td><td class="text-end num text-danger">৳ {{ number_format($s['cash_expenses'], 2) }}</td></tr>
                        <tr><td>− Commission I paid in cash</td><td class="text-end num text-danger">৳ {{ number_format($s['commission_paid_cash'], 2) }}</td></tr>
                        <tr class="total"><td>Should be in the drawer</td><td class="text-end num" data-expected-out>৳ {{ number_format($mine ? (float) $mine->expected_cash : $suggestedOpening + $s['cash_received'] - $s['cash_refunded'] - $s['cash_expenses'] - $s['commission_paid_cash'], 2) }}</td></tr>
                    </table>
                    @if ($s['non_cash'])
                        <hr>
                        <div class="small text-muted mb-1">Not in the drawer (goes to the account):</div>
                        @foreach ($s['non_cash'] as $m => $amt)
                            <div class="d-flex justify-content-between small"><span>{{ $methods[$m] ?? $m }}</span><span class="num">৳ {{ number_format($amt, 2) }}</span></div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            @if ($mine)
                @php
                    $d = (float) $mine->difference;
                @endphp
                <div @class(['card h-100', 'border-success' => $d == 0, 'border-danger' => $d != 0])>
                    <div class="card-header"><b>Closed</b> <span class="text-muted small">at {{ $mine->created_at->format('h:i A') }}</span>
                        <span @class(['badge float-end', 'text-bg-success' => $mine->status === 'verified', 'text-bg-warning' => $mine->status !== 'verified'])>{{ $mine->status === 'verified' ? 'Checked by '.($mine->verifier->name ?? '') : 'Waiting for check' }}</span>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between"><span>Counted cash</span><b class="num">৳ {{ number_format((float) $mine->counted_cash, 2) }}</b></div>
                        <div class="d-flex justify-content-between"><span>Should be</span><span class="num">৳ {{ number_format((float) $mine->expected_cash, 2) }}</span></div>
                        <div @class(['d-flex justify-content-between fs-5 mt-2', 'text-success' => $d == 0, 'text-danger' => $d < 0, 'text-warning' => $d > 0])>
                            <span>{{ $d == 0 ? 'Matches' : ($d < 0 ? 'Short' : 'Extra') }}</span><b class="num">৳ {{ number_format(abs($d), 2) }}</b>
                        </div>
                        @if ($mine->note)<p class="small text-muted mt-2 mb-0">Note: {{ $mine->note }}</p>@endif
                    </div>
                </div>
            @else
                <form class="card h-100" method="POST" action="{{ route('admin.cash-closings.store') }}">
                    @csrf
                    <input type="hidden" name="closing_date" value="{{ $date->toDateString() }}">
                    <div class="card-header"><b>Close this day</b></div>
                    <div class="card-body">
                        @if ($errors->any())<div class="alert alert-danger small py-2">{{ $errors->first() }}</div>@endif
                        <x-admin.input name="opening_cash" label="Opening cash (from your last closing)" type="number" step="0.01" min="0" :value="$suggestedOpening" required />
                        <x-admin.input name="counted_cash" label="Cash I counted in the drawer" type="number" step="0.01" min="0" required help="Count notes and coins. Do not copy the “should be” figure." />
                        <div class="alert alert-light border py-2 small" data-diff-box hidden></div>
                        <x-admin.textarea name="note" label="Note (reason for any difference)" rows="2" />
                        <button class="btn btn-success w-100" onclick="return confirm('Close the day? You cannot change it afterwards.')"><i class="fas fa-lock me-1"></i> Close day</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header"><b>{{ in_array('cashclosing-view', $permissions) ? 'All closings' : 'My closings' }}</b> <span class="text-muted small">· last 60</span></div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="small text-muted"><tr><th>Day</th><th>Staff</th><th>Branch</th><th class="text-end">Received</th><th class="text-end">Expenses</th><th class="text-end">Should be</th><th class="text-end">Counted</th><th class="text-end">Difference</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($list as $c)
                        @php
                            $cd = (float) $c->difference;
                        @endphp
                        <tr>
                            <td class="num">{{ $c->closing_date->format('d M Y') }}</td>
                            <td>{{ $c->admin->name ?? '—' }}</td>
                            <td>{{ $c->branch->code ?? '' }}</td>
                            <td class="text-end num">{{ number_format((float) $c->cash_received, 2) }}</td>
                            <td class="text-end num">{{ number_format((float) $c->cash_expenses + (float) $c->commission_paid_cash, 2) }}</td>
                            <td class="text-end num">{{ number_format((float) $c->expected_cash, 2) }}</td>
                            <td class="text-end num">{{ number_format((float) $c->counted_cash, 2) }}</td>
                            <td @class(['text-end num fw-semibold', 'text-danger' => $cd < 0, 'text-warning' => $cd > 0])>{{ $cd == 0 ? '—' : number_format($cd, 2) }}</td>
                            <td>
                                @if ($c->status === 'verified')
                                    <span class="badge text-bg-success" title="{{ $c->verified_at?->format('d M Y h:i A') }}">Checked · {{ $c->verifier->name ?? '' }}</span>
                                @else
                                    <span class="badge text-bg-warning">Waiting</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if ($c->status !== 'verified' && in_array('cashclosing-verify', $permissions) && $c->admin_id !== auth('admin')->id())
                                    <form method="POST" action="{{ route('admin.cash-closings.verify', $c) }}">@csrf<button class="btn btn-sm btn-outline-success">Mark checked</button></form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-4">No closings yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (() => {
            const opening = document.querySelector('[name=opening_cash]');
            const counted = document.querySelector('[name=counted_cash]');
            const box = document.querySelector('[data-diff-box]');
            if (!opening || !counted || !box) return;
            const moves = {{ $s['cash_received'] - $s['cash_refunded'] - $s['cash_expenses'] - $s['commission_paid_cash'] }};
            const fmt = (n) => '৳ ' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const update = () => {
                const expected = (parseFloat(opening.value) || 0) + moves;
                document.querySelector('[data-opening-out]').textContent = fmt(parseFloat(opening.value) || 0);
                document.querySelector('[data-expected-out]').textContent = fmt(expected);
                if (counted.value === '') { box.hidden = true; return; }
                const diff = (parseFloat(counted.value) || 0) - expected;
                box.hidden = false;
                box.className = 'alert py-2 small ' + (Math.abs(diff) < 0.005 ? 'alert-success' : 'alert-warning');
                box.textContent = Math.abs(diff) < 0.005 ? 'Matches. Good.' : (diff < 0 ? 'Short by ' : 'Extra ') + fmt(Math.abs(diff)) + '. Count again, or write the reason below.';
            };
            opening.addEventListener('input', update);
            counted.addEventListener('input', update);
            update();
        })();
    </script>
@endpush
