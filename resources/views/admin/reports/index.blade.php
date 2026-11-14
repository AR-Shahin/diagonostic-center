@extends('admin.layouts.master')

@section('title', $report['title'])

@push('css')
    <style>
        .card:hover { transform: none; }
        .num { font-variant-numeric: tabular-nums; white-space: nowrap; }
        .report-nav .list-group-item { border: 0; border-radius: .5rem !important; padding: .5rem .75rem; }
        .report-nav .list-group-item i { width: 1.25rem; text-align: center; opacity: .7; }
        .report-nav .active i { opacity: 1; }
        .stat small { color: var(--bs-secondary-color); }
        .stat .v { font-size: 1.35rem; font-weight: 700; font-variant-numeric: tabular-nums; }
        .chart-box { position: relative; height: 260px; }
        .report-table tfoot td { border-top: 2px solid var(--bs-border-color); }
    </style>
@endpush

@section('master_content')
    <x-admin.page-header :title="$report['title']" icon="{{ \App\Services\Reports\ReportService::TYPES[$type][1] }}"
        :subtitle="$from->format('d M Y').' – '.$to->format('d M Y').' · '.$branchName">
        <a class="btn btn-sm btn-outline-secondary" target="_blank" href="{{ request()->fullUrlWithQuery(['print' => 1]) }}"><i class="fas fa-print me-1"></i> Print</a>
        <a class="btn btn-sm btn-success" href="{{ route('admin.reports.export', [$type] + request()->only(['from', 'to', 'branch_id'])) }}"><i class="fas fa-file-excel me-1"></i> Excel</a>
    </x-admin.page-header>

    <div class="row g-3">
        <div class="col-lg-2">
            <nav class="list-group report-nav" aria-label="Reports">
                @foreach ($types as $key => [$label, $icon])
                    <a href="{{ route('admin.reports.index', [$key] + request()->only(['from', 'to', 'branch_id'])) }}" @class(['list-group-item list-group-item-action', 'active' => $key === $type]) @if ($key === $type) aria-current="page" @endif><i class="{{ $icon }} me-1"></i> {{ $label }}</a>
                @endforeach
            </nav>
        </div>
        <div class="col-lg-10">
            <div class="card mb-3">
                <div class="card-body">
                    @php
                        $quick = [
                            'Today' => [now(), now()],
                            'Yesterday' => [now()->subDay(), now()->subDay()],
                            'This month' => [now()->startOfMonth(), now()],
                            'Last month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
                            'This year' => [now()->startOfYear(), now()],
                        ];
                    @endphp
                    <form method="GET" class="d-flex flex-wrap gap-2 align-items-end">
                        <div><label class="form-label small mb-1" for="rFrom">From</label><input type="date" class="form-control form-control-sm" id="rFrom" name="from" value="{{ $from->toDateString() }}"></div>
                        <div><label class="form-label small mb-1" for="rTo">To</label><input type="date" class="form-control form-control-sm" id="rTo" name="to" value="{{ $to->toDateString() }}"></div>
                        @if ($branches->count() > 1)
                            <div>
                                <label class="form-label small mb-1" for="rBranch">Branch</label>
                                <select class="form-select form-select-sm" id="rBranch" name="branch_id">
                                    <option value="">All branches</option>
                                    @foreach ($branches as $b)<option value="{{ $b->id }}" @selected($branchId === $b->id)>{{ $b->name_en }}</option>@endforeach
                                </select>
                            </div>
                        @endif
                        <button class="btn btn-sm btn-primary">Show</button>
                        <div class="btn-group btn-group-sm flex-wrap" role="group" aria-label="Quick periods">
                            @foreach ($quick as $label => [$qf, $qt])
                                <a @class(['btn btn-light', 'active' => $from->isSameDay($qf) && $to->isSameDay($qt)]) href="{{ request()->fullUrlWithQuery(['from' => $qf->toDateString(), 'to' => $qt->toDateString()]) }}">{{ $label }}</a>
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>

            @if ($report['cards'])
                <div class="row g-2 mb-3">
                    @foreach ($report['cards'] as $card)
                        <div class="col-6 col-md-4 col-xl-3">
                            <div @class(['card h-100 stat', 'border-'.($card[3] ?? '') => ! empty($card[3])])>
                                <div class="card-body py-2">
                                    <small>{{ $card[0] }}</small>
                                    <div @class(['v', 'text-'.($card[3] ?? '') => ! empty($card[3])])>{{ $card[1] }}</div>
                                    @if (! empty($card[2]))<div class="small text-muted">{{ $card[2] }}</div>@endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($report['chart'] && $report['rows'])
                <div class="card mb-3"><div class="card-body"><div class="chart-box"><canvas id="reportChart" aria-label="{{ $report['title'] }} chart" role="img"></canvas></div></div></div>
            @endif

            <div class="card">
                @if ($report['note'])<div class="card-header small text-muted"><i class="fas fa-info-circle me-1"></i> {{ $report['note'] }}</div>@endif
                <div class="card-body p-0 table-responsive">
                    @include('admin.reports._table')
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    @if ($report['chart'] && $report['rows'])
        <script src="{{ asset('admin/plugins/chart.js/Chart.min.js') }}"></script>
        <script>
            (() => {
                const chart = {{ \Illuminate\Support\Js::from($report['chart']) }};
                const palette = ['#0d6efd', '#20c997', '#fd7e14', '#6f42c1', '#d63384', '#0dcaf0', '#ffc107', '#198754', '#6c757d', '#dc3545'];
                const doughnut = chart.type === 'doughnut';
                const money = (v) => '৳ ' + Number(v).toLocaleString('en-US');
                new Chart(document.getElementById('reportChart'), {
                    type: doughnut ? 'doughnut' : 'bar',
                    data: {
                        labels: chart.labels,
                        datasets: Object.entries(chart.series).map(([label, data], i) => ({
                            label, data,
                            backgroundColor: doughnut ? palette : palette[i] + (i ? 'cc' : ''),
                            borderWidth: doughnut ? 1 : 0,
                        })),
                    },
                    options: {
                        maintainAspectRatio: false,
                        legend: { position: doughnut ? 'right' : 'top' },
                        tooltips: { callbacks: { label: (item, data) => (doughnut ? data.labels[item.index] : data.datasets[item.datasetIndex].label) + ': ' + money(data.datasets[item.datasetIndex].data[item.index]) } },
                        scales: doughnut ? {} : { yAxes: [{ ticks: { beginAtZero: true, callback: (v) => money(v) } }], xAxes: [{ gridLines: { display: false } }] },
                    },
                });
            })();
        </script>
    @endif
@endpush
