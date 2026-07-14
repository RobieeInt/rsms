<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Http\Resources\InvoiceResource;
use App\Models\Client;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Client::withCount(['assets', 'schedules', 'findings', 'invoices']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%")
                    ->orWhere('pic_email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        if ($request->filled('health')) {
            $query->where('health_status', $request->health);
        }

        $clients = $query->orderBy('company_name')
            ->paginate($request->input('per_page', 15));

        return ClientResource::collection($clients);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'pic_name' => ['required', 'string', 'max:255'],
            'pic_email' => ['required', 'email', 'max:255'],
            'pic_phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'monthly_retainer_fee' => ['required', 'numeric', 'min:0'],
            'invoice_due_date' => ['nullable', 'integer', 'between:1,28'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['health_score'] = 100;
        $validated['health_status'] = 'healthy';
        $validated['is_active'] = $request->boolean('is_active', true);

        $client = Client::create($validated);

        return response()->json([
            'message' => 'Client berhasil dibuat.',
            'data' => new ClientResource($client),
        ], 201);
    }

    public function show(Client $client): ClientResource
    {
        $client->loadCount(['assets', 'schedules', 'findings', 'invoices']);

        return new ClientResource($client);
    }

    public function update(Request $request, Client $client): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => ['sometimes', 'required', 'string', 'max:255'],
            'pic_name' => ['sometimes', 'required', 'string', 'max:255'],
            'pic_email' => ['sometimes', 'required', 'email', 'max:255'],
            'pic_phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'monthly_retainer_fee' => ['sometimes', 'required', 'numeric', 'min:0'],
            'invoice_due_date' => ['nullable', 'integer', 'between:1,28'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', $client->is_active);

        $client->update($validated);

        return response()->json([
            'message' => 'Client berhasil diperbarui.',
            'data' => new ClientResource($client->fresh()),
        ]);
    }

    public function destroy(Client $client): JsonResponse
    {
        $client->delete();

        return response()->json([
            'message' => 'Client berhasil dihapus.',
        ]);
    }

    public function generateRetainer(Client $client, InvoiceService $invoiceService): JsonResponse
    {
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

        $invoice = $invoiceService->createFromRetainer($client, auth()->id());

        return response()->json([
            'message' => 'Invoice retainer berhasil dibuat.',
            'data' => new InvoiceResource($invoice),
        ], 201);
    }
}
