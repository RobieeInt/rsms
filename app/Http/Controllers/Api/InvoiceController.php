<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Notifications\InvoiceGeneratedNotification;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvoiceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::with(['client', 'sendLogs']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($cq) use ($search) {
                        $cq->where('company_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $invoices = $query->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->paginate($request->input('per_page', 15));

        $totalUnpaid = Invoice::whereIn('status', ['sent', 'overdue'])->sum('total_amount');

        $resource = InvoiceResource::collection($invoices);
        $resource->additional = ['total_unpaid' => (float) $totalUnpaid];

        return $resource;
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'type' => ['nullable', 'in:retainer,quotation,manual'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'tax_percent' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.detail' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['nullable', 'string'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        // Calculate item totals
        foreach ($validated['items'] as &$item) {
            $item['total_price'] = ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0);
        }
        unset($item);

        $subtotal = collect($validated['items'])->sum('total_price');
        $taxAmount = $subtotal * (($validated['tax_percent'] ?? 0) / 100);
        $total = $subtotal + $taxAmount - ($validated['discount_amount'] ?? 0);

        $invoice = Invoice::create([
            'client_id' => $validated['client_id'],
            'created_by' => $request->user()->id,
            'invoice_number' => Invoice::generateNumber(),
            'type' => $validated['type'] ?? 'manual',
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'],
            'subtotal' => $subtotal,
            'tax_percent' => $validated['tax_percent'] ?? 0,
            'tax_amount' => $taxAmount,
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'total_amount' => $total,
            'notes' => $validated['notes'] ?? null,
            'status' => 'draft',
        ]);

        foreach ($validated['items'] as $index => $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'detail' => $item['detail'] ?? null,
                'quantity' => $item['quantity'],
                'unit' => $item['unit'] ?? 'unit',
                'unit_price' => $item['unit_price'],
                'discount_amount' => $item['discount_amount'] ?? 0,
                'total_price' => $item['total_price'],
                'sort_order' => $index,
            ]);
        }

        return response()->json([
            'message' => 'Invoice berhasil dibuat.',
            'data' => new InvoiceResource($invoice->load(['client', 'items'])),
        ], 201);
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        $this->authorize('view', $invoice);

        $invoice->load(['client', 'creator', 'items', 'sendLogs']);

        return new InvoiceResource($invoice);
    }

    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'client_id' => ['sometimes', 'required', 'exists:clients,id'],
            'type' => ['sometimes', 'in:retainer,quotation,manual'],
            'invoice_date' => ['sometimes', 'required', 'date'],
            'due_date' => ['sometimes', 'required', 'date'],
            'tax_percent' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['sometimes', 'required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.detail' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['nullable', 'string'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (isset($validated['items'])) {
            foreach ($validated['items'] as &$item) {
                $item['total_price'] = ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0);
            }
            unset($item);

            $subtotal = collect($validated['items'])->sum('total_price');
            $taxAmount = $subtotal * (($validated['tax_percent'] ?? $invoice->tax_percent) / 100);
            $total = $subtotal + $taxAmount - ($validated['discount_amount'] ?? $invoice->discount_amount);

            $validated['subtotal'] = $subtotal;
            $validated['tax_amount'] = $taxAmount;
            $validated['total_amount'] = $total;

            // Delete old items and recreate
            $invoice->items()->delete();
            foreach ($validated['items'] as $index => $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'detail' => $item['detail'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? 'unit',
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'total_price' => $item['total_price'],
                    'sort_order' => $index,
                ]);
            }

            unset($validated['items']);
        }

        $invoice->update($validated);

        return response()->json([
            'message' => 'Invoice berhasil diperbarui.',
            'data' => new InvoiceResource($invoice->fresh()->load(['client', 'items'])),
        ]);
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        $this->authorize('delete', $invoice);

        if ($invoice->status !== 'draft') {
            return response()->json([
                'message' => 'Hanya invoice berstatus draft yang bisa dihapus.',
            ], 422);
        }

        $invoice->delete();

        return response()->json([
            'message' => 'Invoice berhasil dihapus.',
        ]);
    }

    public function send(Invoice $invoice, InvoiceService $invoiceService): JsonResponse
    {
        $this->authorize('update', $invoice);

        if ($invoice->status !== 'draft') {
            return response()->json([
                'message' => 'Hanya invoice dengan status draft yang bisa dikirim.',
            ], 422);
        }

        $invoiceService->markAsSent($invoice);

        return response()->json([
            'message' => 'Invoice berhasil dikirim.',
            'data' => new InvoiceResource($invoice->fresh()->load(['client', 'items'])),
        ]);
    }

    public function resendEmail(Invoice $invoice): JsonResponse
    {
        $this->authorize('update', $invoice);

        if (! $invoice->client || ! $invoice->client->pic_email) {
            return response()->json([
                'message' => 'Client tidak memiliki email PIC.',
            ], 422);
        }

        $invoice->load('client');
        $invoice->client->notifyNow(new InvoiceGeneratedNotification($invoice));
        $invoice->logSend('sent', $invoice->client->pic_email);

        return response()->json([
            'message' => 'Email invoice berhasil dikirim ulang.',
        ]);
    }

    public function markAsPaid(Request $request, Invoice $invoice, InvoiceService $invoiceService): JsonResponse
    {
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string'],
            'payment_proof' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf'],
            'payment_notes' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('payment_proof')) {
            $validated['payment_proof'] = $request->file('payment_proof')->store('payment-proofs', 'public');
        }

        $invoiceService->markAsPaid($invoice, $validated);

        return response()->json([
            'message' => 'Invoice berhasil ditandai sebagai lunas.',
            'data' => new InvoiceResource($invoice->fresh()->load(['client', 'items'])),
        ]);
    }

    public function generateRetainer(Request $request, InvoiceService $invoiceService): JsonResponse
    {
        $this->authorize('create', Invoice::class);

        $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
        ]);

        $client = Client::find($request->client_id);

        if ($client->monthly_retainer_fee <= 0) {
            return response()->json([
                'message' => 'Client ini tidak memiliki biaya retainer.',
            ], 422);
        }

        $exists = Invoice::where('client_id', $client->id)
            ->where('type', 'retainer')
            ->whereYear('invoice_date', now()->year)
            ->whereMonth('invoice_date', now()->month)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Invoice retainer untuk bulan ini sudah dibuat.',
            ], 422);
        }

        $invoice = $invoiceService->createFromRetainer($client, $request->user()->id);

        return response()->json([
            'message' => 'Invoice retainer berhasil dibuat.',
            'data' => new InvoiceResource($invoice->load(['client', 'items'])),
        ], 201);
    }
}
