@php
    $fmt = function ($value, string $kind) {
        if ($value === null || $value === '') {
            return '—';
        }

        return match ($kind) {
            'money' => number_format((float) $value, 2),
            'int' => number_format((int) $value),
            'pct' => rtrim(rtrim(number_format((float) $value, 1), '0'), '.').'%',
            'hours' => (float) $value >= 48 ? round($value / 24, 1).' d' : rtrim(rtrim(number_format((float) $value, 1), '0'), '.').' h',
            default => e($value),
        };
    };
@endphp
<table class="table table-sm table-hover align-middle mb-0 report-table">
    <thead>
        <tr>
            @foreach ($report['columns'] as [$label, $kind])
                <th @class(['text-end' => $kind !== 'text'])>{{ $label }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse ($report['rows'] as $row)
            <tr>
                @foreach ($report['columns'] as $key => [$label, $kind])
                    <td @class(['text-end num' => $kind !== 'text', 'text-danger' => $kind === 'money' && ($row[$key] ?? 0) < 0])>{!! $fmt($row[$key] ?? null, $kind) !!}</td>
                @endforeach
            </tr>
        @empty
            <tr><td colspan="{{ count($report['columns']) }}" class="text-center text-muted py-4">Nothing in this period.</td></tr>
        @endforelse
    </tbody>
    @if ($report['totals'] && $report['rows'])
        <tfoot>
            <tr class="fw-bold">
                @foreach ($report['columns'] as $key => [$label, $kind])
                    <td @class(['text-end num' => $kind !== 'text'])>{!! array_key_exists($key, $report['totals']) ? $fmt($report['totals'][$key], $kind) : '' !!}</td>
                @endforeach
            </tr>
        </tfoot>
    @endif
</table>
