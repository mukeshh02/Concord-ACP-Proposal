<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
/*  ═══════════════════════════════════════════════════
    ACP PROPOSALS — PDF TEMPLATE
    A4 = 210mm × 297mm
    All text zones are position:absolute within each page
    ═══════════════════════════════════════════════════ */

* { margin: 0; padding: 0; box-sizing: border-box; }

@page {
    margin: 0;
    size: A4 portrait;
}

body {
    margin: 0;
    padding: 0;
    font-family: Georgia, 'Times New Roman', serif;
    background: #FAF8F5;
}

/* ─── PAGE WRAPPER ─────────────────────────────────── */
.page {
    position: relative;
    width: 210mm;
    height: 297mm;
    overflow: hidden;
    page-break-after: always;
    page-break-inside: avoid;
}

.page:last-child {
    page-break-after: auto;
}

/* ─── BACKGROUND IMAGE ─────────────────────────────── */
.page-bg {
    position: absolute;
    top: 0; left: 0;
    width: 210mm;
    height: 297mm;
    object-fit: cover;
    z-index: 1;
}

.page-bg-plain {
    position: absolute;
    top: 0; left: 0;
    width: 210mm;
    height: 297mm;
    background: #FAF8F5;
    z-index: 1;
}

/* ─── TEXT ZONE BASE ───────────────────────────────── */
.tz {
    position: absolute;
    z-index: 10;
}

