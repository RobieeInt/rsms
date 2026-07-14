<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FindingResource;
use App\Http\Resources\RecommendationResource;
use App\Models\Client;
use App\Models\Finding;
use App\Models\Recommendation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FindingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $query = Finding::with(['client', 'asset', 'reporter']);

        if ($user->hasRole('technician')) {
            $query->where('reported_by', $user->id);
        }

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $findings = $query->orderByRaw("FIELD(severity, 'critical','high','medium','low')")
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        return FindingResource::collection($findings);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'asset_id' => ['nullable', 'exists:assets,id'],
            'visit_report_id' => ['nullable', 'exists:visit_reports,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'severity' => ['required', 'in:low,medium,high,critical'],
            'status' => ['required', 'in:open,monitoring,resolved'],
        ]);

        $validated['reported_by'] = $request->user()->id;

        if ($validated['status'] === 'resolved') {
            $validated['resolved_at'] = now();
        }

        $finding = Finding::create($validated);

        // Recalculate client health score
        $client = Client::find($validated['client_id']);
        $client->recalculateHealthScore();

        return response()->json([
            'message' => 'Temuan berhasil dibuat.',
            'data' => new FindingResource($finding->load(['client', 'asset', 'reporter'])),
        ], 201);
    }

    public function show(Finding $finding): FindingResource
    {
        $finding->load(['client', 'asset', 'reporter', 'recommendations.creator']);

        return new FindingResource($finding);
    }

    public function update(Request $request, Finding $finding): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => ['sometimes', 'required', 'exists:clients,id'],
            'asset_id' => ['nullable', 'exists:assets,id'],
            'visit_report_id' => ['nullable', 'exists:visit_reports,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'severity' => ['sometimes', 'required', 'in:low,medium,high,critical'],
            'status' => ['sometimes', 'required', 'in:open,monitoring,resolved'],
        ]);

        if (isset($validated['status']) && $validated['status'] === 'resolved' && ! $finding->resolved_at) {
            $validated['resolved_at'] = now();
        }

        $finding->update($validated);

        // Recalculate client health score
        $client = Client::find($finding->client_id);
        $client->recalculateHealthScore();

        return response()->json([
            'message' => 'Temuan berhasil diperbarui.',
            'data' => new FindingResource($finding->fresh()->load(['client', 'asset', 'reporter', 'recommendations.creator'])),
        ]);
    }

    public function destroy(Finding $finding): JsonResponse
    {
        $finding->delete();

        // Recalculate client health score
        $client = Client::find($finding->client_id);
        $client->recalculateHealthScore();

        return response()->json([
            'message' => 'Temuan berhasil dihapus.',
        ]);
    }

    public function addRecommendation(Request $request, Finding $finding): JsonResponse
    {
        $validated = $request->validate([
            'recommendation' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high'],
        ]);

        $recommendation = Recommendation::create([
            'finding_id' => $finding->id,
            'created_by' => $request->user()->id,
            'recommendation' => $validated['recommendation'],
            'priority' => $validated['priority'],
        ]);

        return response()->json([
            'message' => 'Rekomendasi berhasil ditambahkan.',
            'data' => new RecommendationResource($recommendation->load('creator')),
        ], 201);
    }

    public function updateStatus(Request $request, Finding $finding): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,monitoring,resolved'],
        ]);

        $data = ['status' => $validated['status']];

        if ($validated['status'] === 'resolved' && ! $finding->resolved_at) {
            $data['resolved_at'] = now();
        }

        $finding->update($data);

        // Recalculate client health score
        $client = Client::find($finding->client_id);
        $client->recalculateHealthScore();

        return response()->json([
            'message' => 'Status temuan berhasil diperbarui.',
            'data' => new FindingResource($finding->fresh()->load(['client', 'asset', 'reporter', 'recommendations.creator'])),
        ]);
    }
}
