<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Hind+Siliguri:wght@400;600&display=swap">
    <style>
        @page { size: {{ $paper === 'a5' ? 'A5' : 'A4' }}; margin: 10mm; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #e9edf2; color: #1d2733; font: 12px/1.45 'Inter', 'Hind Siliguri', Arial, sans-serif; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .sheet { width: {{ $paper === 'a5' ? '148mm' : '210mm' }}; min-height: 100px; margin: 16px auto; background: #fff; padding: 10mm; box-shadow: 0 6px 30px rgba(0,0,0,.12); position: relative; overflow: hidden; }
        .toolbar { position: sticky; top: 0; z-index: 5; display: flex; gap: 8px; justify-content: center; padding: 10px; background: #1d2733; }
        .toolbar a, .toolbar button { font: 600 13px Inter, Arial; color: #fff; background: #2f6fd1; border: 0; border-radius: 6px; padding: 8px 14px; text-decoration: none; cursor: pointer; }
        .toolbar .ghost { background: transparent; border: 1px solid #5b6b7d; }
        .toolbar select { border-radius: 6px; padding: 6px; }
        .head { display: flex; align-items: center; gap: 12px; padding-bottom: 8px; border-bottom: 2px solid #1d2733; }
        .head img { height: 52px; width: auto; }
        .head .org { flex: 1; }
        .head h1 { margin: 0; font-size: 20px; letter-spacing: .2px; }
        .head .muted { color: #5a6876; font-size: 11px; }
        .doc-title { text-align: right; }
        .doc-title b { display: inline-block; font-size: 13px; letter-spacing: 2px; padding: 4px 10px; border: 1.5px solid #1d2733; border-radius: 4px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 3px 18px; margin: 10px 0; padding: 8px 10px; border: 1px solid #c9d2dc; border-radius: 6px; }
        .grid div span { color: #5a6876; display: inline-block; min-width: 72px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.items th { text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: .4px; color: #5a6876; border-bottom: 1.5px solid #1d2733; padding: 5px 4px; }
        table.items td { padding: 5px 4px; border-bottom: 1px solid #e3e8ee; vertical-align: top; }
        table.items .sub { color: #5a6876; font-size: 10.5px; }
        .r { text-align: right; } .num { font-variant-numeric: tabular-nums; white-space: nowrap; }
        .totals { display: flex; justify-content: space-between; gap: 20px; margin-top: 8px; }
        .totals table { border-collapse: collapse; min-width: 240px; }
        .totals td { padding: 2px 4px; }
        .totals .big td { font-size: 14px; font-weight: 700; border-top: 1.5px solid #1d2733; padding-top: 5px; }
        .words { flex: 1; font-size: 11px; color: #33414f; }
        .totals { position: relative; }
        .stamp { position: absolute; right: 280px; top: 18px; transform: rotate(-14deg); font: 800 30px Inter, Arial; letter-spacing: 4px; padding: 4px 16px; border: 4px solid; border-radius: 8px; opacity: .2; }
        .stamp.paid { color: #13804b; } .stamp.due { color: #c62828; } .stamp.cancelled { color: #555; }
        .foot { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 26px; font-size: 11px; }
        .sign { border-top: 1px solid #1d2733; padding-top: 3px; min-width: 150px; text-align: center; }
        .notice { margin-top: 10px; padding: 6px 8px; background: #f3f6f9; border-radius: 5px; font-size: 10.5px; color: #3c4a58; }
        @media print { body { background: #fff; } .toolbar { display: none; } .sheet { margin: 0; box-shadow: none; width: auto; padding: 0; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print</button>
        <select aria-label="Paper size" onchange="location.search = new URLSearchParams({ paper: this.value }).toString()">
            <option value="a4" @selected($paper !== 'a5')>A4</option>
            <option value="a5" @selected($paper === 'a5')>A5</option>
        </select>
        @yield('toolbar')
    </div>
    <div class="sheet">
        <header class="head">
            @if ($site['logo'])<img src="{{ $site['logo'] }}" alt="{{ $site['name'] }}">@endif
            <div class="org">
                <h1>{{ $site['name'] }}</h1>
                <div class="muted">{{ $branch->name_en }} · {{ $branch->address_en ?: $site['address'] }}</div>
                <div class="muted">{{ collect([$branch->phone ?: $site['phone'], $branch->email ?: $site['email']])->filter()->implode(' · ') }}</div>
            </div>
            <div class="doc-title"><b>@yield('doc')</b></div>
        </header>
        @yield('body')
    </div>
    @if (request('auto'))
        <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
    @endif
</body>
</html>
