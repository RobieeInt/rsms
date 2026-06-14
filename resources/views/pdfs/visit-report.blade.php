<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

html, body {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 10px;
    color: #1a1a1a;
    background: #1a1a1a;
    margin: 0; padding: 0;
}

.page-bg {
    position: fixed;
    top: -60px; left: -60px; right: -60px; bottom: -60px;
    background: #1a1a1a;
    z-index: -1;
}

.outer { padding: 22px 20px; }

/* ── CARD ── */
.card {
    background: #F0EBE0;
    border-radius: 20px;
    overflow: hidden;
}

/* ── HEADER ── */
.hdr { width: 100%; border-collapse: collapse; }
.hdr-left {
    background: #F0EBE0;
    padding: 18px 24px;
    vertical-align: middle;
    width: 56%;
    border-radius: 20px 0 0 0;
}
.hdr-right {
    background: #1a1a1a;
    padding: 18px 28px 22px 20px;
    vertical-align: bottom;
    width: 44%;
    text-align: right;
    border-radius: 0 20px 0 32px;
}
.logo-tbl { border-collapse: collapse; }
.logo-tbl td { vertical-align: middle; }
.logo-img  { height: 40px; width: auto; }
.brand-name { font-size: 18px; font-weight: 700; color: #1a1a1a; padding-left: 9px; white-space: nowrap; }
.report-word { font-size: 32px; font-weight: 900; color: #F0EBE0; letter-spacing: -1px; line-height: 1; }
.report-sub  { font-size: 10px; color: #b0a898; margin-top: 3px; text-align: right; }

/* ── META INFO ── */
.meta { padding: 16px 24px 0; }
.meta-tbl { width: 100%; border-collapse: collapse; }
.meta-box { padding: 0 12px 0 0; vertical-align: top; }
.meta-label { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #888; margin-bottom: 3px; }
.meta-value { font-size: 11px; font-weight: 700; color: #1a1a1a; }
.meta-value-sm { font-size: 10px; color: #1a1a1a; }

/* ── DIVIDER ── */
.divider { border: 0; border-top: 1px solid #D8D0C4; margin: 14px 24px; }

/* ── SECTION ── */
.section { padding: 0 24px 14px; }
.section-title {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #1a1a1a;
    margin-bottom: 8px;
}

/* ── SUMMARY BOX ── */
.summary-box {
    background: rgba(0,0,0,0.05);
    border-radius: 6px;
    padding: 10px 12px;
    font-size: 10px;
    color: #333;
    line-height: 1.65;
}

/* ── CHECKLIST TABLE ── */
.chk-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
.chk-table thead tr { background: #1a1a1a; }
.chk-table th {
    padding: 7px 10px;
    font-size: 8.5px;
    font-weight: 700;
    color: #F0EBE0;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    text-align: left;
}
.chk-table th.c { text-align: center; }
.chk-table td {
    padding: 7px 10px;
    font-size: 9.5px;
    border-bottom: 1px solid #D8D0C4;
    vertical-align: top;
    color: #1a1a1a;
}
.chk-table td.c { text-align: center; }
.chk-table tbody tr:last-child td { border-bottom: none; }

.ok   { color: #059669; font-weight: 700; }
.fail { color: #dc2626; font-weight: 700; }
.na   { color: #999; }

/* ── FINDINGS ── */
.sev-critical { color: #dc2626; font-weight: 700; text-transform: uppercase; }
.sev-high     { color: #ea580c; font-weight: 700; text-transform: uppercase; }
.sev-medium   { color: #ca8a04; font-weight: 700; text-transform: uppercase; }
.sev-low      { color: #2563eb; font-weight: 700; text-transform: uppercase; }

/* ── SIGNATURES ── */
.sig-section { padding: 10px 24px 22px; }
.sig-tbl { width: 100%; border-collapse: collapse; }
.sig-box {
    border: 1px solid #C8C0B4;
    border-radius: 8px;
    background: #fff;
    height: 72px;
    text-align: center;
}
.sig-box img { max-height: 62px; max-width: 95%; }
.sig-name  { font-size: 10px; font-weight: 700; color: #1a1a1a; text-align: center; margin-top: 5px; }
.sig-role  { font-size: 8.5px; color: #888; text-align: center; margin-top: 2px; }

/* ── FOOTER ── */
.rpt-footer {
    padding: 12px 24px 18px;
    border-top: 1px solid #D8D0C4;
    font-size: 8.5px;
    color: #888;
    text-align: center;
    line-height: 1.7;
}

/* Page breaks */
.avoid-break { page-break-inside: avoid; }
</style>
</head>
<body>

<div class="page-bg"></div>

<div class="outer">
<div class="card">

    {{-- ── HEADER ── --}}
    <table class="hdr">
        <tr>
            <td class="hdr-left">
                <table class="logo-tbl">
                    <tr>
                        @if($company->logo)
                        <td><img src="{{ public_path('storage/' . $company->logo) }}" class="logo-img" alt=""></td>
                        @endif
                        <td class="brand-name">{{ $company->company_name }}</td>
                    </tr>
                </table>
            </td>
            <td class="hdr-right">
                <div class="report-word">VISIT REPORT</div>
                <div class="report-sub">IT Maintenance Visit</div>
            </td>
        </tr>
    </table>

    {{-- ── META INFO ── --}}
    <div class="meta">
        <table class="meta-tbl">
            <tr>
                <td class="meta-box" style="width:22%;">
                    <div class="meta-label">No. Laporan</div>
                    <div class="meta-value" style="font-size:10px;">{{ $report->report_number }}</div>
                </td>
                <td class="meta-box" style="width:18%;">
                    <div class="meta-label">Tanggal Kunjungan</div>
                    <div class="meta-value-sm">{{ $report->schedule->visit_date->locale('id')->translatedFormat('d F Y') }}</div>
                </td>
                <td class="meta-box" style="width:22%;">
                    <div class="meta-label">Klien</div>
                    <div class="meta-value-sm">{{ $report->client->company_name }}</div>
                </td>
                <td class="meta-box" style="width:20%;">
                    <div class="meta-label">PIC</div>
                    <div class="meta-value-sm">{{ $report->client->pic_name ?? '-' }}</div>
                </td>
                <td class="meta-box" style="width:18%; padding-right:0;">
                    <div class="meta-label">Teknisi</div>
                    <div class="meta-value-sm">{{ $report->technician->name }}</div>
                </td>
            </tr>
        </table>
    </div>

    <hr class="divider">

    {{-- ── SUMMARY ── --}}
    @if($report->summary)
    <div class="section avoid-break">
        <div class="section-title">Ringkasan</div>
        <div class="summary-box">{{ $report->summary }}</div>
    </div>
    @endif

    {{-- ── ASSET CHECKLISTS ── --}}
    @foreach($report->assetChecklists as $checklist)
    @php $resolvedItems = $checklist->getResolvedItems(); @endphp
    @if(count($resolvedItems))
    <div class="section avoid-break">
        <div class="section-title">Aset: {{ $checklist->asset->asset_name }} &mdash; {{ $checklist->asset->asset_code }}</div>
        <table class="chk-table">
            <thead>
                <tr>
                    <th style="width:42%;">Item Pemeriksaan</th>
                    <th class="c" style="width:14%;">Hasil</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resolvedItems as $item)
                <tr>
                    <td>{{ $item['label'] }}</td>
                    <td class="c">
                        <span class="{{ $item['result'] === 'passed' ? 'ok' : ($item['result'] === 'failed' ? 'fail' : 'na') }}">
                            {{ $item['result'] === 'passed' ? 'OK' : ($item['result'] === 'failed' ? 'GAGAL' : 'N/A') }}
                        </span>
                    </td>
                    <td style="color:#555;">{{ $item['notes'] }}</td>
                </tr>
                @endforeach
                @if($checklist->general_notes)
                <tr>
                    <td colspan="3" style="color:#555; font-style:italic; border-top: 1px solid #D8D0C4;">
                        <strong>Catatan:</strong> {{ $checklist->general_notes }}
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @endif
    @endforeach

    {{-- ── NETWORK CHECKLIST ── --}}
    @if($report->networkChecklist)
    <div class="section avoid-break">
        <div class="section-title">Jaringan</div>
        <table class="chk-table">
            <thead>
                <tr>
                    <th style="width:42%;">Item Pemeriksaan</th>
                    <th class="c" style="width:14%;">Hasil</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach([
                    'internet_connectivity' => 'Koneksi Internet',
                    'speed_test'            => 'Speed Test',
                    'router_check'          => 'Router Check',
                    'lan_cable_check'       => 'Kabel LAN',
                    'ip_conflict_check'     => 'IP Conflict',
                ] as $field => $label)
                @php $v = $report->networkChecklist->$field; @endphp
                @if($v && $v !== 'na')
                <tr>
                    <td>{{ $label }}</td>
                    <td class="c">
                        <span class="{{ $v === 'passed' ? 'ok' : ($v === 'failed' ? 'fail' : 'na') }}">
                            {{ $v === 'passed' ? 'OK' : ($v === 'failed' ? 'GAGAL' : 'N/A') }}
                        </span>
                    </td>
                    <td style="color:#555;">{{ $report->networkChecklist->{$field.'_notes'} ?? '' }}</td>
                </tr>
                @endif
                @endforeach
                @if($report->networkChecklist->download_speed)
                <tr>
                    <td style="color:#555;">Kecepatan Internet</td>
                    <td class="c" style="color:#1a1a1a; font-weight:700;">—</td>
                    <td style="color:#555;">↓ {{ $report->networkChecklist->download_speed }} Mbps &nbsp;/&nbsp; ↑ {{ $report->networkChecklist->upload_speed }} Mbps</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @endif

    {{-- ── FINDINGS ── --}}
    @if($report->findings->count())
    <div class="section avoid-break">
        <div class="section-title">Temuan</div>
        <table class="chk-table">
            <thead>
                <tr>
                    <th style="width:28%;">Judul</th>
                    <th style="width:13%;">Tingkat</th>
                    <th>Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report->findings as $f)
                <tr>
                    <td style="font-weight:600;">{{ $f->title }}</td>
                    <td>
                        <span class="sev-{{ $f->severity }}">{{ $f->severity }}</span>
                    </td>
                    <td style="color:#555;">{{ $f->description }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ── OVERALL NOTES ── --}}
    @if($report->overall_notes)
    <div class="section avoid-break">
        <div class="section-title">Catatan Keseluruhan</div>
        <div class="summary-box">{{ $report->overall_notes }}</div>
    </div>
    @endif

    {{-- ── SIGNATURES ── --}}
    <div class="sig-section avoid-break">
        <table class="sig-tbl">
            <tr>
                <td style="width:46%; vertical-align:top; padding-right:16px;">
                    <div class="section-title" style="margin-bottom:6px;">Tanda Tangan Teknisi</div>
                    <div class="sig-box">
                        @if($report->technician_signature)
                        <img src="{{ $report->technician_signature }}" alt="">
                        @endif
                    </div>
                    <div class="sig-name">{{ $report->technician->name }}</div>
                    <div class="sig-role">Teknisi</div>
                </td>
                <td style="width:8%;"></td>
                <td style="width:46%; vertical-align:top; padding-left:16px;">
                    <div class="section-title" style="margin-bottom:6px;">Tanda Tangan Klien</div>
                    <div class="sig-box">
                        @if($report->client_signature)
                        <img src="{{ $report->client_signature }}" alt="">
                        @endif
                    </div>
                    <div class="sig-name">{{ $report->client_signed_by ?: ($report->client->pic_name ?? '-') }}</div>
                    <div class="sig-role">Perwakilan Klien</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── FOOTER ── --}}
    <div class="rpt-footer">
        {{ $company->company_name }}
        @if($company->phone) &bull; {{ $company->phone }}@endif
        @if($company->email) &bull; {{ $company->email }}@endif
        <br>Dicetak: {{ now()->locale('id')->translatedFormat('d F Y, H:i') }}
    </div>

</div>{{-- /card --}}
</div>{{-- /outer --}}

</body>
</html>
