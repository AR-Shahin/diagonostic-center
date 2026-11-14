<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title')</title>
    <style>
        @page { size: A4; margin: 12mm; }
        body { font: 12px/1.45 Arial, Helvetica, sans-serif; color: #17212b; margin: 0; padding: 16px; }
        .head { display: flex; justify-content: space-between; border-bottom: 2px solid #17212b; padding-bottom: 6px; margin-bottom: 12px; }
        .head h1 { margin: 0; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid #c8d1db; padding: 4px 6px; text-align: left; }
        .text-end { text-align: right; } .small { font-size: 10px; } .text-muted { color: #56636f; } .fw-bold, .fw-semibold { font-weight: 700; }
        .table-danger { background: #fdeaea; }
        .toolbar { margin-bottom: 12px; } @media print { .toolbar { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="toolbar"><button onclick="window.print()">Print</button></div>
    <div class="head"><h1>{{ $site['name'] }}</h1><div class="small">{{ collect([$site['address'], $site['phone']])->filter()->implode(' · ') }}</div></div>
    @yield('master_content')
    <p class="small text-muted" style="margin-top:14px">Printed {{ now()->format('d M Y, h:i A') }}</p>
</body>
</html>
