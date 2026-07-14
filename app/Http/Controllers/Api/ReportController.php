<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VisitReportResource;
use App\Models\AssetChecklist;
use App\Models\NetworkChecklist;
use App\Models\Schedule;
use App\Models\User;
use App\Models\VisitPhoto;
use App\Models\VisitReport;
use App\Notifications\AdminAlertNotification;
use App\Notifications\VisitReportSentNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReportController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $query = VisitReport::with(['client', 'technician', 'schedule']);

        if ($user->hasRole('technician')) {
            $query->where('technician_id', $user->id);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('report_number', 'like', "%{$search}%")
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

        $reports = $query->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        return VisitReportResource::collection($reports);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'schedule_id' => ['required', 'exists:schedules,id'],
            'summary' => ['nullable', 'string'],
            'overall_notes' => ['nullable', 'string'],
            'client_signed_by' => ['nullable', 'string'],
            'technician_signature' => ['nullable', 'string'],
            'client_signature' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,completed'],
            'selected_asset_ids' => ['nullable', 'array'],
            'asset_checklists' => ['nullable', 'array'],
            'network_checklist' => ['nullable', 'array'],
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $schedule = Schedule::findOrFail($validated['schedule_id']);

            $report = VisitReport::create([
                'schedule_id' => $schedule->id,
                'client_id' => $schedule->client_id,
                'technician_id' => $request->user()->id,
                'report_number' => VisitReport::generateNumber(),
                'summary' => $validated['summary'] ?? null,
                'overall_notes' => $validated['overall_notes'] ?? null,
                'client_signed_by' => $validated['client_signed_by'] ?? null,
                'technician_signature' => $validated['technician_signature'] ?? null,
                'client_signature' => $validated['client_signature'] ?? null,
                'status' => $validated['status'] ?? 'draft',
            ]);

            // Save asset checklists
            if (! empty($validated['asset_checklists'])) {
                foreach ($validated['asset_checklists'] as $checklistData) {
                    if (isset($checklistData['asset_id'])) {
                        AssetChecklist::updateOrCreate(
                            [
                                'visit_report_id' => $report->id,
                                'asset_id' => $checklistData['asset_id'],
                            ],
                            array_filter([
                                'template_id' => $checklistData['template_id'] ?? null,
                                'results' => $checklistData['results'] ?? null,
                                'storage_check' => $checklistData['storage_check'] ?? null,
                                'storage_check_notes' => $checklistData['storage_check_notes'] ?? null,
                                'ram_check' => $checklistData['ram_check'] ?? null,
                                'ram_check_notes' => $checklistData['ram_check_notes'] ?? null,
                                'temp_files_cleanup' => $checklistData['temp_files_cleanup'] ?? null,
                                'temp_files_cleanup_notes' => $checklistData['temp_files_cleanup_notes'] ?? null,
                                'ssd_health_check' => $checklistData['ssd_health_check'] ?? null,
                                'ssd_health_check_notes' => $checklistData['ssd_health_check_notes'] ?? null,
                                'windows_update_check' => $checklistData['windows_update_check'] ?? null,
                                'windows_update_check_notes' => $checklistData['windows_update_check_notes'] ?? null,
                                'driver_check' => $checklistData['driver_check'] ?? null,
                                'driver_check_notes' => $checklistData['driver_check_notes'] ?? null,
                                'virus_scan' => $checklistData['virus_scan'] ?? null,
                                'virus_scan_notes' => $checklistData['virus_scan_notes'] ?? null,
                                'printer_check' => $checklistData['printer_check'] ?? null,
                                'printer_check_notes' => $checklistData['printer_check_notes'] ?? null,
                                'hardware_cleaning' => $checklistData['hardware_cleaning'] ?? null,
                                'hardware_cleaning_notes' => $checklistData['hardware_cleaning_notes'] ?? null,
                                'general_notes' => $checklistData['general_notes'] ?? null,
                            ], fn ($v) => $v !== null)
                        );
                    }
                }
            }

            // Save network checklist
            if (! empty($validated['network_checklist'])) {
                $nc = $validated['network_checklist'];
                NetworkChecklist::updateOrCreate(
                    ['visit_report_id' => $report->id],
                    array_filter([
                        'internet_connectivity' => $nc['internet_connectivity'] ?? null,
                        'internet_connectivity_notes' => $nc['internet_connectivity_notes'] ?? null,
                        'speed_test' => $nc['speed_test'] ?? null,
                        'speed_test_notes' => $nc['speed_test_notes'] ?? null,
                        'download_speed' => $nc['download_speed'] ?? null,
                        'upload_speed' => $nc['upload_speed'] ?? null,
                        'router_check' => $nc['router_check'] ?? null,
                        'router_check_notes' => $nc['router_check_notes'] ?? null,
                        'lan_cable_check' => $nc['lan_cable_check'] ?? null,
                        'lan_cable_check_notes' => $nc['lan_cable_check_notes'] ?? null,
                        'ip_conflict_check' => $nc['ip_conflict_check'] ?? null,
                        'ip_conflict_check_notes' => $nc['ip_conflict_check_notes'] ?? null,
                        'general_notes' => $nc['general_notes'] ?? null,
                    ], fn ($v) => $v !== null)
                );
            }

            // Save photos if uploaded
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('visit-photos', 'public');
                    VisitPhoto::create([
                        'visit_report_id' => $report->id,
                        'uploaded_by' => $request->user()->id,
                        'file_path' => $path,
                        'original_name' => $photo->getClientOriginalName(),
                        'photo_type' => $request->input('photo_type', 'general'),
                    ]);
                }
            }

            // If completed, update schedule and notify admins
            if (($validated['status'] ?? 'draft') === 'completed') {
                $schedule->update(['status' => 'completed']);

                if (! $request->user()->hasRole('admin')) {
                    try {
                        $admins = User::role('admin')->get();
                        $admins->each(fn ($admin) => $admin->notifyNow(
                            new AdminAlertNotification(
                                'Laporan Dikirim',
                                $request->user()->name.' mengirim laporan kunjungan '.$report->report_number,
                                'info',
                                route('reports.show', $report)
                            )
                        ));
                    } catch (Throwable $e) {
                        Log::warning('Gagal mengirim notifikasi laporan: '.$e->getMessage());
                    }
                }
            }

            return response()->json([
                'message' => 'Laporan berhasil disimpan.',
                'data' => new VisitReportResource($report->load(['client', 'technician', 'assetChecklists', 'networkChecklist', 'photos'])),
            ], 201);
        });
    }

    public function show(VisitReport $report): VisitReportResource
    {
        $report->load([
            'client', 'technician', 'schedule',
            'assetChecklists.asset', 'assetChecklists.template',
            'networkChecklist', 'findings', 'photos.uploader',
        ]);

        return new VisitReportResource($report);
    }

    public function update(Request $request, VisitReport $report): JsonResponse
    {
        $validated = $request->validate([
            'summary' => ['nullable', 'string'],
            'overall_notes' => ['nullable', 'string'],
            'client_signed_by' => ['nullable', 'string'],
            'technician_signature' => ['nullable', 'string'],
            'client_signature' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,completed,signed'],
            'asset_checklists' => ['nullable', 'array'],
            'network_checklist' => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($validated, $request, $report) {
            $report->update(collect($validated)->only([
                'summary', 'overall_notes', 'client_signed_by',
                'technician_signature', 'client_signature', 'status',
            ])->toArray());

            if (! empty($validated['asset_checklists'])) {
                foreach ($validated['asset_checklists'] as $checklistData) {
                    if (isset($checklistData['asset_id'])) {
                        AssetChecklist::updateOrCreate(
                            [
                                'visit_report_id' => $report->id,
                                'asset_id' => $checklistData['asset_id'],
                            ],
                            array_filter([
                                'template_id' => $checklistData['template_id'] ?? null,
                                'results' => $checklistData['results'] ?? null,
                                'storage_check' => $checklistData['storage_check'] ?? null,
                                'storage_check_notes' => $checklistData['storage_check_notes'] ?? null,
                                'ram_check' => $checklistData['ram_check'] ?? null,
                                'ram_check_notes' => $checklistData['ram_check_notes'] ?? null,
                                'temp_files_cleanup' => $checklistData['temp_files_cleanup'] ?? null,
                                'temp_files_cleanup_notes' => $checklistData['temp_files_cleanup_notes'] ?? null,
                                'ssd_health_check' => $checklistData['ssd_health_check'] ?? null,
                                'ssd_health_check_notes' => $checklistData['ssd_health_check_notes'] ?? null,
                                'windows_update_check' => $checklistData['windows_update_check'] ?? null,
                                'windows_update_check_notes' => $checklistData['windows_update_check_notes'] ?? null,
                                'driver_check' => $checklistData['driver_check'] ?? null,
                                'driver_check_notes' => $checklistData['driver_check_notes'] ?? null,
                                'virus_scan' => $checklistData['virus_scan'] ?? null,
                                'virus_scan_notes' => $checklistData['virus_scan_notes'] ?? null,
                                'printer_check' => $checklistData['printer_check'] ?? null,
                                'printer_check_notes' => $checklistData['printer_check_notes'] ?? null,
                                'hardware_cleaning' => $checklistData['hardware_cleaning'] ?? null,
                                'hardware_cleaning_notes' => $checklistData['hardware_cleaning_notes'] ?? null,
                                'general_notes' => $checklistData['general_notes'] ?? null,
                            ], fn ($v) => $v !== null)
                        );
                    }
                }
            }

            if (! empty($validated['network_checklist'])) {
                $nc = $validated['network_checklist'];
                NetworkChecklist::updateOrCreate(
                    ['visit_report_id' => $report->id],
                    array_filter([
                        'internet_connectivity' => $nc['internet_connectivity'] ?? null,
                        'internet_connectivity_notes' => $nc['internet_connectivity_notes'] ?? null,
                        'speed_test' => $nc['speed_test'] ?? null,
                        'speed_test_notes' => $nc['speed_test_notes'] ?? null,
                        'download_speed' => $nc['download_speed'] ?? null,
                        'upload_speed' => $nc['upload_speed'] ?? null,
                        'router_check' => $nc['router_check'] ?? null,
                        'router_check_notes' => $nc['router_check_notes'] ?? null,
                        'lan_cable_check' => $nc['lan_cable_check'] ?? null,
                        'lan_cable_check_notes' => $nc['lan_cable_check_notes'] ?? null,
                        'ip_conflict_check' => $nc['ip_conflict_check'] ?? null,
                        'ip_conflict_check_notes' => $nc['ip_conflict_check_notes'] ?? null,
                        'general_notes' => $nc['general_notes'] ?? null,
                    ], fn ($v) => $v !== null)
                );
            }

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('visit-photos', 'public');
                    VisitPhoto::create([
                        'visit_report_id' => $report->id,
                        'uploaded_by' => $request->user()->id,
                        'file_path' => $path,
                        'original_name' => $photo->getClientOriginalName(),
                        'photo_type' => $request->input('photo_type', 'general'),
                    ]);
                }
            }
        });

        return response()->json([
            'message' => 'Laporan berhasil diperbarui.',
            'data' => new VisitReportResource($report->fresh()->load([
                'client', 'technician', 'assetChecklists.asset',
                'assetChecklists.template', 'networkChecklist', 'photos.uploader',
            ])),
        ]);
    }

    public function destroy(VisitReport $report): JsonResponse
    {
        $report->delete();

        return response()->json([
            'message' => 'Laporan berhasil dihapus.',
        ]);
    }

    public function sendEmail(VisitReport $report): JsonResponse
    {
        if (! $report->client || ! $report->client->pic_email) {
            return response()->json([
                'message' => 'Client tidak memiliki email PIC.',
            ], 422);
        }

        $report->load(['client', 'technician', 'assetChecklists', 'networkChecklist', 'photos']);
        $report->client->notifyNow(new VisitReportSentNotification($report));

        return response()->json([
            'message' => 'Email laporan berhasil dikirim ke '.$report->client->pic_email,
        ]);
    }
}
