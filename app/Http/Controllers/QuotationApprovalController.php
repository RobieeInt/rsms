<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use Illuminate\Http\Request;

class QuotationApprovalController extends Controller
{
    public function show(string $token)
    {
        $quotation = Quotation::with(['client', 'items'])
            ->where('approval_token', $token)
            ->firstOrFail();

        return view('quotation-approval', compact('quotation'));
    }

    public function process(string $token, Request $request)
    {
        $request->validate([
            'action' => ['required', 'in:approved,rejected'],
            'approved_by_name' => ['required', 'string', 'max:255'],
            'approval_notes' => ['nullable', 'string'],
        ]);

        $quotation = Quotation::where('approval_token', $token)
            ->whereIn('status', ['sent'])
            ->firstOrFail();

        $quotation->update([
            'status' => $request->action,
            'approved_by_name' => $request->approved_by_name,
            'approval_notes' => $request->approval_notes,
            'approved_at' => now(),
        ]);

        $message = $request->action === 'approved'
            ? 'Quotation approved successfully. We will contact you shortly.'
            : 'Quotation rejected. We will reach out to discuss further.';

        return back()->with('success', $message);
    }
}
