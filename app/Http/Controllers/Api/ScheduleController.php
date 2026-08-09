<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScheduleResource;
use App\Models\Schedule;
use App\Models\User;
use App\Notifications\AdminAlertNotification;
use App\Notifications\ScheduleCreatedNotification;
use App\Notifications\ScheduleUpdatedNotification;
use App\Notifications\TechnicianScheduleNotification;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Throwable;

class ScheduleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $query = Schedule::with(['client', 'technician']);

        if ($user->hasRole('technician')) {
            $query->where('technician_id', $user->id);
        }

        if ($search = $request->input('search')) {
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        if ($request->filled('date_from')) {
            $query->where('visit_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('visit_date', '<=', $request->date_to);
        }

        $schedules = $query->orderByDesc('visit_date')
            ->paginate($request->input('per_page', 15));

        return ScheduleResource::collection($schedules);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'technician_id' => ['required', 'exists:users,id'],
            'visit_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['nullable'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['status'] = 'scheduled';

        $schedule = Schedule::create($validated);
        $schedule->load(['client', 'technician']);

        // Notifications are best-effort: a mail/SMTP outage must not make an
        // otherwise-successful schedule creation look like it failed.
        try {
            $technician = User::find($validated['technician_id']);
            $technician->notifyNow(new TechnicianScheduleNotification($schedule));

            if ($schedule->client && $schedule->client->pic_email) {
                $schedule->client->notifyNow(new ScheduleCreatedNotification($schedule));
            }
        } catch (Throwable $e) {
            Log::warning('Gagal mengirim notifikasi jadwal baru: '.$e->getMessage());
        }

        return response()->json([
            'message' => 'Jadwal berhasil dibuat.',
            'data' => new ScheduleResource($schedule),
        ], 201);
    }

    public function show(Schedule $schedule): ScheduleResource
    {
        $schedule->load(['client', 'technician', 'visitReport']);

        return new ScheduleResource($schedule);
    }

    public function update(Request $request, Schedule $schedule): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => ['sometimes', 'required', 'exists:clients,id'],
            'technician_id' => ['sometimes', 'required', 'exists:users,id'],
            'visit_date' => ['sometimes', 'required', 'date'],
            'start_time' => ['sometimes', 'required'],
            'end_time' => ['nullable'],
            'notes' => ['nullable', 'string'],
        ]);

        $schedule->update($validated);
        $schedule = $schedule->fresh()->load(['client', 'technician']);

        // Notifications are best-effort: a mail/SMTP outage must not make an
        // otherwise-successful schedule update look like it failed.
        try {
            if ($schedule->client && $schedule->client->pic_email) {
                $schedule->client->notifyNow(new ScheduleUpdatedNotification($schedule));
            }
        } catch (Throwable $e) {
            Log::warning('Gagal mengirim notifikasi jadwal diperbarui: '.$e->getMessage());
        }

        return response()->json([
            'message' => 'Jadwal berhasil diperbarui.',
            'data' => new ScheduleResource($schedule),
        ]);
    }

    public function destroy(Schedule $schedule): JsonResponse
    {
        $schedule->delete();

        return response()->json([
            'message' => 'Jadwal berhasil dihapus.',
        ]);
    }

    public function checkIn(Request $request, Schedule $schedule): JsonResponse
    {
        if ($schedule->status !== 'scheduled') {
            return response()->json([
                'message' => 'Hanya jadwal dengan status scheduled yang bisa check-in.',
            ], 422);
        }

        $request->validate([
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = [
            'status' => 'in_progress',
            'checked_in_at' => now(),
            'checkin_lat' => $request->lat,
            'checkin_lng' => $request->lng,
        ];

        if ($request->hasFile('photo')) {
            $data['checkin_photo'] = $request->file('photo')->store('checkins', 'public');
        }

        $schedule->update($data);

        // Notify admins
        try {
            $admins = User::role('admin')->get();
            $admins->each(fn ($admin) => $admin->notifyNow(
                new AdminAlertNotification(
                    'Check-In',
                    $schedule->technician->name.' telah check-in di '.$schedule->client->company_name,
                    'info',
                    route('schedules.show', $schedule)
                )
            ));
        } catch (Throwable $e) {
            Log::warning('Gagal mengirim notifikasi check-in: '.$e->getMessage());
        }

        return response()->json([
            'message' => 'Check-in berhasil.',
            'data' => new ScheduleResource($schedule->fresh()->load(['client', 'technician'])),
        ]);
    }

    public function checkOut(Request $request, Schedule $schedule): JsonResponse
    {
        if ($schedule->status !== 'in_progress') {
            return response()->json([
                'message' => 'Hanya jadwal dengan status in_progress yang bisa check-out.',
            ], 422);
        }

        $request->validate([
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = [
            'status' => 'completed',
            'checked_out_at' => now(),
            'checkout_lat' => $request->lat,
            'checkout_lng' => $request->lng,
        ];

        if ($request->hasFile('photo')) {
            $data['checkout_photo'] = $request->file('photo')->store('checkouts', 'public');
        }

        $schedule->update($data);

        // Completing the schedule auto-completes its visit report (if one
        // exists yet) and emails the client — see ReportService.
        $schedule->loadMissing('visitReport');
        if ($schedule->visitReport) {
            app(ReportService::class)->syncStatusWithSchedule($schedule->visitReport);
        }

        // Notify admins
        try {
            $admins = User::role('admin')->get();
            $admins->each(fn ($admin) => $admin->notifyNow(
                new AdminAlertNotification(
                    'Check-Out',
                    $schedule->technician->name.' telah check-out dari '.$schedule->client->company_name,
                    'success',
                    route('schedules.show', $schedule)
                )
            ));
        } catch (Throwable $e) {
            Log::warning('Gagal mengirim notifikasi check-out: '.$e->getMessage());
        }

        return response()->json([
            'message' => 'Check-out berhasil.',
            'data' => new ScheduleResource($schedule->fresh()->load(['client', 'technician'])),
        ]);
    }

    public function cancel(Schedule $schedule): JsonResponse
    {
        $schedule->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Jadwal berhasil dibatalkan.',
            'data' => new ScheduleResource($schedule->fresh()->load(['client', 'technician'])),
        ]);
    }
}
