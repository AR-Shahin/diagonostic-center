@extends('admin.print.layout', ['paper' => request('paper', 'a4'), 'branch' => $invoice->branch])

@section('title', 'Invoice '.$invoice->invoice_no)
@section('doc', 'INVOICE')

@section('toolbar')
    <a class="ghost" href="{{ route('admin.invoices.show', $invoice) }}">Open invoice</a>
    <a class="ghost" href="{{ route('admin.invoices.create') }}">New bill</a>
@endsection

@section('body')
    @php
        $stamp = $invoice->isCancelled() ? 'cancelled' : ($invoice->hasDue() ? 'due' : 'paid');
    @endphp

    <section class="grid">
        <div><span>Invoice no</span> <b>{{ $invoice->invoice_no }}</b></div>
        <div><span>Date</span> {{ $invoice->created_at->format('d M Y, h:i A') }}</div>
        <div><span>Patient</span> <b>{{ $invoice->patient_name }}</b></div>
        <div><span>Patient ID</span> {{ $invoice->patient->patient_code ?? '' }}</div>
        <div><span>Age / Sex</span> {{ $invoice->patient_age ?? '—' }} / {{ ucfirst($invoice->patient_gender) }}</div>
        <div><span>Mobile</span> {{ $invoice->patient_mobile }}</div>
        <div><span>Ref. by</span> {{ $invoice->referrer?->displayName() ?? 'Self' }}</div>
        <div><span>Delivery</span> <b>{{ $invoice->delivery_at?->format('d M Y, h:i A') }}</b></div>
    </section>

    <table class="items">
        <thead><tr><th style="width:28px">SL</th><th>Test / Package</th><th class="r">Amount (৳)</th></tr></thead>
        <tbody>
            @foreach ($invoice->billedItems as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><b>{{ $item->name }}</b>
                        @if ($item->children->isNotEmpty())<div class="sub">{{ $item->children->pluck('name')->implode(', ') }}</div>@endif
                    </td>
                    <td class="r num">{{ number_format((float) $item->price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="stamp {{ $stamp }}">{{ strtoupper($stamp) }}</div>
        <div class="words">
            <b>In words:</b> {{ \App\Support\AmountInWords::taka((float) $invoice->net_amount) }}
            @if ($invoice->home_collection)<div style="margin-top:6px">✔ Home sample collection</div>@endif
            @if ($invoice->note)<div style="margin-top:6px"><b>Note:</b> {{ $invoice->note }}</div>@endif
        </div>
        <table class="num">
            <tr><td>Subtotal</td><td class="r">{{ number_format((float) $invoice->subtotal, 2) }}</td></tr>
            @if ((float) $invoice->discount_amount > 0)
                <tr><td>Discount{{ $invoice->discount_type === 'percent' ? ' ('.(float) $invoice->discount_value.'%)' : '' }}</td><td class="r">− {{ number_format((float) $invoice->discount_amount, 2) }}</td></tr>
            @endif
            @if ((float) $invoice->vat_amount > 0)<tr><td>VAT ({{ (float) $invoice->vat_percent }}%)</td><td class="r">{{ number_format((float) $invoice->vat_amount, 2) }}</td></tr>@endif
            @if ((float) $invoice->home_collection_charge > 0)<tr><td>Home collection</td><td class="r">{{ number_format((float) $invoice->home_collection_charge, 2) }}</td></tr>@endif
            @if ((float) $invoice->round_off != 0)<tr><td>Round off</td><td class="r">{{ number_format((float) $invoice->round_off, 2) }}</td></tr>@endif
            <tr class="big"><td>Net payable</td><td class="r">৳ {{ number_format((float) $invoice->net_amount, 2) }}</td></tr>
            <tr><td>Paid</td><td class="r">{{ number_format((float) $invoice->paid_amount, 2) }}</td></tr>
            <tr><td><b>Due</b></td><td class="r"><b>{{ number_format((float) $invoice->due_amount, 2) }}</b></td></tr>
        </table>
    </div>

    <div class="notice">
        Please bring this invoice when collecting your report.
        @if ($invoice->hasDue() && ! $invoice->canReleaseReport()) Reports are delivered after the due is paid. @endif
        <b>Online report:</b> {{ url('/download-report') }} · Invoice no + PIN <b style="font-size:13px;letter-spacing:1px">{{ $invoice->report_pin }}</b>, or log in with your mobile number.
    </div>

    <div class="foot">
        <div>Prepared by: {{ $invoice->creator->name ?? '—' }}<br>Printed: {{ now()->format('d M Y, h:i A') }}</div>
        <div class="sign">Authorised signature</div>
    </div>
@endsection
