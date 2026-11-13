@extends('admin.print.layout', ['paper' => request('paper', 'a5'), 'branch' => $invoice->branch])

@section('title', 'Receipt '.$payment->receipt_no)
@section('doc', $payment->type === 'refund' ? 'REFUND RECEIPT' : 'MONEY RECEIPT')

@section('toolbar')
    <a class="ghost" href="{{ route('admin.invoices.show', $invoice) }}">Open invoice</a>
@endsection

@section('body')
    <section class="grid">
        <div><span>Receipt no</span> <b>{{ $payment->receipt_no }}</b></div>
        <div><span>Date</span> {{ $payment->created_at->format('d M Y, h:i A') }}</div>
        <div><span>Invoice no</span> {{ $invoice->invoice_no }}</div>
        <div><span>Patient</span> <b>{{ $invoice->patient_name }}</b></div>
        <div><span>Mobile</span> {{ $invoice->patient_mobile }}</div>
        <div><span>Method</span> {{ \App\Models\Payment::METHODS[$payment->method] ?? $payment->method }}{{ $payment->transaction_ref ? ' · '.$payment->transaction_ref : '' }}</div>
    </section>

    <table class="items">
        <tbody>
            <tr><td>{{ $payment->type === 'refund' ? 'Amount refunded' : 'Amount received' }}</td><td class="r num" style="font-size:16px"><b>৳ {{ number_format((float) $payment->amount, 2) }}</b></td></tr>
            <tr><td colspan="2"><b>In words:</b> {{ \App\Support\AmountInWords::taka((float) $payment->amount) }}</td></tr>
            @if ($payment->note)<tr><td colspan="2" class="sub">{{ $payment->note }}</td></tr>@endif
        </tbody>
    </table>

    <div class="totals">
        <div class="words">Bill status today</div>
        <table class="num">
            <tr><td>Net payable</td><td class="r">{{ number_format((float) $invoice->net_amount, 2) }}</td></tr>
            <tr><td>Total paid</td><td class="r">{{ number_format((float) $invoice->paid_amount, 2) }}</td></tr>
            <tr class="big"><td>Due</td><td class="r">৳ {{ number_format((float) $invoice->due_amount, 2) }}</td></tr>
        </table>
    </div>

    <div class="foot">
        <div>Received by: {{ $payment->receiver->name ?? '—' }}</div>
        <div class="sign">Authorised signature</div>
    </div>
@endsection
