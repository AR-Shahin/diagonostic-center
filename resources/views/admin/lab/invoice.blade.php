@extends('admin.layouts.master')

@section('title', 'Lab · '.$invoice->invoice_no)

@push('css')
    <style>
        .card:hover { transform: none; }
        .barcode-svg { height: 34px; width: auto; }
        .num { font-variant-numeric: tabular-nums; }
    </style>
@endpush

@section('master_content')
    @php
        $ready = $invoice->labItems->whereIn('lab_status', ['ready', 'delivered']);
        $toVerify = $invoice->labItems->where('lab_status', 'processing');
        $canRelease = $invoice->canReleaseReport();
    @endphp

    <x-admin.page-header :title="$invoice->patient_name" icon="fas fa-vials" :subtitle="$invoice->invoice_no.' · '.$invoice->patient_age.' / '.ucfirst($invoice->patient_gender).' · '.$invoice->created_at->format('d M Y, h:i A').' · Ref: '.($invoice->referrer?->displayName() ?? 'Self')">
        <a href="{{ route('admin.lab.index') }}" class="btn btn-sm btn-light"><i class="fas fa-list me-1"></i> Work list</a>
        @if (in_array('invoice-view', $permissions))
            <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-file-invoice me-1"></i> Bill</a>
        @endif
    </x-admin.page-header>

    @if ($invoice->isCancelled())
        <div class="alert alert-secondary"><i class="fas fa-ban me-1"></i> This bill is cancelled. No lab work is needed.</div>
    @elseif ($invoice->hasDue())
        <div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-1"></i> Due ৳ {{ number_format((float) $invoice->due_amount, 2) }}.
            @unless ($canRelease) The report cannot be handed over until it is paid. @endunless</div>
    @endif

    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <b><i class="fas fa-flask text-primary me-1"></i> Tests</b>
                    @if ($toVerify->isNotEmpty() && in_array('lab-verify', $permissions))
                        <form method="POST" action="{{ route('admin.lab.verify-all', $invoice) }}" onsubmit="return confirm('Verify all {{ $toVerify->count() }} entered result(s)? Check them first.')">
                            @csrf<button class="btn btn-sm btn-success"><i class="fas fa-user-check me-1"></i> Verify all entered ({{ $toVerify->count() }})</button>
                        </form>
                    @endif
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="small text-muted"><tr><th>Test</th><th>Sample</th><th>Due</th><th>Status</th><th class="text-end"></th></tr></thead>
                        <tbody>
                            @foreach ($invoice->labItems as $item)
                                <tr>
                                    <td><b>{{ $item->name }}</b><div class="small text-muted">{{ $item->department?->name_en }}</div></td>
                                    <td>@if ($item->sample)<code>{{ $item->sample->sample_no }}</code>@else<span class="small text-muted">No sample</span>@endif</td>
                                    <td class="small {{ $item->isOverdue() ? 'text-danger fw-semibold' : '' }}">{{ $item->delivery_at?->format('d M, h:i A') }}</td>
                                    <td>{!! $item->labBadge() !!}
                                        @if ($item->result?->verifiedBy)<div class="small text-muted">by {{ $item->result->verifiedBy->name }}</div>@endif</td>
                                    <td class="text-end text-nowrap">
                                        @if ($item->lab_status !== 'cancelled')
                                            @php
                                                $canEnter = in_array($item->lab_status, ['collected', 'processing'], true) || ($item->lab_status === 'pending' && ! $item->sample_id);
                                            @endphp
                                            @if ($canEnter && in_array('lab-result', $permissions))
                                                <a class="btn btn-sm btn-primary" href="{{ route('admin.lab.result', $item) }}"><i class="fas fa-keyboard me-1"></i>{{ $item->lab_status === 'processing' ? 'Review' : 'Enter result' }}</a>
                                            @elseif ($item->isReportable())
                                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.lab.result', $item) }}">View</a>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><b><i class="fas fa-file-medical text-success me-1"></i> Report</b></div>
                <div class="card-body">
                    @if ($ready->isEmpty())
                        <p class="text-muted mb-0">No verified test yet. The report can be printed once tests are verified.</p>
                    @else
                        <p class="mb-2">{{ $ready->count() }} of {{ $invoice->labItems->where('lab_status', '!=', 'cancelled')->count() }} test(s) verified.</p>
                        <div class="d-flex flex-wrap gap-2">
                            @if ($canRelease)
                                <a class="btn btn-success" target="_blank" href="{{ route('admin.lab.report', $invoice) }}"><i class="fas fa-print me-1"></i> Print report</a>
                                <a class="btn btn-outline-success" target="_blank" href="{{ route('admin.lab.report', [$invoice, 'pad' => 1]) }}" title="For pre-printed letterhead paper"><i class="fas fa-print me-1"></i> Print on pad</a>
                            @else
                                <a class="btn btn-outline-secondary" target="_blank" href="{{ route('admin.lab.report', [$invoice, 'preview' => 1]) }}"><i class="fas fa-eye me-1"></i> Preview (not for the patient)</a>
                            @endif
                            @if ($canRelease && $invoice->labItems->where('lab_status', 'ready')->isNotEmpty() && in_array('lab-deliver', $permissions))
                                <form method="POST" action="{{ route('admin.lab.deliver', $invoice) }}">@csrf<button class="btn btn-outline-primary"><i class="fas fa-hand-holding-medical me-1"></i> Mark as delivered</button></form>
                            @endif
                        </div>
                    @endif
                    <hr>
                    <div class="small text-muted">
                        Online report: <b>{{ url('/download-report') }}</b> · Invoice <b>{{ $invoice->invoice_no }}</b> · PIN <b class="text-dark">{{ $invoice->report_pin }}</b>
                        (also printed on the invoice). The QR code on the report opens
                        <a href="{{ $invoice->verifyUrl() }}" target="_blank">this verification page</a>.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <b><i class="fas fa-vial text-danger me-1"></i> Samples</b>
                    @if ($invoice->samples->where('status', '!=', 'rejected')->isNotEmpty())
                        <a class="btn btn-sm btn-outline-secondary" target="_blank" href="{{ route('admin.lab.labels', $invoice) }}"><i class="fas fa-tags me-1"></i> Print labels</a>
                    @endif
                </div>
                <div class="card-body">
                    @forelse ($invoice->samples as $sample)
                        <div @class(['border rounded-3 p-3 mb-2', 'opacity-50' => $sample->status === 'rejected'])>
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <svg class="barcode-svg" data-barcode="{{ $sample->sample_no }}"></svg>
                                    <div class="fw-semibold">{{ $sample->sampleType?->name_en ?? 'Sample' }}</div>
                                    <div class="small text-muted">{{ $sample->sampleType?->container }}</div>
                                    <div class="small">{{ $sample->items->pluck('code')->implode(', ') }}</div>
                                </div>
                                <div class="text-end">{!! $sample->statusBadge() !!}
                                    @if ($sample->collected_at)<div class="small text-muted">{{ $sample->collected_at->format('d M, h:i A') }}</div>@endif
                                    @if ($sample->rejected_reason)<div class="small text-danger">{{ $sample->rejected_reason }}</div>@endif
                                </div>
                            </div>
                            @if (! $invoice->isCancelled() && in_array('lab-collect', $permissions) && $sample->status !== 'rejected')
                                <div class="d-flex flex-wrap gap-1 mt-2">
                                    @if ($sample->status === 'pending')
                                        <form method="POST" action="{{ route('admin.lab.collect', $sample) }}">@csrf<button class="btn btn-sm btn-success"><i class="fas fa-syringe me-1"></i> Collected</button></form>
                                    @endif
                                    @if (in_array($sample->status, ['pending', 'collected'], true))
                                        <form method="POST" action="{{ route('admin.lab.receive', $sample) }}">@csrf<button class="btn btn-sm btn-outline-primary"><i class="fas fa-inbox me-1"></i> Received in lab</button></form>
                                    @endif
                                    <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#rejectModal" data-action="{{ route('admin.lab.reject', $sample) }}" data-no="{{ $sample->sample_no }}"><i class="fas fa-times me-1"></i> Reject</button>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">No sample needed (imaging / ECG only).</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectTitle" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" id="rejectForm">
                @csrf
                <div class="modal-header"><h5 class="modal-title" id="rejectTitle">Reject sample <span data-no></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <p class="small text-muted">A new barcode is created so the sample can be collected again.</p>
                    <label class="form-label fw-semibold" for="rejectReason">Reason</label>
                    <select class="form-select mb-2" onchange="document.getElementById('rejectReason').value = this.value" aria-label="Common reasons">
                        <option value="">Choose a common reason…</option>
                        <option>Haemolysed</option><option>Clotted</option><option>Insufficient quantity</option><option>Wrong container</option><option>Label mismatch</option>
                    </select>
                    <input class="form-control" id="rejectReason" name="reason" required>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button><button class="btn btn-danger">Reject sample</button></div>
            </form>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('vendor/jsbarcode/JsBarcode.all.min.js') }}"></script>
    <script>
        document.querySelectorAll('[data-barcode]').forEach((el) => JsBarcode(el, el.dataset.barcode, { format: 'CODE128', height: 34, width: 1.4, fontSize: 11, margin: 0, displayValue: true }));
        document.getElementById('rejectModal').addEventListener('show.bs.modal', (e) => {
            document.getElementById('rejectForm').action = e.relatedTarget.dataset.action;
            e.target.querySelector('[data-no]').textContent = e.relatedTarget.dataset.no;
        });
    </script>
@endpush
