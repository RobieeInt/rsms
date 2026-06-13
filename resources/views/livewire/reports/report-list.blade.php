<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">Visit Reports</h2>
            <p class="page-subtitle">Semua laporan kunjungan maintenance</p>
        </div>
    </div>

    <div class="card">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex flex-wrap gap-3">
            <div class="flex-1 min-w-48">
                <input wire:model.live="search" type="search" placeholder="Cari nomor report atau klien..." class="form-input">
            </div>
            @if(auth()->user()->hasRole('admin'))
            <select wire:model.live="clientFilter" class="form-select w-44">
                <option value="">Semua Klien</option>
                @foreach($clients as $client)
                <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                @endforeach
            </select>
            @endif
            <select wire:model.live="statusFilter" class="form-select w-36">
                <option value="">Semua Status</option>
                <option value="draft">Draft</option>
                <option value="completed">Completed</option>
                <option value="signed">Signed</option>
            </select>
        </div>

        <div class="table-wrapper rounded-none border-0">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Report</th>
                        <th>Klien</th>
                        <th>Teknisi</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                    @php $statusClasses = ['draft' => 'badge-yellow', 'completed' => 'badge-blue', 'signed' => 'badge-green']; @endphp
                    <tr>
                        <td data-label="No. Report"><span class="font-mono text-sm text-violet-600 dark:text-violet-400">{{ $report->report_number }}</span></td>
                        <td data-label="Klien" class="font-medium text-slate-900 dark:text-white">{{ $report->client->company_name }}</td>
                        <td data-label="Teknisi">{{ $report->technician->name }}</td>
                        <td data-label="Tanggal">{{ $report->schedule->visit_date->format('d M Y') }}</td>
                        <td data-label="Status"><span class="{{ $statusClasses[$report->status] ?? 'badge-gray' }}">{{ ucfirst($report->status) }}</span></td>
                        <td data-label="">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('reports.show', $report) }}" class="btn-secondary py-1 px-2.5 text-xs">Lihat</a>
                                <a href="{{ route('reports.edit', $report) }}" class="btn-secondary py-1 px-2.5 text-xs">Edit</a>
                                <a href="{{ route('pdf.report', $report) }}" target="_blank" class="btn-secondary py-1 px-2.5 text-xs">PDF</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="6" class="py-12 text-center">
                            <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-slate-500 dark:text-slate-400 text-sm">Tidak ada report</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reports->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-700">{{ $reports->links() }}</div>
        @endif
    </div>
</div>
