<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
html, body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1a1a; background: #1a1a1a; margin: 0; padding: 0; }
.page-bg { position: fixed; top: -60px; left: -60px; right: -60px; bottom: -60px; background: #1a1a1a; z-index: -1; }
.outer { padding: 26px 22px; }
.card { background: #F0EBE0; border-radius: 20px; overflow: hidden; }
.hdr { width: 100%; border-collapse: collapse; }
.hdr-left  { background: #F0EBE0; padding: 20px 24px; vertical-align: middle; width: 56%; border-radius: 20px 0 0 0; }
.hdr-right { background: #1a1a1a; padding: 20px 28px 24px 20px; vertical-align: bottom; width: 44%; text-align: right; border-radius: 0 20px 0 32px; }
.logo-tbl td { vertical-align: middle; }
.logo-img   { height: 46px; width: auto; }
.brand-name { font-size: 20px; font-weight: 700; color: #1a1a1a; padding-left: 10px; white-space: nowrap; }
.quo-word   { font-size: 46px; font-weight: 900; color: #F0EBE0; letter-spacing: -2px; line-height: 1; }
.body { padding: 22px 26px 0; }
.section-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #1a1a1a; margin-bottom: 5px; }
.info-val { font-size: 11px; color: #1a1a1a; line-height: 1.65; }
.client-name { font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #1a1a1a; text-align: center; }
.client-sub  { font-size: 10px; color: #555; text-align: center; line-height: 1.6; margin-top: 4px; }
.items { width: 100%; border-collapse: collapse; }
.items thead tr { background: #1a1a1a; }
.items th { padding: 10px 12px; font-size: 10px; font-weight: 700; color: #F0EBE0; text-transform: uppercase; letter-spacing: 0.1em; text-align: left; }
.items th.r { text-align: right; }
.items td { padding: 13px 12px; font-size: 11px; color: #1a1a1a; border-bottom: 1px solid #D8D0C4; vertical-align: top; }
.items td.r { text-align: right; }
.items td.c { text-align: center; color: #555; }
.items tbody tr:last-child td { border-bottom: none; }
.total-tbl { width: 100%; border-collapse: collapse; border-top: 1.5px solid #C8C0B4; }
.total-label  { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; padding: 13px 12px 13px 0; }
.total-amount { font-size: 13px; font-weight: 700; text-align: right; padding: 13px 0; }
.quo-footer { padding: 24px 26px 26px; }
.approval-url { font-size: 9px; color: #888; word-break: break-all; }
</style>
</head>
<body>

<div class="page-bg"></div>

<div class="outer">
<div class="card">

    <table class="hdr">
        <tr>
            <td class="hdr-left">
                <table class="logo-tbl" style="border-collapse:collapse;">
                    <tr>
                        @if($company->logo)
                        <td><img src="{{ public_path('storage/' . $company->logo) }}" class="logo-img" alt=""></td>
                        @endif
                        <td class="brand-name">{{ $company->company_name }}</td>
                    </tr>
                </table>
            </td>
            <td class="hdr-right">
                <div class="quo-word">QUOTATION</div>
            </td>
        </tr>
    </table>

    <div class="body">
        <table style="width:100%; border-collapse:collapse; margin-bottom:18px;">
            <tr>
                <td style="width:46%; vertical-align:top;">
                    <div class="section-label">Penawaran</div>
                    <div class="info-val">{{ $quotation->quotation_number }}</div>
                    <div class="info-val">{{ $quotation->date->locale('id')->translatedFormat('d F Y') }}</div>
                    @if($quotation->expiry_date)
                    <div style="font-size:10px; color:#666; margin-top:3px;">Berlaku hingga: {{ $quotation->expiry_date->locale('id')->translatedFormat('d F Y') }}</div>
                    @endif
                </td>
                <td style="width:8%;"></td>
                <td style="width:46%; vertical-align:top;">
                    <div class="client-name">{{ $quotation->client->company_name }}</div>
                    @if($quotation->client->pic_name)<div class="client-sub">{{ $quotation->client->pic_name }}</div>@endif
                    @if($quotation->client->address)<div class="client-sub">{{ $quotation->client->address }}</div>@endif
                </td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th style="width:43%;">Deskripsi</th>
                    <th class="r" style="width:10%;">Qty</th>
                    <th class="r" style="width:20%;">Harga Satuan</th>
                    <th class="r" style="width:22%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $i => $item)
                <tr>
                    <td class="c">{{ $i + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="r">{{ number_format($item->quantity, 0, ',', '.') }}{{ $item->unit ? ' '.$item->unit : '' }}</td>
                    <td class="r">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="r" style="font-weight:600;">{{ number_format($item->total_price, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($quotation->tax_percent > 0 || $quotation->discount_amount > 0)
        <table style="width:100%; border-collapse:collapse; margin-top:6px;">
            <tr><td style="font-size:10px;color:#666;padding:2px 12px;">Subtotal</td><td style="font-size:10px;color:#666;text-align:right;padding:2px 0;">{{ number_format($quotation->subtotal,0,',','.') }}</td></tr>
            @if($quotation->discount_amount > 0)
            <tr><td style="font-size:10px;color:#666;padding:2px 12px;">Diskon</td><td style="font-size:10px;color:#666;text-align:right;padding:2px 0;">-{{ number_format($quotation->discount_amount,0,',','.') }}</td></tr>
            @endif
            @if($quotation->tax_percent > 0)
            <tr><td style="font-size:10px;color:#666;padding:2px 12px;">PPN {{ $quotation->tax_percent }}%</td><td style="font-size:10px;color:#666;text-align:right;padding:2px 0;">{{ number_format($quotation->tax_amount,0,',','.') }}</td></tr>
            @endif
        </table>
        @endif

        <div style="padding:0 12px 8px; margin-top:{{ ($quotation->tax_percent > 0 || $quotation->discount_amount > 0) ? '4px' : '14px' }};">
            <table class="total-tbl">
                <tr>
                    <td class="total-label">Total Penawaran</td>
                    <td class="total-amount">{{ number_format($quotation->total_amount, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    @if($quotation->notes)
    <div style="margin:14px 26px; padding:10px 12px; background:rgba(0,0,0,0.05); border-radius:6px; font-size:10px; color:#444; line-height:1.65;">
        <div style="font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#888; margin-bottom:5px;">Catatan</div>
        {{ $quotation->notes }}
    </div>
    @endif

    <div class="quo-footer">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="width:50%; vertical-align:bottom;">
                    <div style="font-size:10px; color:#555; line-height:1.75;">Penawaran ini berlaku hingga:</div>
                    <div style="font-size:11px; font-weight:700; color:#1a1a1a; margin-top:3px;">{{ $quotation->expiry_date->locale('id')->translatedFormat('d F Y') }}</div>
                    @if($company->phone || $company->email)
                    <div style="font-size:10px; color:#888; margin-top:8px; line-height:1.7;">
                        @if($company->phone){{ $company->phone }}<br>@endif
                        @if($company->email){{ $company->email }}@endif
                    </div>
                    @endif
                </td>
                <td style="width:50%; vertical-align:bottom; text-align:right;">
                    @if($quotation->getApprovalUrl())
                    <div style="font-size:9px; color:#888; margin-bottom:4px;">Link persetujuan:</div>
                    <div class="approval-url">{{ $quotation->getApprovalUrl() }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

</div>
</div>
</body>
</html>
