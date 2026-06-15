<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\User;
use App\Notifications\AdminAlertNotification;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class QuotationApprovalController extends Controller
{
    public function show(string $token)
    {
        $quotation = Quotation::with(['client', 'items', 'invoice'])
            ->where('approval_token', $token)
            ->firstOrFail();

        return view('quotation-approval', compact('quotation'));
    }

    public function process(string $token, Request $request, InvoiceService $invoiceService)
    {
        $request->validate([
            'action' => ['required', 'in:approved,rejected'],
            'approved_by_name' => ['required', 'string', 'max:255'],
            'approval_notes' => ['nullable', 'string'],
        ]);

        $quotation = Quotation::with(['client', 'items'])
            ->where('approval_token', $token)
            ->whereIn('status', ['sent'])
            ->firstOrFail();

        $quotation->update([
            'status' => $request->action,
            'approved_by_name' => $request->approved_by_name,
            'approval_notes' => $request->approval_notes,
            'approved_at' => now(),
        ]);

        $admins = User::role('admin')->get();

        if ($request->action === 'approved') {
            if (! $quotation->invoice) {
                $invoice = $invoiceService->createFromQuotation($quotation, $admins->first()->id);
                $invoiceService->markAsSent($invoice);
            }

            $notif = new AdminAlertNotification(
                'Penawaran Disetujui',
                $request->approved_by_name . ' menyetujui ' . $quotation->quotation_number . ' (' . $quotation->client->company_name . ')',
                'success',
                route('quotations.show', $quotation)
            );
        } else {
            $notif = new AdminAlertNotification(
                'Penawaran Ditolak',
                $request->approved_by_name . ' menolak ' . $quotation->quotation_number . ' (' . $quotation->client->company_name . ')'
                . ($request->approval_notes ? ': ' . $request->approval_notes : ''),
                'danger',
                route('quotations.show', $quotation)
            );
        }

        $admins->each(fn($admin) => $admin->notifyNow($notif));

        $message = $request->action === 'approved'
            ? 'Penawaran disetujui! Invoice telah dikirim ke email Anda.'
            : 'Penawaran ditolak. Kami akan menghubungi Anda untuk diskusi lebih lanjut.';

        return back()->with('success', $message);
    }
}
