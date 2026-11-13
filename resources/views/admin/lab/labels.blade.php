<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Labels · {{ $invoice->invoice_no }}</title>
    @php
        $sizes = ['50x25' => [50, 25], '38x25' => [38, 25], '60x30' => [60, 30]];
        $size = $sizes[request('size')] ?? $sizes['50x25'];
        $copies = max(1, min(5, (int) request('copies', 1)));
    @endphp
    <style>
        @page { size: {{ $size[0] }}mm {{ $size[1] }}mm; margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; font: 8px/1.15 Arial, Helvetica, sans-serif; color: #000; background: #e9edf2; }
        .toolbar { position: sticky; top: 0; display: flex; gap: 8px; justify-content: center; align-items: center; padding: 10px; background: #1d2733; color: #fff; font: 13px Arial; }
        .toolbar button { background: #2f6fd1; color: #fff; border: 0; border-radius: 6px; padding: 7px 14px; font-weight: 600; cursor: pointer; }
        .toolbar select { border-radius: 5px; padding: 4px; }
        .sheet { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; padding: 16px; }
        .label { width: {{ $size[0] }}mm; height: {{ $size[1] }}mm; padding: 1.2mm 1.6mm; background: #fff; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 1px 4px rgba(0,0,0,.2); }
        .label .name { font-weight: 700; font-size: 9px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .label .row { display: flex; justify-content: space-between; gap: 4px; white-space: nowrap; }
        .label svg { width: 100%; height: auto; flex: 1 1 auto; min-height: 0; max-height: 58%; margin: .4mm 0 .6mm; }
        .label .tests { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        @media print { body { background: #fff; } .toolbar { display: none; } .sheet { display: block; padding: 0; } .label { box-shadow: none; page-break-after: always; } }
    </style>
</head>
<body>
    <form class="toolbar" method="GET">
        <button type="button" onclick="window.print()">Print labels</button>
        <label>Size <select name="size" onchange="this.form.submit()">@foreach ($sizes as $k => $v)<option value="{{ $k }}" @selected(request('size', '50x25') === $k)>{{ $v[0] }} × {{ $v[1] }} mm</option>@endforeach</select></label>
        <label>Copies <select name="copies" onchange="this.form.submit()">@foreach (range(1, 3) as $n)<option @selected($copies === $n)>{{ $n }}</option>@endforeach</select></label>
    </form>
    <div class="sheet">
        @forelse ($invoice->samples as $sample)
            @for ($i = 0; $i < $copies; $i++)
                <div class="label">
                    <div class="name">{{ $invoice->patient_name }}</div>
                    <div class="row"><span>{{ $invoice->patient_age }} / {{ strtoupper(substr($invoice->patient_gender, 0, 1)) }}</span><span>{{ $invoice->created_at->format('d/m/y H:i') }}</span></div>
                    <svg data-barcode="{{ $sample->sample_no }}"></svg>
                    <div class="row"><b>{{ \Illuminate\Support\Str::limit($sample->sampleType?->name_en, 18, '') }}</b><span>{{ $invoice->branch?->code }}</span></div>
                    <div class="tests">{{ $sample->items->pluck('code')->implode(', ') }}</div>
                </div>
            @endfor
        @empty
            <p>No samples on this bill.</p>
        @endforelse
    </div>
    <script src="{{ asset('vendor/jsbarcode/JsBarcode.all.min.js') }}"></script>
    <script>
        document.querySelectorAll('[data-barcode]').forEach((el) => JsBarcode(el, el.dataset.barcode, { format: 'CODE128', height: 32, width: 1.6, fontSize: 12, margin: 0, textMargin: 1, marginBottom: 2 }));
    </script>
</body>
</html>
