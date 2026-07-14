<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $request->user()->notifications();

        if ($request->boolean('unread_only')) {
            $query->whereNull('read_at');
        }

        $notifications = $query->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return NotificationResource::collection($notifications);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->update(['read_at' => now()]);

        return response()->json([
            'message' => 'Notifikasi berhasil ditandai sudah dibaca.',
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'message' => 'Semua notifikasi berhasil ditandai sudah dibaca.',
        ]);
    }
}
