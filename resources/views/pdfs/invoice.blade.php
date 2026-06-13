<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

html, body {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 11px;
    color: #1a1a1a;
    background: #1a1a1a;
    margin: 0;
    padding: 0;
}

/* Full-page dark background (DomPDF fixed-position trick) */
.page-bg {
    position: fixed;
    top: -60px; left: -60px; right: -60px; bottom: -60px;
    background: #1a1a1a;
    z-index: -1;
}

.outer {
    padding: 26px 22px;
}

/* Cream card */
.card {
    background: #F0EBE0;
    border-radius: 20px;
    overflow: hidden;
}

/* ─── HEADER ─── */
.hdr-table {
    width: 100%;
    border-collapse: collapse;
}
.hdr-left {
    background: #F0EBE0;
    padding: 20px 24px 20px 24px;
    vertical-align: middle;
    width: 56%;
    border-radius: 20px 0 0 0;
}
.hdr-right {
    background: #1a1a1a;
    padding: 20px 28px 24px 20px;
    vertical-align: bottom;
    width: 44%;
    text-align: right;
    border-radius: 0 20px 0 32px;
}

/* Logo row inside header-left */
.logo-tbl { border-collapse: collapse; }
.logo-tbl td { vertical-align: middle; }
.logo-img  { height: 46px; width: auto; }
.brand-name {
    font-size: 20px;
    font-weight: 700;
    color: #1a1a1a;
    letter-spacing: -0.2px;
    padding-left: 10px;
    white-space: nowrap;
}

/* INVOICE word */
.invoice-word {
    font-size: 54px;
    font-weight: 900;
    color: #F0EBE0;
    letter-spacing: -2px;
    line-height: 1;
}

/* ─── BODY ─── */
.body { padding: 22px 26px 0; }

