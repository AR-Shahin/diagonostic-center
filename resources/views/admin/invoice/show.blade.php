@extends('admin.layouts.master')

@section('title', $invoice->invoice_no)

@push('css')
    <style>
        .num { font-variant-numeric: tabular-nums; }
        .inv-items .child td { font-size: .85rem; color: var(--bs-secondary-color); border-top: 0; }
        .inv-items .child td:nth-child(2) { padding-left: 2rem; }
        .card:hover { transform: none; }
    </style>
@endpush

@section('master_content')
    <x-admin.page-header :title="$invoice->invoice_no" icon="fas fa-file-invoice" :subtitle="$invoice->created_at->format('d M Y, h:i A').' · '.$invoice->branch->name_en.' · by '.($invoice->creator->name ?? '—')">
        <span class="align-self-center">{!! $invoice->statusBadge() !!}</span>
        <a href="{{ route('admin.invoices.print', $invoice) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fas fa-print me-1"></i> Print invoice</a>
        @if (in_array('lab-view', $permissions) && ! $invoice->isCancelled())
            <a href="{{ route('admin.lab.invoice', $invoice) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-microscope me-1"></i> Lab & report</a>
        @endif
        @if ($invoice->hasDue() && in_array('invoice-collect', $permissions))
            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#collectModal"><i class="fas fa-hand-holding-usd me-1"></i> Collect due</button>
        @endif
        @if (! $invoice->isCancelled() && in_array('invoice-cancel', $permissions))
            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal"><i class="fas fa-ban me-1"></i> Cancel</button>
        @endif
        @if (in_array('invoice-create', $permissions))
            <a href="{{ route('admin.invoices.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i> New bill</a>
        @endif
    </x-admin.page-header>

    @if (request('receipt'))
        <div class="alert alert-success d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span><i class="fas fa-check-circle me-1"></i> Payment received.</span>
            <a href="{{ route('admin.invoices.receipt', (int) request('receipt')) }}" target="_blank" class="btn btn-sm btn-success"><i class="fas fa-receipt me-1"></i> Print money receipt</a>
        </div>
    @endif

    @if ($invoice->isCancelled())
        <div class="alert alert-secondary"><i class="fas fa-ban me-1"></i> Cancelled on {{ $invoice->cancelled_at->format('d M Y, h:i A') }} by {{ $invoice->canceller->name ?? '—' }}. Reason: <b>{{ $invoice->cancel_reason }}</b></div>
    @elseif ($invoice->hasDue())
        <div class="alert alert-warning d-flex flex-wrap justify-content-between gap-2">
            <span><i class="fas fa-exclamation-triangle me-1"></i> Due <b>৳ {{ number_format((float) $invoice->due_amount, 2) }}</b>.
                @unless ($invoice->canReleaseReport()) The report must be held until the due is paid. @endunless</span>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body p-0 table-responsive">
                    <table class="table align-middle mb-0 inv-items">
                        <thead class="small text-muted"><tr><th>#</th><th>Item</th><th>Report by</th><th class="text-end">Price</th><th class="text-end">Discount</th><th class="text-end">Net</th></tr></thead>
                        <tbody>
                            @foreach ($invoice->billedItems as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><span class="fw-semibold">{{ $item->name }}</span> <span class="text-muted small">{{ $item->code }}</span>
                                        @if ($item->item_type === 'package')<span class="badge text-bg-warning ms-1">Package</span>@endif</td>
                                    <td class="small">{{ $item->delivery_at?->format('d M, h:i A') ?? '' }}</td>
                                    <td class="text-end num">৳ {{ number_format((float) $item->price, 2) }}</td>
                                    <td class="text-end num text-danger">{{ (float) $item->discount_amount > 0 ? '− '.number_format((float) $item->discount_amount, 2) : '' }}</td>
                                    <td class="text-end num fw-semibold">৳ {{ number_format((float) $item->net_amount, 2) }}</td>
                                </tr>
                                @foreach ($item->children as $child)
                                    <tr class="child">
                                        <td></td><td>↳ {{ $child->name }}</td><td>{{ $child->delivery_at?->format('d M, h:i A') }}</td>
                                        <td class="text-end num" title="Share of the package price">{{ number_format((float) $child->price, 2) }}</td><td></td><td></td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><b>Payments</b></div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="small text-muted"><tr><th>Receipt</th><th>Date</th><th>Method</th><th>By</th><th class="text-end">Amount</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($invoice->payments as $pay)
                                <tr @class(['table-danger' => $pay->type === 'refund', 'table-success' => (int) request('receipt') === $pay->id])>
                                    <td>{{ $pay->receipt_no }} @if ($pay->type === 'refund')<span class="badge text-bg-danger">Refund</span>@endif</td>
                                    <td>{{ $pay->created_at->format('d M Y, h:i A') }}</td>
                                    <td>{{ \App\Models\Payment::METHODS[$pay->method] ?? $pay->method }} @if ($pay->transaction_ref)<span class="text-muted small">({{ $pay->transaction_ref }})</span>@endif</td>
                                    <td>{{ $pay->receiver->name ?? '—' }}</td>
                                    <td class="text-end num fw-semibold">{{ $pay->type === 'refund' ? '−' : '' }} ৳ {{ number_format((float) $pay->amount, 2) }}</td>
                                    <td class="text-end"><a href="{{ route('admin.invoices.receipt', $pay) }}" target="_blank" class="btn btn-sm btn-light" title="Print receipt"><i class="fas fa-receipt"></i></a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-3">No payment yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <a href="{{ route('admin.patients.show', $invoice->patient_id) }}" class="fw-semibold fs-5 text-decoration-none">{{ $invoice->patient_name }}</a>
                            <div class="small text-muted">{{ $invoice->patient->patient_code ?? '' }} · {{ $invoice->patient_mobile }}</div>
                            <div class="small text-muted">{{ \App\Models\Patient::GENDERS[$invoice->patient_gender] ?? '' }} · {{ $invoice->patient_age ?? '—' }}</div>
                        </div>
                        <i class="fas fa-user-circle fa-2x text-muted"></i>
                    </div>
                    <hr>
                    <div class="small"><span class="text-muted">Ref. by:</span> {{ $invoice->referrer?->displayName() ?? 'Self' }}</div>
                    @if ($invoice->home_collection)<div class="small"><i class="fas fa-home text-primary me-1"></i> Home sample collection</div>@endif
                    <div class="small"><span class="text-muted">Report delivery:</span> {{ $invoice->delivery_at?->format('d M Y, h:i A') }}</div>
                    @if ($invoice->note)<div class="small mt-2 p-2 bg-body-tertiary rounded">{{ $invoice->note }}</div>@endif
                </div>
            </div>

            <div class="card">
                <div class="card-body num">
                    <div class="d-flex justify-content-between"><span class="text-muted">Subtotal</span><span>৳ {{ number_format((float) $invoice->subtotal, 2) }}</span></div>
                    @if ((float) $invoice->discount_amount > 0)
                        <div class="d-flex justify-content-between text-danger"><span>Discount {{ $invoice->discount_type === 'percent' ? '('.(float) $invoice->discount_value.'%)' : '' }}</span><span>− ৳ {{ number_format((float) $invoice->discount_amount, 2) }}</span></div>
                        <div class="small text-muted mb-1">{{ $invoice->discount_reason }}@if ($invoice->discountApprover) · approved by {{ $invoice->discountApprover->name }}@endif</div>
                    @endif
                    @if ((float) $invoice->vat_amount > 0)<div class="d-flex justify-content-between"><span class="text-muted">VAT ({{ (float) $invoice->vat_percent }}%)</span><span>৳ {{ number_format((float) $invoice->vat_amount, 2) }}</span></div>@endif
                    @if ((float) $invoice->home_collection_charge > 0)<div class="d-flex justify-content-between"><span class="text-muted">Home collection</span><span>৳ {{ number_format((float) $invoice->home_collection_charge, 2) }}</span></div>@endif
                    @if ((float) $invoice->round_off != 0)<div class="d-flex justify-content-between small"><span class="text-muted">Round off</span><span>{{ number_format((float) $invoice->round_off, 2) }}</span></div>@endif
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fs-5"><b>Net payable</b><b>৳ {{ number_format((float) $invoice->net_amount, 2) }}</b></div>
                    <div class="d-flex justify-content-between text-success"><span>Paid</span><span>৳ {{ number_format((float) $invoice->paid_amount, 2) }}</span></div>
                    <div class="d-flex justify-content-between fs-5 {{ $invoice->hasDue() ? 'text-danger' : '' }}"><b>Due</b><b>৳ {{ number_format((float) $invoice->due_amount, 2) }}</b></div>
                </div>
            </div>
        </div>
    </div>

    @if ($invoice->hasDue() && in_array('invoice-collect', $permissions))
        <div class="modal fade" id="collectModal" tabindex="-1" aria-labelledby="collectTitle" aria-hidden="true">
            <div class="modal-dialog">
                <form class="modal-content" method="POST" action="{{ route('admin.invoices.collect', $invoice) }}">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title" id="collectTitle">Collect due</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">
                        <p class="mb-2">Outstanding: <b class="text-danger">৳ {{ number_format((float) $invoice->due_amount, 2) }}</b></p>
                        <x-admin.input name="amount" label="Amount (৳)" type="number" step="0.01" min="1" :max="(float) $invoice->due_amount" :value="(float) $invoice->due_amount" required />
                        <x-admin.select name="method" label="Method" :options="\App\Models\Payment::METHODS" default="cash" />
                        <x-admin.input name="transaction_ref" label="Transaction ID (bKash / Nagad / card)" />
                        <x-admin.input name="note" label="Note" />
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button><button class="btn btn-success"><i class="fas fa-check me-1"></i> Receive</button></div>
                </form>
            </div>
        </div>
    @endif

    @if (! $invoice->isCancelled() && in_array('invoice-cancel', $permissions))
        <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelTitle" aria-hidden="true">
            <div class="modal-dialog">
                <form class="modal-content" method="POST" action="{{ route('admin.invoices.cancel', $invoice) }}">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title" id="cancelTitle">Cancel invoice</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">
                        <p>This cancels all tests on the bill.
                            @if ((float) $invoice->paid_amount > 0) <b>৳ {{ number_format((float) $invoice->paid_amount, 2) }}</b> will be recorded as refunded. @endif</p>
                        <x-admin.input name="reason" label="Reason" required />
                        @if ((float) $invoice->paid_amount > 0)
                            <x-admin.select name="refund_method" label="Refund method" :options="\App\Models\Payment::METHODS" default="cash" />
                        @else
                            <input type="hidden" name="refund_method" value="cash">
                        @endif
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Keep invoice</button><button class="btn btn-danger">Cancel invoice</button></div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('script')
    @if ($errors->has('amount') || $errors->has('method'))
        <script>document.addEventListener('DOMContentLoaded', () => bootstrap.Modal.getOrCreateInstance('#collectModal').show());</script>
    @endif
    @if ($errors->has('reason'))
        <script>document.addEventListener('DOMContentLoaded', () => bootstrap.Modal.getOrCreateInstance('#cancelModal').show());</script>
    @endif
@endpush