/* ─── TYPOGRAPHY ───────────────────────────────────── */
.t-gold        { color: #C9A96E; }
.t-dark        { color: #1A1A1A; }
.t-muted       { color: #7a7260; }
.t-white       { color: #ffffff; }
.t-serif       { font-family: Georgia, 'Times New Roman', serif; }
.t-sans        { font-family: Arial, Helvetica, sans-serif; }
.t-italic      { font-style: italic; }
.t-bold        { font-weight: bold; }
.t-upper       { text-transform: uppercase; letter-spacing: 2px; }
.t-center      { text-align: center; }
.t-right       { text-align: right; }

/* ─── GOLD LINE ────────────────────────────────────── */
.gold-line {
    border: none;
    border-top: 0.5px solid #C9A96E;
    width: 100%;
    margin: 3mm 0;
}

/* ─── TABLE (Scope page) ───────────────────────────── */
.scope-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 8.5pt;
}
.scope-table thead tr {
    background: #1A1A1A;
    color: #ffffff;
}
.scope-table thead th {
    padding: 3.5mm 4mm;
    text-align: left;
    font-size: 9pt;
    letter-spacing: 1px;
    font-family: Arial, Helvetica, sans-serif;
}
.scope-table thead th:first-child { width: 38%; }
.scope-table tbody tr {
    border-bottom: 0.5px solid #e8e0d5;
}
.scope-table tbody td {
    padding: 4mm 4mm;
    vertical-align: top;
    color: #1A1A1A;
    line-height: 1.6;
}
.scope-table tbody td .day-date  { font-weight: bold; font-size: 9pt; }
.scope-table tbody td .day-event { font-weight: bold; font-size: 8.5pt; margin-top: 1mm; }
.scope-table tbody td .team-text { font-size: 8pt; color: #3a2e1e; line-height: 1.7; }

/* ─── DELIVERABLES GRID ────────────────────────────── */
.deliverables-grid {
    width: 100%;
}
.deliverables-grid td {
    vertical-align: top;
    padding: 0 3mm 7mm 0;
    width: 50%;
}
.deliv-label {
    font-size: 7pt;
    color: #C9A96E;
    letter-spacing: 2px;
    text-transform: uppercase;
    font-family: Arial, Helvetica, sans-serif;
    margin-bottom: 1mm;
}
.deliv-title {
    font-size: 15pt;
    color: #1A1A1A;
    font-family: Georgia, 'Times New Roman', serif;
    line-height: 1.2;
}

/* ─── CHARGES BOX ──────────────────────────────────── */
.charges-box {
    border: 1px solid #C9A96E;
    border-radius: 2mm;
    padding: 5mm 6mm;
    width: 100%;
}
.charges-title {
    text-align: center;
    font-size: 10pt;
    font-weight: bold;
    letter-spacing: 2px;
    color: #1A1A1A;
    font-family: Arial, Helvetica, sans-serif;
    margin-bottom: 4mm;
}
.charges-row {
    width: 100%;
}
.charges-actual {
    font-size: 8pt;
    color: #9a9a9a;
    text-decoration: line-through;
    font-style: italic;
    vertical-align: middle;
}
.charges-actual-label {
    font-size: 7pt;
    color: #9a9a9a;
    display: block;
    margin-bottom: 1mm;
}
.charges-main {
    font-size: 22pt;
    font-weight: bold;
    color: #1A1A1A;
    font-family: Georgia, 'Times New Roman', serif;
    text-align: center;
    vertical-align: middle;
}
.charges-note {
    font-size: 7.5pt;
    color: #9a9a9a;
    font-style: italic;
    text-align: right;
    vertical-align: middle;
}
.savings-badge {
    text-align: center;
    margin-top: 3mm;
}
.savings-badge span {
    background: #C9A96E;
    color: #ffffff;
    font-size: 8.5pt;
    font-weight: bold;
    font-family: Arial, Helvetica, sans-serif;
    letter-spacing: 1px;
    padding: 1.5mm 5mm;
    border-radius: 1mm;
}

/* ─── BULLET POINTS ────────────────────────────────── */
.bullet-point {
    font-size: 9.5pt;
    color: #3a2e1e;
    line-height: 1.7;
    margin-bottom: 3.5mm;
    padding-left: 4mm;
    position: relative;
}
.bullet-point::before {
    content: "•";
    color: #C9A96E;
    position: absolute;
    left: 0;
    font-size: 10pt;
}

/* ─── PACKAGE ITEMS ────────────────────────────────── */
.pkg-item {
    font-size: 11pt;
    color: #3a2e1e;
    line-height: 1.9;
    padding-left: 5mm;
    position: relative;
    font-family: Georgia, serif;
}
.pkg-item::before {
    content: "—";
    color: #C9A96E;
    position: absolute;
    left: 0;
}

</style>
</head>
<body>

{{-- ═══════════════════════════════════════════════════ --}}
{{-- PAGE 1: COVER                                       --}}
{{-- ═══════════════════════════════════════════════════ --}}
<div class="page">
    @if($bg['page1'])
        <img class="page-bg" src="{{ $bg['page1'] }}" />
    @else
        <div class="page-bg-plain"></div>
    @endif

    {{-- Client Name + Date — positioned in the blank lower area --}}
    {{-- Adjust top value based on your actual image --}}
    <div class="tz t-center" style="top:218mm; left:25mm; width:160mm;">
        <div style="font-size:9pt; color:#C9A96E; letter-spacing:3px; text-transform:uppercase; font-family:Arial,sans-serif; margin-bottom:2mm;">
            PREPARED FOR
        </div>
        <div class="gold-line" style="width:80%; margin:0 auto 3mm;"></div>
        <div style="font-size:20pt; color:#1A1A1A; font-family:Georgia,serif; font-weight:bold; line-height:1.2;">
            {{ $data['cover']['client_name'] ?? 'Client Name' }}
        </div>
        <div style="font-size:10pt; color:#C9A96E; margin-top:2mm; font-family:Georgia,serif; font-style:italic;">
            {{ $data['cover']['event_date'] ?? '' }}
        </div>
        <div class="gold-line" style="width:80%; margin:3mm auto 0;"></div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════ --}}
{{-- PAGE 2: OUR PACKAGE                                 --}}
{{-- ═══════════════════════════════════════════════════ --}}
<div class="page">
    @if($bg['page2'])
        <img class="page-bg" src="{{ $bg['page2'] }}" />
    @else
        <div class="page-bg-plain"></div>
    @endif

    {{-- Package items — in the blank area between "OUR PACKAGE" and the 3 photos --}}
    {{-- Adjust top based on your actual image blank area --}}
    <div class="tz" style="top:93mm; left:20mm; width:170mm;">
        @php $pkgItems = $data['package']['items'] ?? []; @endphp
        @foreach($pkgItems as $item)
            <div class="pkg-item">{{ $item }}</div>
        @endforeach
    </div>
</div>

{{-- ═══════════════════════════════════════════════════ --}}
{{-- PAGE 3: WORK SCOPE + DELIVERABLES + CHARGES         --}}
{{-- ═══════════════════════════════════════════════════ --}}
<div class="page">
    @if($bg['page3'])
        <img class="page-bg" src="{{ $bg['page3'] }}" />
    @else
        <div class="page-bg-plain"></div>
    @endif

    {{-- "WORK SCOPE x [PACKAGE TYPE]" header --}}
    <div class="tz t-center" style="top:42.5mm; left:0; width:210mm;">
        <span style="font-size:8.5pt; color:#3a2e1e; letter-spacing:3px; text-transform:uppercase; font-family:Arial,sans-serif;">
            WORK SCOPE &nbsp;x&nbsp; {{ strtoupper($data['scope']['package_type'] ?? 'SENIOR DIRECTOR') }}
        </span>
    </div>

    {{-- Schedule Table --}}
    <div class="tz" style="top:52mm; left:10mm; width:190mm;">
        <table class="scope-table">
            <thead>
                <tr>
                    <th>DAY</th>
                    <th>TEAM DETAILS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['scope']['schedule'] ?? [] as $row)
                <tr>
                    <td>
                        <div class="day-date">{{ $row['date'] ?? '' }}</div>
                        @if(!empty($row['event']))
                            <div class="day-event">{{ $row['event'] }}</div>
                        @endif
                    </td>
                    <td>
                        <div class="team-text">{{ $row['team'] ?? '' }}</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- DELIVERABLES grid --}}
    {{-- Position below the "DELIVERABLES" heading that's baked into the image --}}
    <div class="tz" style="top:175mm; left:10mm; width:190mm;">
        @php
            $deliverables = $data['scope']['deliverables'] ?? [];
            $left  = array_values(array_filter($deliverables, fn($i) => $i % 2 === 0, ARRAY_FILTER_USE_KEY));
            $right = array_values(array_filter($deliverables, fn($i) => $i % 2 !== 0, ARRAY_FILTER_USE_KEY));
        @endphp
        <table class="deliverables-grid">
            <tr>
                <td>
                    @foreach($left as $d)
                        <div style="margin-bottom:8mm;">
                            <div class="deliv-label">{{ $d['label'] ?? '' }} ——</div>
                            <div class="deliv-title">{{ $d['title'] ?? '' }}</div>
                        </div>
                    @endforeach
                </td>
                <td>
                    @foreach($right as $d)
                        <div style="margin-bottom:8mm;">
                            <div class="deliv-label">{{ $d['label'] ?? '' }} ——</div>
                            <div class="deliv-title">{{ $d['title'] ?? '' }}</div>
                        </div>
                    @endforeach
                </td>
            </tr>
        </table>
    </div>

    {{-- CHARGES box --}}
    <div class="tz" style="top:255mm; left:10mm; width:190mm;">
        <div class="charges-box">
            <div class="charges-title">CHARGES</div>
            <table class="charges-row" style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="width:28%; vertical-align:middle;">
                        <span class="charges-actual-label">Actual Price</span>
                        <span class="charges-actual">Rs. {{ $data['scope']['actual_price'] ?? '' }}</span>
                    </td>
                    <td style="width:44%; text-align:center; vertical-align:middle;">
                        <span class="charges-main">Rs. {{ $data['scope']['offer_price'] ?? '' }}</span>
                    </td>
                    <td style="width:28%; vertical-align:middle;">
                        <span class="charges-note">{{ $data['scope']['offer_note'] ?? 'This offer is available only for this month.' }}</span>
                    </td>
                </tr>
            </table>
            <div class="savings-badge">
                <span>TOTAL SAVINGS – RS {{ $data['scope']['savings'] ?? '' }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════ --}}
{{-- PAGE 4: WHY CHOOSE US                               --}}
{{-- ═══════════════════════════════════════════════════ --}}
<div class="page">
    @if($bg['page4'])
        <img class="page-bg" src="{{ $bg['page4'] }}" />
    @else
        <div class="page-bg-plain"></div>
    @endif

    {{-- Why Choose Us points — left half only --}}
    {{-- Positioned below "Why Choose Us" heading baked in the image --}}
    <div class="tz" style="top:88mm; left:12mm; width:95mm;">
        @foreach($data['why_us']['points'] ?? [] as $point)
            <div class="bullet-point">{{ $point }}</div>
        @endforeach
    </div>
</div>

{{-- ═══════════════════════════════════════════════════ --}}
{{-- PAGE 5: BACK COVER (fully static)                   --}}
{{-- ═══════════════════════════════════════════════════ --}}
<div class="page">
    @if($bg['page5'])
        <img class="page-bg" src="{{ $bg['page5'] }}" />
    @else
        <div class="page-bg-plain" style="background:#1A1A1A;"></div>
    @endif
    {{-- This page is fully fixed — no dynamic text --}}
</div>

</body>
</html>
