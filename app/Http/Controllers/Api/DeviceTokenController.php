<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'platform' => ['nullable', 'in:android,ios,web'],
        ]);

        // A token can belong to a different user after a logout/login on the
        // same device, so re-point it rather than erroring on the unique
        // constraint.
        DeviceToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $validated['platform'] ?? 'android',
            ]
        );

        return response()->json(['message' => 'Device token tersimpan.']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $request->user()->deviceTokens()->where('token', $validated['token'])->delete();

        return response()->json(['message' => 'Device token dihapus.']);
    }
}
