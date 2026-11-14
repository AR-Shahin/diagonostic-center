@extends('admin.commission.print-layout')

@section('title', 'Payout '.$payout->payout_no)

@section('master_content')
    <h2 style="margin:0 0 8px">Commission payment voucher</h2>
    <table style="margin-bottom:12px">
        <tr><th style="width:22%">Voucher no</th><td>{{ $payout->payout_no }}</td><th style="width:18%">Date</th><td>{{ $payout->created_at->format('d M Y, h:i A') }}</td></tr>
        <tr><th>Paid to</th><td>{{ $payout->referrer->displayName() }}</td><th>Covers</th><td>Commission up to {{ $payout->period_to->format('d M Y') }}</td></tr>
        <tr><th>Method</th><td>{{ \App\Models\Payment::METHODS[$payout->method] ?? $payout->method }} {{ $payout->reference ? '· '.$payout->reference : '' }}</td><th>Amount</th><td class="fw-bold">৳ {{ number_format((float) $payout->amount, 2) }}</td></tr>
        <tr><th>In words</th><td colspan="3">{{ \App\Support\AmountInWords::taka((float) $payout->amount) }}</td></tr>
    </table>
    <table>
        <thead><tr><th>Date</th><th>Bill</th><th>Patient</th><th>Test</th><th class="text-end">Commission</th></tr></thead>
        <tbody>
            @foreach ($payout->entries->sortBy('id') as $e)
                <tr><td>{{ $e->created_at->format('d M Y') }}</td><td>{{ $e->invoice?->invoice_no }}</td><td>{{ $e->invoice?->patient_name }}</td><td>{{ $e->type === 'reversal' ? $e->note : $e->item?->name }}</td><td class="text-end">{{ number_format((float) $e->amount, 2) }}</td></tr>
            @endforeach
        </tbody>
        <tfoot><tr><th colspan="4" class="text-end">Total</th><th class="text-end">৳ {{ number_format((float) $payout->amount, 2) }}</th></tr></tfoot>
    </table>
    <div style="display:flex;justify-content:space-between;margin-top:50px">
        <div style="border-top:1px solid #000;padding-top:4px;min-width:180px;text-align:center">Paid by: {{ $payout->payer->name ?? '' }}</div>
        <div style="border-top:1px solid #000;padding-top:4px;min-width:180px;text-align:center">Received by</div>
    </div>
@endsection
