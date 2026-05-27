<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
@page { margin: 0; size: A4 portrait; }
body { margin: 0; padding: 0; font-family: Georgia, 'Times New Roman', serif; background: #FAF8F5; }

.page {
    position: relative; width: 210mm; height: 297mm;
    overflow: hidden; page-break-after: always; page-break-inside: avoid;
}
.page:last-child { page-break-after: auto; }

.page-bg { position: absolute; top:0; left:0; width:210mm; height:297mm; object-fit:cover; z-index:1; }
.page-bg-plain { position: absolute; top:0; left:0; width:210mm; height:297mm; background:#FAF8F5; z-index:1; }
.tz { position: absolute; z-index: 10; }

.t-gold   { color: #C9A96E; }
.t-dark   { color: #1A1A1A; }
.t-center { text-align: center; }
.gold-line { border: none; border-top: 0.5px solid #C9A96E; width: 100%; margin: 3mm 0; }

.scope-table { width: 100%; border-collapse: collapse; }
.scope-table thead tr { background: #1A1A1A; color: #fff; }
.scope-table thead th { padding: 4.5mm 5mm; text-align:left; font-size:12pt; letter-spacing:2px; font-family:Arial,sans-serif; font-weight:bold; }
.scope-table thead th:first-child { width: 40%; }
.scope-table tbody tr { border-bottom: 0.5px solid #e0d8cc; }
.scope-table tbody td { padding: 5mm; vertical-align:top; color:#1A1A1A; line-height:1.6; }
.day-date  { font-weight:bold; font-size:12pt; color:#1A1A1A; }
.day-event { font-size:11pt; color:#5a4a38; margin-top:1.5mm; }
.team-text { font-size:11pt; color:#3a2e1e; line-height:1.8; }

.deliverables-grid { width:100%; border-collapse:collapse; }
.deliverables-grid td { vertical-align:top; padding:0 5mm 5mm 0; width:50%; }
.deliv-label { font-size:9pt; color:#C9A96E; letter-spacing:2px; text-transform:uppercase; font-family:Arial,sans-serif; margin-bottom:1.5mm; }
.deliv-title { font-size:13.5pt; color:#1A1A1A; font-family:Georgia,serif; line-height:1.25; }

.bullet-point { font-size:12pt; color:#3a2e1e; line-height:1.8; margin-bottom:4.5mm; padding-left:6mm; position:relative; }
.bullet-point::before { content:"•"; color:#C9A96E; position:absolute; left:0; font-size:13pt; }
</style>
</head>
<body>

@php
/** Return inline style string for a text zone from the layout. */
if (!function_exists('acp_tz')) {
    function acp_tz(array $layout, string $slot, string $key, string $extra = ''): string {
        $z    = $layout[$slot][$key] ?? [];
        $top  = isset($z['top'])   ? $z['top']  .'mm' : '0';
        $left = isset($z['left'])  ? $z['left'] .'mm' : '0';
        $w    = isset($z['width']) ? $z['width'].'mm' : 'auto';
        return "top:{$top}; left:{$left}; width:{$w};" . ($extra ? " {$extra}" : '');
    }
}
@endphp

{{-- ═══════════════════════════════════════════════════
     COVER
     ═══════════════════════════════════════════════════ --}}
<div class="page">
    @if(!empty($bg['cover']))
        <img class="page-bg" src="{{ $bg['cover'] }}" />
    @else
        <div class="page-bg-plain"></div>
    @endif

    <div class="tz t-center" style="{{ acp_tz($layout,'cover','client_name') }}">
        <div style="font-size:10pt;color:#C9A96E;letter-spacing:4px;text-transform:uppercase;font-family:Arial,sans-serif;margin-bottom:2mm;">PREPARED FOR</div>
        <div class="gold-line" style="width:80%;margin:0 auto 3mm;"></div>
        <div style="font-size:24pt;color:#1A1A1A;font-family:Georgia,serif;font-weight:bold;line-height:1.2;">
            {{ $data['cover']['client_name'] ?? 'Client Name' }}
        </div>
    </div>

    <div class="tz t-center" style="{{ acp_tz($layout,'cover','event_date') }}">
        <div style="font-size:12pt;color:#C9A96E;font-family:Georgia,serif;font-style:italic;">
            {{ $data['cover']['event_date'] ?? '' }}
        </div>
        <div class="gold-line" style="width:80%;margin:3mm auto 0;"></div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════
     OUR PACKAGE
     ═══════════════════════════════════════════════════ --}}
<div class="page">
    @if(!empty($bg['package']))
        <img class="page-bg" src="{{ $bg['package'] }}" />
    @else
        <div class="page-bg-plain"></div>
    @endif

    <div class="tz t-center" style="{{ acp_tz($layout,'package','package_name') }}">
        <div style="font-size:34pt;font-family:Georgia,serif;font-style:italic;color:#1A1A1A;line-height:1.15;">
            {{ $data['package']['name'] ?? 'Royal Experience' }}
        </div>
    </div>

    <div class="tz t-center" style="{{ acp_tz($layout,'package','package_desc') }}">
        <div style="font-size:11.5pt;font-family:Georgia,serif;color:#7a7260;line-height:1.7;">
            {{ $data['package']['description'] ?? '' }}
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════
     WORK SCOPE — Schedule Table
     ═══════════════════════════════════════════════════ --}}
<div class="page">
    @php
        // Single wrapper positioned at scope_header top, using scope_table's left/width
        $hTop   = (float)($layout['scope_schedule']['scope_header']['top'] ?? 42);
        $tLeft  = (float)($layout['scope_schedule']['scope_table']['left']  ?? 10);
        $tWidth = (float)($layout['scope_schedule']['scope_table']['width'] ?? 190);
        $boxTop = max(0, $hTop - 5); // 5mm padding above header text
    @endphp

    {{-- Background image (full page — visible above AND below the content box) --}}
    @if(!empty($bg['scope_schedule']))
        <img class="page-bg" src="{{ $bg['scope_schedule'] }}" />
    @else
        <div class="page-bg-plain"></div>
    @endif

    {{--
        Single content box: wraps both the header label + table.
        Height is auto → shrinks to exactly fit the content (2 rows or 6 rows).
        Background image shows above and below this box.
    --}}
    <div style="
        position:absolute;
        top:{{ $boxTop }}mm;
        left:{{ $tLeft }}mm;
        width:{{ $tWidth }}mm;
        z-index:10;
        background:rgba(250, 248, 245, 0.82);
        border-top: 2px solid #C9A96E;
        border-bottom: 1px solid #C9A96E;
        padding-bottom: 3mm;
    ">
        {{-- Header label inside the box --}}
        <div style="text-align:center; padding: 4mm 5mm 3mm;">
            <span style="font-size:11pt;color:#3a2e1e;letter-spacing:4px;text-transform:uppercase;font-family:Arial,sans-serif;">
                WORK SCOPE &nbsp;&times;&nbsp; {{ strtoupper($data['scope']['package_type'] ?? 'SENIOR DIRECTOR') }}
            </span>
        </div>

        {{-- Schedule table --}}
        <table class="scope-table">
            <thead><tr><th>DAY</th><th>TEAM DETAILS</th></tr></thead>
            <tbody>
                @foreach($data['scope']['schedule'] ?? [] as $row)
                <tr>
                    <td>
                        <div class="day-date">{{ $row['date'] ?? '' }}</div>
                        @if(!empty($row['event']))<div class="day-event">{{ $row['event'] }}</div>@endif
                    </td>
                    <td><div class="team-text">{{ $row['team'] ?? '' }}</div></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════
     DELIVERABLES + CHARGES
     (own page — always clean and consistent,
      no dependency on how many schedule rows exist)
     ═══════════════════════════════════════════════════ --}}
<div class="page">
    @if(!empty($bg['scope_deliverables']))
        <img class="page-bg" src="{{ $bg['scope_deliverables'] }}" />
    @else
        <div class="page-bg-plain"></div>
    @endif

    <div class="tz" style="{{ acp_tz($layout,'scope_deliverables','deliverables') }}">
        <div class="t-center" style="font-size:9pt;color:#C9A96E;letter-spacing:4px;text-transform:uppercase;font-family:Arial,sans-serif;margin-bottom:2mm;">DELIVERABLES</div>
        <hr class="gold-line" style="margin-bottom:0;" />
        @php
            $deliverables = $data['scope']['deliverables'] ?? [];
            $left  = array_values(array_filter($deliverables, fn($i) => $i%2===0, ARRAY_FILTER_USE_KEY));
            $right = array_values(array_filter($deliverables, fn($i) => $i%2!==0, ARRAY_FILTER_USE_KEY));
        @endphp
        <table class="deliverables-grid" style="margin-top:5mm;">
            <tr>
                <td>@foreach($left as $d)<div style="margin-bottom:5mm;"><div class="deliv-label">{{ $d['label']??'' }} ——</div><div class="deliv-title">{{ $d['title']??'' }}</div></div>@endforeach</td>
                <td>@foreach($right as $d)<div style="margin-bottom:5mm;"><div class="deliv-label">{{ $d['label']??'' }} ——</div><div class="deliv-title">{{ $d['title']??'' }}</div></div>@endforeach</td>
            </tr>
        </table>
    </div>

    <div class="tz t-center" style="{{ acp_tz($layout,'scope_deliverables','charges') }}">
        <div style="font-size:9pt;color:#C9A96E;letter-spacing:4px;text-transform:uppercase;font-family:Arial,sans-serif;margin-bottom:2mm;">CHARGES</div>
        <hr class="gold-line" style="margin-bottom:3mm;" />
        <table style="width:100%;border-collapse:collapse;">
            <tr>
                <td style="width:30%;text-align:left;vertical-align:middle;">
                    <div style="font-size:8.5pt;color:#aaa;font-style:italic;font-family:Arial,sans-serif;">Actual Price</div>
                    <div style="font-size:11pt;color:#bbb;text-decoration:line-through;font-style:italic;">{{ $data['scope']['actual_price']??'' }}</div>
                </td>
                <td style="width:40%;text-align:center;vertical-align:middle;">
                    <div style="font-size:26pt;font-weight:bold;color:#1A1A1A;font-family:Georgia,serif;line-height:1;">{{ $data['scope']['offer_price']??'' }}</div>
                </td>
                <td style="width:30%;text-align:right;vertical-align:middle;">
                    <div style="font-size:8.5pt;color:#aaa;font-style:italic;font-family:Arial,sans-serif;line-height:1.5;">{{ $data['scope']['offer_note']??'This offer is available only for this month.' }}</div>
                </td>
            </tr>
        </table>
        <hr class="gold-line" style="margin-top:3mm;margin-bottom:2mm;" />
        <div style="font-size:10pt;font-family:Arial,sans-serif;letter-spacing:2px;color:#C9A96E;font-weight:bold;text-transform:uppercase;">
            TOTAL SAVINGS — {{ $data['scope']['savings']??'' }}
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════
     WHY CHOOSE US
     ═══════════════════════════════════════════════════ --}}
<div class="page">
    @if(!empty($bg['why_us']))
        <img class="page-bg" src="{{ $bg['why_us'] }}" />
    @else
        <div class="page-bg-plain"></div>
    @endif

    <div class="tz" style="{{ acp_tz($layout,'why_us','why_us_points') }}">
        @foreach($data['why_us']['points'] ?? [] as $point)
            <div class="bullet-point">{{ $point }}</div>
        @endforeach
    </div>
</div>

{{-- ═══════════════════════════════════════════════════
     BACK COVER (background only)
     ═══════════════════════════════════════════════════ --}}
<div class="page">
    @if(!empty($bg['back']))
        <img class="page-bg" src="{{ $bg['back'] }}" />
    @else
        <div class="page-bg-plain" style="background:#1A1A1A;"></div>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════
     EXTRA PAGES (background only — reorderable)
     ═══════════════════════════════════════════════════ --}}
@foreach($bg as $key => $src)
    @if(str_starts_with($key, 'extra_') && $src)
    <div class="page">
        <img class="page-bg" src="{{ $src }}" />
    </div>
    @endif
@endforeach

</body>
</html>
