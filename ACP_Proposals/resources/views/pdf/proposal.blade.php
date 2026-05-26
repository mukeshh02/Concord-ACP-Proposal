<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
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

/* PAGE WRAPPER */
.page {
    position: relative;
    width: 210mm;
    height: 297mm;
    overflow: hidden;
    page-break-after: always;
    page-break-inside: avoid;
}
.page:last-child { page-break-after: auto; }

/* BACKGROUND IMAGE */
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

/* TEXT ZONE BASE */
.tz {
    position: absolute;
    z-index: 10;
}

/* TYPOGRAPHY UTILITIES */
.t-gold   { color: #C9A96E; }
.t-dark   { color: #1A1A1A; }
.t-white  { color: #ffffff; }
.t-center { text-align: center; }
.t-upper  { text-transform: uppercase; letter-spacing: 2px; }
.t-bold   { font-weight: bold; }

/* GOLD DIVIDER */
.gold-line {
    border: none;
    border-top: 0.5px solid #C9A96E;
    width: 100%;
    margin: 3mm 0;
}

/* ====================================================
   TABLE — Work Scope (Page 3)
   ==================================================== */
.scope-table {
    width: 100%;
    border-collapse: collapse;
}
.scope-table thead tr {
    background: #1A1A1A;
    color: #ffffff;
}
.scope-table thead th {
    padding: 4.5mm 5mm;
    text-align: left;
    font-size: 12pt;
    letter-spacing: 2px;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: bold;
}
.scope-table thead th:first-child { width: 40%; }
.scope-table tbody tr {
    border-bottom: 0.5px solid #e0d8cc;
}
.scope-table tbody td {
    padding: 5mm 5mm;
    vertical-align: top;
    color: #1A1A1A;
    line-height: 1.6;
}
.day-date  {
    font-weight: bold;
    font-size: 12pt;
    color: #1A1A1A;
}
.day-event {
    font-size: 11pt;
    color: #5a4a38;
    margin-top: 1.5mm;
}
.team-text {
    font-size: 11pt;
    color: #3a2e1e;
    font-weight: normal;
    line-height: 1.8;
}

/* ====================================================
   DELIVERABLES (Page 3)
   ==================================================== */
.deliverables-grid { width: 100%; border-collapse: collapse; }
.deliverables-grid td {
    vertical-align: top;
    padding: 0 5mm 5mm 0;
    width: 50%;
}
.deliv-label {
    font-size: 9pt;
    color: #C9A96E;
    letter-spacing: 2px;
    text-transform: uppercase;
    font-family: Arial, Helvetica, sans-serif;
    margin-bottom: 1.5mm;
}
.deliv-title {
    font-size: 13.5pt;
    color: #1A1A1A;
    font-family: Georgia, 'Times New Roman', serif;
    line-height: 1.25;
}

/* ====================================================
   CHARGES BOX (Page 3)
   ==================================================== */
.charges-box {
    border: 1px solid #C9A96E;
    border-radius: 2mm;
    padding: 4mm 7mm;
    width: 100%;
}
.charges-title {
    text-align: center;
    font-size: 12pt;
    font-weight: bold;
    letter-spacing: 3px;
    color: #1A1A1A;
    font-family: Arial, Helvetica, sans-serif;
    margin-bottom: 3mm;
    text-transform: uppercase;
}
.charges-actual-label {
    font-size: 9pt;
    color: #9a9a9a;
    display: block;
    margin-bottom: 1.5mm;
}
.charges-actual {
    font-size: 11pt;
    color: #9a9a9a;
    text-decoration: line-through;
    font-style: italic;
}
.charges-main {
    font-size: 22pt;
    font-weight: bold;
    color: #1A1A1A;
    font-family: Georgia, 'Times New Roman', serif;
    text-align: center;
    line-height: 1.1;
}
.charges-note {
    font-size: 9.5pt;
    color: #9a9a9a;
    font-style: italic;
    text-align: right;
}
.savings-badge {
    text-align: center;
    margin-top: 3mm;
}
.savings-badge span {
    background: #C9A96E;
    color: #ffffff;
    font-size: 10.5pt;
    font-weight: bold;
    font-family: Arial, Helvetica, sans-serif;
    letter-spacing: 1.5px;
    padding: 1.5mm 7mm;
    border-radius: 1mm;
}

/* ====================================================
   BULLET POINTS — Why Choose Us (Page 4)
   ==================================================== */
.bullet-point {
    font-size: 12pt;
    color: #3a2e1e;
    line-height: 1.8;
    margin-bottom: 4.5mm;
    padding-left: 6mm;
    position: relative;
}
.bullet-point::before {
    content: "•";
    color: #C9A96E;
    position: absolute;
    left: 0;
    font-size: 13pt;
}

/* ====================================================
   PACKAGE ITEMS (Page 2)
   ==================================================== */
.pkg-item {
    font-size: 13.5pt;
    color: #3a2e1e;
    line-height: 2.1;
    padding-left: 7mm;
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

{{-- ======================================================= --}}
{{-- PAGE 1: COVER                                           --}}
{{-- ======================================================= --}}
<div class="page">
    @if($bg['page1'])
        <img class="page-bg" src="{{ $bg['page1'] }}" />
    @else
        <div class="page-bg-plain"></div>
    @endif

    {{-- Client name + event date — centred in the blank text zone at bottom of cover --}}
    <div class="tz t-center" style="top:222mm; left:25mm; width:160mm;">
        <div style="font-size:10pt; color:#C9A96E; letter-spacing:4px; text-transform:uppercase; font-family:Arial,sans-serif; margin-bottom:2mm;">
            PREPARED FOR
        </div>
        <div class="gold-line" style="width:80%; margin:0 auto 3mm;"></div>
        <div style="font-size:24pt; color:#1A1A1A; font-family:Georgia,serif; font-weight:bold; line-height:1.2;">
            {{ $data['cover']['client_name'] ?? 'Client Name' }}
        </div>
        <div style="font-size:12pt; color:#C9A96E; margin-top:2mm; font-family:Georgia,serif; font-style:italic;">
            {{ $data['cover']['event_date'] ?? '' }}
        </div>
        <div class="gold-line" style="width:80%; margin:3mm auto 0;"></div>
    </div>
</div>

{{-- ======================================================= --}}
{{-- PAGE 2: OUR PACKAGE                                     --}}
{{-- ======================================================= --}}
<div class="page">
    @if($bg['page2'])
        <img class="page-bg" src="{{ $bg['page2'] }}" />
    @else
        <div class="page-bg-plain"></div>
    @endif

    {{-- Package name (large italic) + description
         Blank zone: below the "OUR PACKAGE" divider and above 3 bottom photos --}}
    <div class="tz t-center" style="top:135mm; left:20mm; width:170mm;">
        {{-- Package Name — large italic serif --}}
        <div style="font-size:34pt; font-family:Georgia,'Times New Roman',serif; font-style:italic; color:#1A1A1A; line-height:1.15; margin-bottom:5mm;">
            {{ $data['package']['name'] ?? 'Royal Experience' }}
        </div>
        {{-- Description paragraph --}}
        <div style="font-size:11.5pt; font-family:Georgia,'Times New Roman',serif; color:#7a7260; line-height:1.7; max-width:130mm; margin:0 auto;">
            {{ $data['package']['description'] ?? '' }}
        </div>
    </div>
</div>

{{-- ======================================================= --}}
{{-- PAGE 3: WORK SCOPE + DELIVERABLES + CHARGES             --}}
{{-- ======================================================= --}}
<div class="page">
    @if($bg['page3'])
        <img class="page-bg" src="{{ $bg['page3'] }}" />
    @else
        <div class="page-bg-plain"></div>
    @endif

    {{-- Header: WORK SCOPE x PACKAGE TYPE --}}
    <div class="tz t-center" style="top:42mm; left:0; width:210mm;">
        <span style="font-size:11pt; color:#3a2e1e; letter-spacing:4px; text-transform:uppercase; font-family:Arial,sans-serif;">
            WORK SCOPE &nbsp;&times;&nbsp; {{ strtoupper($data['scope']['package_type'] ?? 'SENIOR DIRECTOR') }}
        </span>
    </div>

    {{-- Schedule Table --}}
    <div class="tz" style="top:51mm; left:10mm; width:190mm;">
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

    {{-- DELIVERABLES — clean label style (same as CHARGES) --}}
    <div class="tz t-center" style="top:163mm; left:15mm; width:180mm;">
        <div style="font-size:9pt; color:#C9A96E; letter-spacing:4px; text-transform:uppercase; font-family:Arial,sans-serif; margin-bottom:2mm;">
            DELIVERABLES
        </div>
        <hr class="gold-line" style="margin-bottom:0;" />
    </div>

    {{-- DELIVERABLES grid --}}
    <div class="tz" style="top:175mm; left:15mm; width:180mm;">
        @php
            $deliverables = $data['scope']['deliverables'] ?? [];
            $left  = array_values(array_filter($deliverables, fn($i) => $i % 2 === 0, ARRAY_FILTER_USE_KEY));
            $right = array_values(array_filter($deliverables, fn($i) => $i % 2 !== 0, ARRAY_FILTER_USE_KEY));
        @endphp
        <table class="deliverables-grid">
            <tr>
                <td>
                    @foreach($left as $d)
                        <div style="margin-bottom:5mm;">
                            <div class="deliv-label">{{ $d['label'] ?? '' }} ——</div>
                            <div class="deliv-title">{{ $d['title'] ?? '' }}</div>
                        </div>
                    @endforeach
                </td>
                <td>
                    @foreach($right as $d)
                        <div style="margin-bottom:5mm;">
                            <div class="deliv-label">{{ $d['label'] ?? '' }} ——</div>
                            <div class="deliv-title">{{ $d['title'] ?? '' }}</div>
                        </div>
                    @endforeach
                </td>
            </tr>
        </table>
    </div>

    {{-- CHARGES — no box, clean typography --}}
    <div class="tz t-center" style="top:252mm; left:15mm; width:180mm;">

        {{-- Section label --}}
        <div style="font-size:9pt; color:#C9A96E; letter-spacing:4px; text-transform:uppercase; font-family:Arial,sans-serif; margin-bottom:2mm;">
            CHARGES
        </div>
        <hr class="gold-line" style="margin-bottom:3mm;" />

        {{-- Price row --}}
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="width:30%; text-align:left; vertical-align:middle;">
                    <div style="font-size:8.5pt; color:#aaa; font-style:italic; font-family:Arial,sans-serif;">Actual Price</div>
                    <div style="font-size:11pt; color:#bbb; text-decoration:line-through; font-style:italic;">{{ $data['scope']['actual_price'] ?? '' }}</div>
                </td>
                <td style="width:40%; text-align:center; vertical-align:middle;">
                    <div style="font-size:26pt; font-weight:bold; color:#1A1A1A; font-family:Georgia,serif; line-height:1;">{{ $data['scope']['offer_price'] ?? '' }}</div>
                </td>
                <td style="width:30%; text-align:right; vertical-align:middle;">
                    <div style="font-size:8.5pt; color:#aaa; font-style:italic; font-family:Arial,sans-serif; line-height:1.5;">{{ $data['scope']['offer_note'] ?? 'This offer is available only for this month.' }}</div>
                </td>
            </tr>
        </table>

        <hr class="gold-line" style="margin-top:3mm; margin-bottom:2mm;" />

        {{-- Savings line --}}
        <div style="font-size:10pt; font-family:Arial,sans-serif; letter-spacing:2px; color:#C9A96E; font-weight:bold; text-transform:uppercase;">
            TOTAL SAVINGS — {{ $data['scope']['savings'] ?? '' }}
        </div>

    </div>
</div>

{{-- ======================================================= --}}
{{-- PAGE 4: WHY CHOOSE US                                   --}}
{{-- ======================================================= --}}
<div class="page">
    @if($bg['page4'])
        <img class="page-bg" src="{{ $bg['page4'] }}" />
    @else
        <div class="page-bg-plain"></div>
    @endif

    {{-- Bullet points — left side of page, BELOW the "Why Choose Us" heading in the image
         Heading baked in image at ~35-40% (~105-118mm); bullets start just below at 130mm --}}
    <div class="tz" style="top:130mm; left:12mm; width:95mm;">
        @foreach($data['why_us']['points'] ?? [] as $point)
            <div class="bullet-point">{{ $point }}</div>
        @endforeach
    </div>
</div>

{{-- ======================================================= --}}
{{-- PAGE 5: BACK COVER (fully static)                       --}}
{{-- ======================================================= --}}
<div class="page">
    @if($bg['page5'])
        <img class="page-bg" src="{{ $bg['page5'] }}" />
    @else
        <div class="page-bg-plain" style="background:#1A1A1A;"></div>
    @endif
</div>

</body>
</html>
