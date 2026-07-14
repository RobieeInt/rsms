<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TechnicianController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = User::role('technician');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        $technicians = $query->orderBy('name')
            ->paginate($request->input('per_page', 15));

        return UserResource::collection($technicians);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'position' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $user = User::create($validated);
        $user->assignRole('technician');

        return response()->json([
            'message' => 'Teknisi berhasil dibuat.',
            'data' => new UserResource($user->load('roles')),
        ], 201);
    }

    public function show(User $technician): UserResource
    {
        $technician->load('roles');

        return new UserResource($technician);
    }

    public function update(Request $request, User $technician): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,'.$technician->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'position' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', $technician->is_active);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $technician->update($validated);

        return response()->json([
            'message' => 'Teknisi berhasil diperbarui.',
            'data' => new UserResource($technician->fresh()->load('roles')),
        ]);
    }

    public function destroy(User $technician): JsonResponse
    {
        $technician->delete();

        return response()->json([
            'message' => 'Teknisi berhasil dihapus.',
        ]);
    }
}
