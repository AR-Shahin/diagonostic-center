{{-- Lab report. Used by the admin panel, the patient account and the public invoice + PIN download. --}}
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Report · {{ $invoice->patient_name }} · {{ $invoice->invoice_no }}</title>
    <style>
        @page { size: A4; margin: 12mm 12mm 14mm; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #e9edf2; color: #17212b; font: 12px/1.45 'Inter', Arial, Helvetica, sans-serif; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .toolbar { position: sticky; top: 0; z-index: 5; display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; padding: 10px; background: #1d2733; }
        .toolbar a, .toolbar button { font: 600 13px Arial; color: #fff; background: #2f6fd1; border: 0; border-radius: 6px; padding: 8px 14px; text-decoration: none; cursor: pointer; }
        .toolbar .ghost { background: transparent; border: 1px solid #5b6b7d; }
        .sheet { width: 210mm; margin: 16px auto; padding: 12mm; background: #fff; box-shadow: 0 6px 30px rgba(0,0,0,.12); position: relative; }
        .head { display: flex; align-items: center; gap: 12px; padding-bottom: 8px; border-bottom: 2px solid #1d2733; }
        .head img { height: 54px; }
        .head h1 { margin: 0; font-size: 20px; }
        .head .muted { color: #56636f; font-size: 11px; }
        .pad-space { height: 32mm; }
        .patient { display: grid; grid-template-columns: 1.3fr 1fr 1fr; gap: 3px 16px; margin: 10px 0 6px; padding: 8px 10px; border: 1px solid #c8d1db; border-radius: 6px; }
        .patient span { color: #56636f; display: inline-block; min-width: 64px; }
        .dept { margin: 14px 0 4px; padding: 4px 8px; background: #eef2f6; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; font-size: 11px; border-radius: 4px; }
        .test { break-inside: avoid; margin-bottom: 10px; }
        .test h2 { font-size: 13.5px; margin: 8px 0 4px; }
        table.vals { width: 100%; border-collapse: collapse; }
        table.vals th { text-align: left; font-size: 10.5px; text-transform: uppercase; color: #56636f; border-bottom: 1.4px solid #1d2733; padding: 4px; }
        table.vals td { padding: 3.5px 4px; border-bottom: 1px solid #e6ebf0; }
        table.vals .grp td { font-weight: 700; font-size: 11px; padding-top: 7px; border-bottom: 0; }
        table.vals td.v { font-weight: 600; font-variant-numeric: tabular-nums; }
        .abn { font-weight: 800 !important; }
        .flag { display: inline-block; margin-left: 4px; font-size: 10px; font-weight: 800; }
        .body { padding: 2px 4px; }
        .body h4, .body h5 { margin: 8px 0 4px; }
        .body p { margin: 0 0 6px; }
        .comment { margin-top: 4px; font-size: 11px; color: #33414f; }
        .sample-line { color: #56636f; font-size: 10.5px; }
        .signs { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 18px; margin-top: 26px; break-inside: avoid; }
        .sign { min-width: 170px; text-align: center; font-size: 11px; }
        .sign img { height: 42px; display: block; margin: 0 auto 2px; }
        .sign .line { border-top: 1px solid #1d2733; padding-top: 3px; }
        .foot { display: flex; justify-content: space-between; align-items: flex-end; gap: 12px; margin-top: 18px; padding-top: 8px; border-top: 1px solid #c8d1db; font-size: 10px; color: #56636f; break-inside: avoid; }
        .qr { width: 78px; height: 78px; }
        .qr svg, .qr img { width: 100%; height: 100%; }
        .preview { position: fixed; inset: 40% 0 auto; text-align: center; font: 800 64px Arial; color: rgba(200, 0, 0, .14); transform: rotate(-18deg); pointer-events: none; }
        .empty { padding: 40px; text-align: center; color: #56636f; }
        @media print { body { background: #fff; } .toolbar { display: none; } .sheet { width: auto; margin: 0; padding: 0; box-shadow: none; } }
        @media (max-width: 820px) { .sheet { width: auto; margin: 8px; padding: 14px; } .patient { grid-template-columns: 1fr 1fr; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print / Save as PDF</button>
        @if ($backUrl)<a class="ghost" href="{{ $backUrl }}">Back</a>@endif
    </div>
    @if ($preview)<div class="preview">PREVIEW · NOT FOR PATIENT</div>@endif

    <div class="sheet">
        @if ($letterhead)
            <header class="head">
                @if ($site['logo'])<img src="{{ $site['logo'] }}" alt="{{ $site['name'] }}">@endif
                <div>
                    <h1>{{ $site['name'] }}</h1>
                    <div class="muted">{{ $invoice->branch->name_en }} · {{ $invoice->branch->address_en ?: $site['address'] }}</div>
                    <div class="muted">{{ collect([$invoice->branch->phone ?: $site['phone'], $site['email']])->filter()->implode(' · ') }}</div>
                </div>
            </header>
        @else
            <div class="pad-space" aria-hidden="true"></div>
        @endif

        <section class="patient">
            <div><span>Patient</span> <b>{{ $invoice->patient_name }}</b></div>
            <div><span>Age / Sex</span> {{ $invoice->patient_age ?? '—' }} / {{ ucfirst($invoice->patient_gender) }}</div>
            <div><span>Patient ID</span> {{ $invoice->patient?->patient_code }}</div>
            <div><span>Ref. by</span> {{ $invoice->referrer?->displayName() ?? 'Self' }}</div>
            <div><span>Invoice</span> {{ $invoice->invoice_no }}</div>
            <div><span>Bill date</span> {{ $invoice->created_at->format('d M Y, h:i A') }}</div>
        </section>

        @forelse ($items->groupBy(fn ($i) => $i->department?->name_en ?? 'Other') as $dept => $deptItems)
            <div class="dept">{{ $dept }}</div>
            @foreach ($deptItems as $item)
                @php
                    $r = $item->result;
                @endphp
                <article class="test">
                    <h2>{{ $item->name }}</h2>
                    @if ($item->sample)
                        <div class="sample-line">Sample {{ $item->sample->sample_no }} · collected {{ ($item->sample->collected_at ?? $item->sample->received_at)?->format('d M Y, h:i A') }} · reported {{ $r?->verified_at?->format('d M Y, h:i A') }}</div>
                    @endif

                    @if ($r?->report_type === 'parameter')
                        <table class="vals">
                            <thead><tr><th style="width:38%">Test</th><th>Result</th><th>Unit</th><th>Reference range</th></tr></thead>
                            <tbody>
                                @foreach ($r->values->groupBy(fn ($v) => $v->group_name ?? '') as $group => $vals)
                                    @if ($group !== '')<tr class="grp"><td colspan="4">{{ $group }}</td></tr>@endif
                                    @foreach ($vals as $v)
                                        @continue($v->value === null || $v->value === '')
                                        <tr>
                                            <td>{{ $v->name }}</td>
                                            <td class="v {{ $v->flag ? 'abn' : '' }}">{{ $v->value }}@if ($v->flag)<span class="flag">{{ $v->flag }}</span>@endif</td>
                                            <td>{{ $v->unit }}</td>
                                            <td>{{ $v->reference_text }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    @elseif ($r?->report_type === 'template')
                        <div class="body">{!! \App\Services\Lab\ReportHtml::clean((string) $r->body) !!}</div>
                    @elseif ($r?->report_type === 'culture')
                        @php
                            $c = $r->culture ?? [];
                        @endphp
                        @if (empty($c['growth']))
                            <p><b>Result:</b> No growth after 48 hours of aerobic incubation.</p>
                        @else
                            <p><b>Organism isolated:</b> {{ $c['organism'] ?? '' }} @if (! empty($c['colony_count']))· <b>Colony count:</b> {{ $c['colony_count'] }}@endif</p>
                            @if (! empty($c['antibiotics']))
                                <table class="vals">
                                    <thead><tr><th>Antibiotic</th><th>Sensitivity</th></tr></thead>
                                    <tbody>
                                        @foreach ($c['antibiotics'] as $ab)
                                            <tr><td>{{ $ab['name'] }}</td><td class="v {{ $ab['result'] === 'R' ? 'abn' : '' }}">{{ ['S' => 'Sensitive', 'I' => 'Intermediate', 'R' => 'Resistant'][$ab['result']] ?? $ab['result'] }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        @endif
                    @endif
                    @if ($r?->comment)<div class="comment"><b>Comment:</b> {{ $r->comment }}</div>@endif
                </article>
            @endforeach
        @empty
            <div class="empty">No verified report yet.</div>
        @endforelse

        @if ($items->isNotEmpty())
            <div class="signs">
                @foreach ($signers as $signer)
                    <div class="sign">
                        @if ($signer->signature)<img src="{{ asset($signer->signature) }}" alt="Signature of {{ $signer->name }}">@else<div style="height:42px"></div>@endif
                        <div class="line"><b>{{ $signer->name }}</b>
                            @if ($signer->qualification)<br>{{ $signer->qualification }}@endif
                            @if ($signer->designation)<br>{{ $signer->designation }}@endif
                        </div>
                    </div>
                @endforeach
            </div>

            <footer class="foot">
                <div>
                    This report is electronically verified. Results relate only to the sample tested and should be interpreted by a physician.<br>
                    Scan the QR code to check that this report is genuine. Printed {{ now()->format('d M Y, h:i A') }}.
                </div>
                <div class="qr" id="qr" data-url="{{ $invoice->verifyUrl() }}" role="img" aria-label="QR code to verify this report"></div>
            </footer>
        @endif
    </div>

    <script src="{{ asset('vendor/qrcode-generator/qrcode.js') }}"></script>
    <script>
        (function () {
            const box = document.getElementById('qr');
            if (!box || typeof qrcode === 'undefined') return;
            const qr = qrcode(0, 'M');
            qr.addData(box.dataset.url);
            qr.make();
            box.innerHTML = qr.createSvgTag({ cellSize: 2, margin: 0, scalable: true });
            @if (request('auto'))
                window.addEventListener('load', () => setTimeout(() => window.print(), 300));
            @endif
        })();
    </script>
</body>
</html>