.info-tbl { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
.section-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #1a1a1a;
    margin-bottom: 5px;
}
.info-val { font-size: 11px; color: #1a1a1a; line-height: 1.65; }
.client-name {
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    text-align: center;
    color: #1a1a1a;
}
.client-addr {
    font-size: 10px;
    color: #555;
    text-align: center;
    line-height: 1.6;
    margin-top: 4px;
}

/* ─── ITEMS TABLE ─── */
.items { width: 100%; border-collapse: collapse; }
.items thead tr { background: #1a1a1a; }
.items th {
    padding: 10px 14px;
    font-size: 10px;
    font-weight: 700;
    color: #F0EBE0;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    text-align: left;
}
.items th.r { text-align: right; }
.items td {
    padding: 13px 14px;
    font-size: 11px;
    color: #1a1a1a;
    border-bottom: 1px solid #D8D0C4;
    vertical-align: top;
}
.items td.r { text-align: right; }
.items tbody tr:last-child td { border-bottom: none; }

/* ─── TOTAL ─── */
.total-wrap { padding: 0 14px 6px; }
.total-tbl { width: 100%; border-collapse: collapse; border-top: 1.5px solid #C8C0B4; }
.total-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 14px 14px 14px 0;
}
.total-amount {
    font-size: 13px;
    font-weight: 700;
    text-align: right;
    padding: 14px 0;
}

/* ─── PAID STAMP ─── */
@if($invoice->status === 'paid')
.stamp {
    position: fixed;
    top: 44%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-22deg);
    font-size: 68px;
    font-weight: 900;
    color: rgba(5, 150, 105, 0.11);
    letter-spacing: 8px;
    text-transform: uppercase;
}
@endif

/* ─── FOOTER ─── */
.footer { padding: 38px 26px 28px; }
.footer-tbl { width: 100%; border-collapse: collapse; }
.pay-note { font-size: 10px; color: #555; line-height: 1.75; }
.bank-block { margin-top: 8px; font-size: 12px; font-weight: 700; color: #1a1a1a; line-height: 1.9; text-align: center; }
.sig {
    font-family: 'DejaVu Serif', serif;
    font-style: italic;
    font-size: 22px;
    color: #1a1a1a;
    text-align: right;
    vertical-align: bottom;
    padding-bottom: 2px;
}
</style>
</head>
<body>

<div class="page-bg"></div>

@if($invoice->status === 'paid')<div class="stamp">LUNAS</div>@endif

<div class="outer">
<div class="card">

    {{-- ─── HEADER ─── --}}
    <table class="hdr-table">
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
                <div class="invoice-word">INVOICE</div>
            </td>
        </tr>
    </table>

    {{-- ─── BODY ─── --}}
    <div class="body">

        {{-- Pembayaran left | Client right --}}
        <table class="info-tbl">
            <tr>
                <td style="width:46%; vertical-align:top;">
                    <div class="section-label">Pembayaran</div>
                    <div class="info-val">{{ $invoice->invoice_number }}</div>
                    <div class="info-val">{{ $invoice->invoice_date->locale('id')->translatedFormat('d M Y') }}</div>
                    @if($invoice->due_date)
                    <div style="font-size:10px; color:#666; margin-top:3px;">
                        Jatuh tempo: {{ $invoice->due_date->locale('id')->translatedFormat('d M Y') }}
                    </div>
                    @endif
                </td>
                <td style="width:8%;"></td>
                <td style="width:46%; vertical-align:top;">
                    <div class="client-name">{{ $invoice->client->company_name }}</div>
                    @if($invoice->client->address)
                    <div class="client-addr">{{ $invoice->client->address }}</div>
                    @endif
                    @if($invoice->client->pic_name)
                    <div style="font-size:10px; text-align:center; color:#555; margin-top:3px;">
                        {{ $invoice->client->pic_name }}
                    </div>
                    @endif
                </td>
            </tr>
        </table>

        {{-- Items table --}}
        <table class="items">
            <thead>
                <tr>
                    <th style="width:46%;">Deskripsi</th>
                    <th class="r" style="width:20%;">Harga</th>
                    <th class="r" style="width:14%;">Jumlah</th>
                    <th class="r" style="width:20%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="r">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="r">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                    <td class="r">{{ number_format($item->total_price, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Subtotal/discount/tax rows --}}
        @if($invoice->tax_percent > 0 || $invoice->discount_amount > 0)
        <table style="width:100%; border-collapse:collapse; margin-top:6px;">
            @if($invoice->discount_amount > 0)
            <tr>
                <td style="font-size:10px; color:#666; padding:2px 14px 2px 14px;">Diskon</td>
                <td style="font-size:10px; color:#666; text-align:right; padding:2px 0;">-{{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($invoice->tax_percent > 0)
            <tr>
                <td style="font-size:10px; color:#666; padding:2px 14px 2px 14px;">PPN {{ $invoice->tax_percent }}%</td>
                <td style="font-size:10px; color:#666; text-align:right; padding:2px 0;">{{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
        </table>
        @endif

        {{-- Total pembayaran --}}
        <div class="total-wrap" style="margin-top: {{ ($invoice->tax_percent > 0 || $invoice->discount_amount > 0) ? '4px' : '14px' }};">
            <table class="total-tbl">
                <tr>
                    <td class="total-label">Total Pembayaran</td>
                    <td class="total-amount">{{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        @if($invoice->notes)
        <div style="margin: 10px 14px 0; padding: 8px 12px; background: rgba(0,0,0,0.05); border-radius: 6px; font-size: 10px; color: #555; line-height: 1.6;">
            {{ $invoice->notes }}
        </div>
        @endif

    </div>

    {{-- ─── FOOTER ─── --}}
    <div class="footer">
        <table class="footer-tbl">
            <tr>
                <td style="width:50%; vertical-align:bottom;">
                    @if($company->bank_name)
                    <div class="pay-note">
                        Pembayaran dapat di lakukan ke<br>
                        Nomor Rekening dibawah ini :
                    </div>
                    <div class="bank-block">
                        {{ $company->bank_name }}<br>
                        {{ $company->bank_account_number }}<br>
                        {{ $company->bank_account_holder }}
                    </div>
                    @endif
                </td>
                <td style="width:50%;">
                    @if($invoice->status === 'paid' && $invoice->payment_date)
                    <div style="font-size:10px; color:#555; text-align:right; margin-bottom:6px;">
                        Dibayar {{ $invoice->payment_date->locale('id')->translatedFormat('d F Y') }}
                        @if($invoice->payment_method) &bull; {{ $invoice->payment_method }}@endif
                    </div>
                    @endif
                    <div class="sig">{{ $company->bank_account_holder ?? $company->company_name }}</div>
                </td>
            </tr>
        </table>
    </div>

</div>{{-- /card --}}
</div>{{-- /outer --}}

</body>
</html>
