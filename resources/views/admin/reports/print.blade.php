@extends('admin.commission.print-layout')

@section('title', $report['title'])

@section('master_content')
    <h2 style="margin:0 0 2px">{{ $report['title'] }}</h2>
    <p style="margin:0 0 10px">{{ $from->format('d M Y') }} – {{ $to->format('d M Y') }} · {{ $branchName }}</p>
    @if ($report['cards'])
        <table style="margin-bottom:10px">
            @foreach (array_chunk($report['cards'], 4) as $chunk)
                <tr>@foreach ($chunk as $card)<td><span class="text-muted small">{{ $card[0] }}</span><br><b>{{ $card[1] }}</b></td>@endforeach</tr>
            @endforeach
        </table>
    @endif
    @if ($report['note'])<p class="small text-muted">{{ $report['note'] }}</p>@endif
    @include('admin.reports._table')
@endsection
