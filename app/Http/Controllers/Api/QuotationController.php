<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\QuotationResource;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Services\InvoiceService;
use App\Services\QuotationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Quotation::class);

        $query = Quotation::with('client');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
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

        $quotations = $query->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        return QuotationResource::collection($quotations);
    }

    public function store(Request $request, QuotationService $quotationService): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'date' => ['required', 'date'],
            'expiry_date' => ['required', 'date', 'after_or_equal:date'],
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
            'items.*.finding_id' => ['nullable', 'exists:findings,id'],
        ]);

        // Calculate item totals
        foreach ($validated['items'] as &$item) {
            $item['total_price'] = ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0);
        }
        unset($item);

        $quotation = $quotationService->create($validated, $request->user()->id);

        return response()->json([
            'message' => 'Penawaran berhasil dibuat.',
            'data' => new QuotationResource($quotation->load(['client', 'items'])),
        ], 201);
    }

    public function show(Quotation $quotation): QuotationResource
    {
        $this->authorize('view', $quotation);

        $quotation->load(['client', 'creator', 'items', 'invoice']);

        return new QuotationResource($quotation);
    }

    public function update(Request $request, Quotation $quotation): JsonResponse
    {
        $this->authorize('update', $quotation);

        $validated = $request->validate([
            'client_id' => ['sometimes', 'required', 'exists:clients,id'],
            'date' => ['sometimes', 'required', 'date'],
            'expiry_date' => ['sometimes', 'required', 'date', 'after_or_equal:date'],
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
            'items.*.finding_id' => ['nullable', 'exists:findings,id'],
        ]);

        DB::transaction(function () use ($validated, $quotation) {
            // Recalculate if items provided
            if (isset($validated['items'])) {
                foreach ($validated['items'] as &$item) {
                    $item['total_price'] = ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0);
                }
                unset($item);

                $subtotal = collect($validated['items'])->sum('total_price');
                $taxAmount = $subtotal * (($validated['tax_percent'] ?? $quotation->tax_percent) / 100);
                $total = $subtotal + $taxAmount - ($validated['discount_amount'] ?? $quotation->discount_amount);

                $validated['subtotal'] = $subtotal;
                $validated['tax_amount'] = $taxAmount;
                $validated['total_amount'] = $total;

                // Delete old items and recreate
                $quotation->items()->delete();
                foreach ($validated['items'] as $index => $item) {
                    QuotationItem::create([
                        'quotation_id' => $quotation->id,
                        'finding_id' => $item['finding_id'] ?? null,
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

            $quotation->update($validated);
        });

        return response()->json([
            'message' => 'Penawaran berhasil diperbarui.',
            'data' => new QuotationResource($quotation->fresh()->load(['client', 'creator', 'items'])),
        ]);
    }

    public function destroy(Quotation $quotation): JsonResponse
    {
        $this->authorize('delete', $quotation);

        $quotation->delete();

        return response()->json([
            'message' => 'Penawaran berhasil dihapus.',
        ]);
    }

    public function send(Quotation $quotation, QuotationService $quotationService): JsonResponse
    {
        $this->authorize('update', $quotation);

        if ($quotation->status !== 'draft') {
            return response()->json([
                'message' => 'Hanya penawaran dengan status draft yang bisa dikirim.',
            ], 422);
        }

        $quotationService->send($quotation);

        return response()->json([
            'message' => 'Penawaran berhasil dikirim ke client.',
            'data' => new QuotationResource($quotation->fresh()->load(['client', 'items'])),
        ]);
    }

    public function convertToInvoice(Request $request, Quotation $quotation, InvoiceService $invoiceService): JsonResponse
    {
        $this->authorize('update', $quotation);

        if ($quotation->status !== 'approved') {
            return response()->json([
                'message' => 'Hanya penawaran yang disetujui yang bisa diubah ke invoice.',
            ], 422);
        }

        if ($quotation->invoice) {
            return response()->json([
                'message' => 'Penawaran ini sudah memiliki invoice.',
            ], 422);
        }

        $invoice = $invoiceService->createFromQuotation($quotation, $request->user()->id);

        return response()->json([
            'message' => 'Invoice berhasil dibuat dari penawaran.',
            'data' => new InvoiceResource($invoice->load(['client', 'items'])),
        ], 201);
    }
}
