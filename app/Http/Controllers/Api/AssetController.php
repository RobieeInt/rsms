<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AssetController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Asset::with('client');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('asset_name', 'like', "%{$search}%")
                    ->orWhere('asset_code', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('asset_type')) {
            $query->where('asset_type', $request->asset_type);
        }

        if ($request->filled('health')) {
            $query->where('health_status', $request->health);
        }

        $assets = $query->orderBy('asset_code')
            ->paginate($request->input('per_page', 15));

        return AssetResource::collection($assets);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'asset_name' => ['required', 'string', 'max:255'],
            'asset_type' => ['required', 'in:'.implode(',', array_keys(Asset::assetTypes()))],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'cpu' => ['nullable', 'string'],
            'ram' => ['nullable', 'string'],
            'storage' => ['nullable', 'string'],
            'operating_system' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
            'purchase_year' => ['nullable', 'digits:4', 'integer'],
            'notes' => ['nullable', 'string'],
            'health_status' => ['nullable', 'in:good,fair,poor,critical'],
        ]);

        $validated['asset_code'] = Asset::generateCode();
        $validated['health_status'] = $request->input('health_status', 'good');

        $asset = Asset::create($validated);

        return response()->json([
            'message' => 'Aset berhasil dibuat.',
            'data' => new AssetResource($asset->load('client')),
        ], 201);
    }

    public function show(Asset $asset): AssetResource
    {
        $asset->load(['client', 'checklists.visitReport', 'findings']);

        return new AssetResource($asset);
    }

    public function update(Request $request, Asset $asset): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => ['sometimes', 'required', 'exists:clients,id'],
            'asset_name' => ['sometimes', 'required', 'string', 'max:255'],
            'asset_type' => ['sometimes', 'required', 'in:'.implode(',', array_keys(Asset::assetTypes()))],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'cpu' => ['nullable', 'string'],
            'ram' => ['nullable', 'string'],
            'storage' => ['nullable', 'string'],
            'operating_system' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
            'purchase_year' => ['nullable', 'digits:4', 'integer'],
            'notes' => ['nullable', 'string'],
            'health_status' => ['nullable', 'in:good,fair,poor,critical'],
        ]);

        $asset->update($validated);

        return response()->json([
            'message' => 'Aset berhasil diperbarui.',
            'data' => new AssetResource($asset->fresh()->load('client')),
        ]);
    }

    public function destroy(Asset $asset): JsonResponse
    {
        $asset->delete();

        return response()->json([
            'message' => 'Aset berhasil dihapus.',
        ]);
    }
}
