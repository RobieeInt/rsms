<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotation Approval — {{ $quotation->quotation_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; line-height: 1.5; }
        .container { max-width: 680px; margin: 40px auto; padding: 0 16px; }
        .header { text-align: center; margin-bottom: 32px; }
        .logo { font-size: 24px; font-weight: 700; color: #7c3aed; margin-bottom: 8px; }
        .card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { background: linear-gradient(135deg, #7c3aed, #6d28d9); padding: 24px; color: white; }
        .card-header h1 { font-size: 20px; font-weight: 700; }
        .card-header p { opacity: 0.8; margin-top: 4px; font-size: 14px; }
        .card-body { padding: 24px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 14px; }
        th { padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        th.right { text-align: right; }
        td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; }
        td.right { text-align: right; font-weight: 600; }
        .total-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; color: #64748b; }
        .total-final { display: flex; justify-content: space-between; padding: 10px 0; font-size: 18px; font-weight: 700; color: #7c3aed; border-top: 2px solid #e2e8f0; margin-top: 4px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px; }
        input, textarea, select { width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; font-family: inherit; }
        input:focus, textarea:focus { outline: none; border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,0.1); }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; border: none; cursor: pointer; width: 100%; margin-bottom: 8px; transition: all 0.15s; }
        .btn-approve { background: #059669; color: white; }
        .btn-approve:hover { background: #047857; }
        .btn-reject { background: #dc2626; color: white; }
        .btn-reject:hover { background: #b91c1c; }
        .alert { padding: 14px 16px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .alert-info { background: #ede9fe; color: #5b21b6; border: 1px solid #c4b5fd; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-approved { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .divider { border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="logo">Reconext</div>
        <p style="color: #64748b; font-size: 14px;">IT Service Quotation</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <h1>{{ $quotation->quotation_number }}</h1>
            <p>From {{ $quotation->client->company_name }}</p>
        </div>
        <div class="card-body">
            <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                <div><div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px;">Date</div><div style="font-weight: 600;">{{ $quotation->date->format('d F Y') }}</div></div>
                <div><div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px;">Valid Until</div><div style="font-weight: 600;">{{ $quotation->expiry_date->format('d F Y') }}</div></div>
                <div><div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px;">Status</div>
                    @if($quotation->status === 'approved')
                    <span class="status-badge status-approved">Approved</span>
                    @elseif($quotation->status === 'rejected')
                    <span class="status-badge status-rejected">Rejected</span>
                    @else
                    <span style="font-weight: 600; color: #1e293b;">Pending Review</span>
                    @endif
                </div>
            </div>

            <table>
                <thead><tr>
                    <th>#</th><th>Description</th><th class="right">Qty</th><th class="right">Unit Price</th><th class="right">Total</th>
                </tr></thead>
                <tbody>
                    @foreach($quotation->items as $i => $item)
                    <tr>
                        <td style="color: #94a3b8;">{{ $i + 1 }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="right">{{ $item->quantity }} {{ $item->unit }}</td>
                        <td class="right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="display: flex; justify-content: flex-end;">
                <div style="width: 280px;">
                    <div class="total-row"><span>Subtotal</span><span>Rp {{ number_format($quotation->subtotal, 0, ',', '.') }}</span></div>
                    @if($quotation->tax_percent > 0)
                    <div class="total-row"><span>Tax ({{ $quotation->tax_percent }}%)</span><span>Rp {{ number_format($quotation->tax_amount, 0, ',', '.') }}</span></div>
                    @endif
                    <div class="total-final"><span>TOTAL</span><span>Rp {{ number_format($quotation->total_amount, 0, ',', '.') }}</span></div>
                </div>
            </div>

            @if($quotation->notes)
            <div style="margin-top: 20px; padding: 14px; background: #f8fafc; border-radius: 8px; font-size: 13px; color: #475569;">
                <strong>Notes:</strong> {{ $quotation->notes }}
            </div>
            @endif

            @if(in_array($quotation->status, ['sent']))
            <hr class="divider">
            <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 16px;">Your Response</h3>

            <form action="{{ route('quotation.approve.post', $quotation->approval_token) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Your Name <span style="color: #dc2626;">*</span></label>
                    <input type="text" name="approved_by_name" required placeholder="Your full name" value="{{ old('approved_by_name') }}">
                    @error('approved_by_name')<p style="color: #dc2626; font-size: 12px; margin-top: 4px;">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label>Notes (Optional)</label>
                    <textarea name="approval_notes" rows="3" placeholder="Any comments or conditions...">{{ old('approval_notes') }}</textarea>
                </div>
                <button type="submit" name="action" value="approved" class="btn btn-approve">
                    ✓ Approve Quotation
                </button>
                <button type="submit" name="action" value="rejected" class="btn btn-reject">
                    ✗ Reject Quotation
                </button>
            </form>
            @elseif($quotation->status === 'approved')
            <div class="alert alert-success" style="margin-top: 20px;">
                ✓ This quotation was approved by <strong>{{ $quotation->approved_by_name }}</strong> on {{ $quotation->approved_at->format('d F Y H:i') }}.
            </div>
            @elseif($quotation->status === 'rejected')
            <div style="background: #fee2e2; color: #991b1b; padding: 14px 16px; border-radius: 8px; margin-top: 20px;">
                ✗ This quotation was rejected by <strong>{{ $quotation->approved_by_name }}</strong>.
                @if($quotation->approval_notes)<br><em>{{ $quotation->approval_notes }}</em>@endif
            </div>
            @endif
        </div>
    </div>
    <p style="text-align: center; font-size: 12px; color: #94a3b8; margin-top: 20px;">
        &copy; {{ date('Y') }} {{ config('app.name') }}. Powered by Reconext RSMS.
    </p>
</div>
</body>
</html>
