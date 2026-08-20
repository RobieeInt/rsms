<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\User;
use App\Notifications\AdminAlertNotification;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class QuotationApprovalController extends Controller
{
    public function process(string $token, Request $request, InvoiceService $invoiceService): JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:approved,rejected'],
            'approved_by_name' => ['required', 'string', 'max:255'],
            'approval_notes' => ['nullable', 'string'],
        ]);

        $quotation = Quotation::with(['client', 'items'])
            ->where('approval_token', $token)
            ->whereIn('status', ['sent'])
            ->firstOrFail();

        $quotation->update([
            'status' => $validated['action'],
            'approved_by_name' => $validated['approved_by_name'],
            'approval_notes' => $validated['approval_notes'] ?? null,
            'approved_at' => now(),
        ]);

        $admins = User::role('admin')->get();

        if ($validated['action'] === 'approved') {
            if (! $quotation->invoices()->exists()) {
                $invoice = $invoiceService->createFromQuotation($quotation, $admins->first()->id);
                try {
                    $invoiceService->markAsSent($invoice);
                } catch (Throwable $e) {
                    Log::warning('Invoice dibuat tapi gagal mengirim email: '.$e->getMessage());
                }
            }

            $notif = new AdminAlertNotification(
                'Penawaran Disetujui',
                $validated['approved_by_name'].' menyetujui '.$quotation->quotation_number.' ('.$quotation->client->company_name.')',
                'success',
                route('quotations.show', $quotation)
            );
        } else {
            $notif = new AdminAlertNotification(
                'Penawaran Ditolak',
                $validated['approved_by_name'].' menolak '.$quotation->quotation_number.' ('.$quotation->client->company_name.')'
                .(($validated['approval_notes'] ?? null) ? ': '.$validated['approval_notes'] : ''),
                'danger',
                route('quotations.show', $quotation)
            );
        }

        try {
            $admins->each(fn ($admin) => $admin->notifyNow($notif));
        } catch (Throwable $e) {
            Log::warning('Gagal mengirim notifikasi persetujuan penawaran: '.$e->getMessage());
        }

        $message = $validated['action'] === 'approved'
            ? 'Penawaran disetujui! Invoice telah dikirim ke email Anda.'
            : 'Penawaran ditolak. Kami akan menghubungi Anda untuk diskusi lebih lanjut.';

        return response()->json([
            'message' => $message,
            'quotation_number' => $quotation->quotation_number,
            'status' => $quotation->status,
        ]);
    }
}
