<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">Schedules</h2>
            <p class="page-subtitle">Manage maintenance visit schedules</p>
        </div>
        @if(auth()->user()->hasAnyRole(['admin', 'technician']))
        <a href="{{ route('schedules.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Jadwal
        </a>
        @endif
    </div>

    <div class="card">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex flex-wrap gap-3">
            <div class="flex-1 min-w-48">
                <input wire:model.live="search" type="search" placeholder="Cari klien..." class="form-input">
            </div>
            <select wire:model.live="statusFilter" class="form-select w-36">
                <option value="">Semua Status</option>
                <option value="scheduled">Scheduled</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
            @if(auth()->user()->hasRole('admin'))
            <select wire:model.live="clientFilter" class="form-select w-44">
                <option value="">Semua Klien</option>
                @foreach($clients as $client)
                <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                @endforeach
            </select>
            <select wire:model.live="technicianFilter" class="form-select w-40">
                <option value="">Semua Teknisi</option>
                @foreach($technicians as $tech)
                <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                @endforeach
            </select>
            @endif
            <input wire:model.live="dateFrom" type="date" class="form-input w-36">
            <input wire:model.live="dateTo" type="date" class="form-input w-36">
        </div>

        <div class="table-wrapper rounded-none border-0">
            <table class="table">
                <thead>
                    <tr>
                        <th>Klien</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Teknisi</th>
                        <th>Status</th>
                        <th>Report</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $schedule)
                    @php
                    $statusClasses = [
                        'scheduled'   => 'badge-blue',
                        'in_progress' => 'badge-yellow',
                        'completed'   => 'badge-green',
                        'cancelled'   => 'badge-gray',
                    ];
                    @endphp
                    <tr>
                        <td data-label="Klien">
                            <a href="{{ route('clients.show', $schedule->client) }}" class="font-medium text-slate-900 dark:text-white hover:text-stone-600 dark:hover:text-stone-400">
                                {{ $schedule->client->company_name }}
                            </a>
                        </td>
                        <td data-label="Tanggal">{{ $schedule->visit_date->format('d M Y') }}</td>
                        <td data-label="Waktu">{{ $schedule->start_time }}{{ $schedule->end_time ? ' - ' . $schedule->end_time : '' }}</td>
                        <td data-label="Teknisi">{{ $schedule->technician->name }}</td>
                        <td data-label="Status"><span class="{{ $statusClasses[$schedule->status] ?? 'badge-gray' }}">{{ ucfirst(str_replace('_', ' ', $schedule->status)) }}</span></td>
                        <td data-label="Report">
                            @if($schedule->visitReport)
                            <a href="{{ route('reports.show', $schedule->visitReport) }}" class="text-xs text-stone-600 dark:text-stone-400 hover:underline">
                                {{ $schedule->visitReport->report_number }}
                            </a>
                            @elseif($schedule->status !== 'cancelled')
                            <a href="{{ route('reports.create', $schedule) }}" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline">Buat Report</a>
                            @else
                            <span class="text-slate-400 text-xs">-</span>
                            @endif
                        </td>
                        <td data-label="">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('schedules.show', $schedule) }}" class="btn-secondary py-1 px-2.5 text-xs">Lihat</a>
                                @if(auth()->user()->hasRole('admin'))
                                <a href="{{ route('schedules.edit', $schedule) }}" class="btn-secondary py-1 px-2.5 text-xs">Edit</a>
                                <button wire:click="confirmDelete({{ $schedule->id }})" class="btn-danger py-1 px-2.5 text-xs">Hapus</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="7" class="py-12 text-center">
                            <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p class="text-slate-500 dark:text-slate-400 text-sm">Tidak ada jadwal</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($schedules->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-700">{{ $schedules->links() }}</div>
        @endif
    </div>

    @if($deleteId)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="card p-6 w-full max-w-sm mx-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">Hapus Jadwal</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Yakin mau hapus jadwal ini?</p>
            <div class="flex gap-3 justify-end">
                <button wire:click="$set('deleteId', null)" class="btn-secondary">Batal</button>
                <button wire:click="deleteSchedule" class="btn-danger">Hapus</button>
            </div>
        </div>
    </div>
    @endif
</div>
