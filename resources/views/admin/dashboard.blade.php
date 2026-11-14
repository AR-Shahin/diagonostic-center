@extends('admin.layouts.master')

@section('title', 'Dashboard')

@push('css')
    <style>
        .card:hover { transform: none; }
        .num { font-variant-numeric: tabular-nums; white-space: nowrap; }
        .kpi small { color: var(--bs-secondary-color); }
        .kpi .v { font-size: 1.6rem; font-weight: 700; font-variant-numeric: tabular-nums; line-height: 1.2; }
        .pipe a { text-decoration: none; color: inherit; display: block; border-radius: .5rem; padding: .6rem .75rem; background: var(--bs-tertiary-bg); }
        .pipe a:hover { background: var(--bs-secondary-bg); }
        .pipe .v { font-size: 1.4rem; font-weight: 700; }
        .chart-box { position: relative; height: 240px; }
    </style>
@endpush

@section('master_content')
    <x-admin.page-header title="Dashboard" icon="fas fa-tachometer-alt" :subtitle="now()->format('l, d F Y')">
        @if ($canReports)
            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.reports.index') }}"><i class="fas fa-chart-bar me-1"></i> Reports</a>
        @endif
        @if (in_array('invoice-create', $permissions))
            <a class="btn btn-sm btn-success" href="{{ route('admin.invoices.create') }}"><i class="fas fa-plus me-1"></i> New bill</a>
        @endif
    </x-admin.page-header>

    @foreach ($alerts as [$tone, $icon, $text, $url])
        <a href="{{ $url }}" class="alert alert-{{ $tone }} d-flex align-items-center gap-2 py-2 text-decoration-none"><i class="{{ $icon }}"></i> {{ $text }} <i class="fas fa-arrow-right ms-auto"></i></a>
    @endforeach

    @if ($money)
        @php
            $t = $money['today'];
            $change = $money['lastMonth'] > 0 ? round(($money['month'] - $money['lastMonth']) / $money['lastMonth'] * 100) : null;
        @endphp
        <div class="row g-2 mb-3">
            <div class="col-6 col-xl-2"><div class="card h-100 kpi"><div class="card-body py-2"><small>Bills today</small><div class="v">{{ $t['bills'] }}</div><div class="small text-muted">{{ $t['patients'] }} patients</div></div></div></div>
            <div class="col-6 col-xl-2"><div class="card h-100 kpi"><div class="card-body py-2"><small>Billed today</small><div class="v">৳ {{ number_format($t['net']) }}</div><div class="small text-muted">discount ৳{{ number_format($t['discount']) }}</div></div></div></div>
            <div class="col-6 col-xl-2"><div class="card h-100 kpi border-success"><div class="card-body py-2"><small>Collected today</small><div class="v text-success">৳ {{ number_format($t['collected']) }}</div><div class="small text-muted">incl. old dues</div></div></div></div>
            <div class="col-6 col-xl-2"><div class="card h-100 kpi"><div class="card-body py-2"><small>Due from today</small><div @class(['v', 'text-danger' => $t['due'] > 0])>৳ {{ number_format($t['due']) }}</div></div></div></div>
            <div class="col-6 col-xl-2"><div class="card h-100 kpi"><div class="card-body py-2"><small>This month</small><div class="v">৳ {{ number_format($money['month']) }}</div>
                @if ($change !== null)<div @class(['small', 'text-success' => $change >= 0, 'text-danger' => $change < 0])><i @class(['fas', 'fa-arrow-up' => $change >= 0, 'fa-arrow-down' => $change < 0])></i> {{ abs($change) }}% vs same days last month</div>@endif
            </div></div></div>
            <div class="col-6 col-xl-2"><a class="card h-100 kpi text-decoration-none" href="{{ route('admin.invoices.index', ['status' => 'due']) }}"><div class="card-body py-2"><small>All open dues</small><div class="v text-danger">৳ {{ number_format($money['openDue']) }}</div><div class="small text-muted">{{ $money['openDueBills'] }} bills</div></div></a></div>
        </div>
    @endif

    <div class="row g-3">
        @if ($money)
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header"><b>Last 14 days</b></div>
                    <div class="card-body"><div class="chart-box"><canvas id="trendChart" role="img" aria-label="Billed and collected, last 14 days"></canvas></div></div>
                </div>
            </div>
        @endif

        @if ($lab)
            @php
                $c = $lab['counts'];
            @endphp
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header"><b>Lab right now</b></div>
                    <div class="card-body">
                        <div class="row g-2 pipe">
                            <div class="col-6"><a href="{{ route('admin.lab.index', ['stage' => 'collection']) }}"><small class="text-muted">Awaiting sample</small><div class="v">{{ $c['pending'] ?? 0 }}</div></a></div>
                            <div class="col-6"><a href="{{ route('admin.lab.index', ['stage' => 'results']) }}"><small class="text-muted">Sample in, no result</small><div class="v">{{ $c['collected'] ?? 0 }}</div></a></div>
                            <div class="col-6"><a href="{{ route('admin.lab.index', ['stage' => 'verify']) }}"><small class="text-muted">To verify</small><div class="v">{{ $c['processing'] ?? 0 }}</div></a></div>
                            <div class="col-6"><a href="{{ route('admin.lab.index', ['stage' => 'ready']) }}"><small class="text-muted">Ready to hand over</small><div class="v text-success">{{ $c['ready'] ?? 0 }}</div></a></div>
                        </div>
                        <div @class(['mt-3 p-2 rounded', 'bg-danger-subtle text-danger-emphasis' => $lab['lateCount'], 'bg-success-subtle text-success-emphasis' => ! $lab['lateCount']])>
                            <b>{{ $lab['lateCount'] }}</b> {{ $lab['lateCount'] === 1 ? 'report is' : 'reports are' }} past the promised time
                        </div>
                        @if ($lab['late']->isNotEmpty())
                            <ul class="list-unstyled small mt-2 mb-0">
                                @foreach ($lab['late'] as $item)
                                    <li class="d-flex justify-content-between border-bottom py-1">
                                        <a href="{{ route('admin.lab.invoice', $item->invoice_id) }}">{{ $item->name }} <span class="text-muted">· {{ $item->invoice?->patient_name }}</span></a>
                                        <span class="text-danger num" title="Promised {{ $item->delivery_at->format('d M h:i A') }}">{{ $item->delivery_at->diffForHumans(null, true) }} late</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if ($money)
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between"><b>Income by department · this month</b>
                        @if ($canReports)<a class="small" href="{{ route('admin.reports.index', 'departments') }}">Full report</a>@endif
                    </div>
                    <div class="card-body">
                        @forelse ($money['topDepts'] as $d)
                            <div class="d-flex justify-content-between small"><span>{{ $d['name'] }} <span class="text-muted">· {{ $d['tests'] }} tests</span></span><span class="num">৳ {{ number_format($d['net']) }}</span></div>
                            <div class="progress mb-2" style="height:6px" role="progressbar" aria-label="{{ $d['name'] }}" aria-valuenow="{{ $d['share'] }}" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar" style="width: {{ $d['share'] }}%"></div></div>
                        @empty
                            <p class="text-muted mb-0">No bills this month yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            @if ($money['branches'])
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header"><b>Branches · today</b></div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <thead class="small text-muted"><tr><th>Branch</th><th class="text-end">Bills</th><th class="text-end">Billed</th><th class="text-end">Collected</th></tr></thead>
                                <tbody>
                                    @foreach ($money['branches'] as $b)
                                        <tr><td>{{ $b['name'] }}</td><td class="text-end num">{{ $b['bills'] }}</td><td class="text-end num">৳ {{ number_format($b['net']) }}</td><td class="text-end num">৳ {{ number_format($b['collected']) }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        @unless ($money || $lab)
            <div class="col-12"><div class="card"><div class="card-body text-muted">Welcome, {{ auth('admin')->user()->name }}. Use the menu on the left to start.</div></div></div>
        @endunless
    </div>

    @if (auth('admin')->user()->hasRole('Super Admin'))
        <details class="card mt-3">
            <summary class="card-body py-2 small text-muted" style="cursor:pointer"><i class="fas fa-database me-1"></i> Backups</summary>
            <div class="card-body pt-0">
                <form action="{{ route('admin.backup') }}" method="POST" class="d-inline me-2">@csrf<button class="btn btn-sm btn-success"><i class="fas fa-file-archive me-1"></i> Backup site data</button></form>
                <form action="{{ route('admin.backup_db') }}" method="POST" class="d-inline me-2">@csrf<button class="btn btn-sm btn-info"><i class="fas fa-database me-1"></i> Database backup only</button></form>
                <form action="{{ route('backup.download') }}" method="POST" class="d-inline">@csrf<button onclick="return confirm('Download the database backup?')" class="btn btn-sm btn-primary"><i class="fas fa-download me-1"></i> Download database backup</button></form>
            </div>
        </details>
    @endif
@endsection

@push('script')
    @if ($money && $money['trend'])
        <script src="{{ asset('admin/plugins/chart.js/Chart.min.js') }}"></script>
        <script>
            (() => {
                const t = {{ \Illuminate\Support\Js::from($money['trend']) }};
                const money = (v) => '৳ ' + Number(v).toLocaleString('en-US');
                new Chart(document.getElementById('trendChart'), {
                    type: 'bar',
                    data: { labels: t.labels, datasets: [
                        { label: 'Billed', data: t.series['Net billed'], backgroundColor: '#0d6efd' },
                        { label: 'Collected', data: t.series['Collected'], backgroundColor: '#20c997' },
                    ] },
                    options: {
                        maintainAspectRatio: false,
                        tooltips: { callbacks: { label: (i, d) => d.datasets[i.datasetIndex].label + ': ' + money(d.datasets[i.datasetIndex].data[i.index]) } },
                        scales: { yAxes: [{ ticks: { beginAtZero: true, callback: money } }], xAxes: [{ gridLines: { display: false } }] },
                    },
                });
            })();
        </script>
    @endif
@endpush
