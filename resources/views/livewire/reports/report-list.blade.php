<div>
    <div class="page-header">
        <div>
            <h2 class="page-title"Laporan Kunjungan</h2>
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
                        <td data-label="No. Report"><span class="font-mono text-sm text-stone-600 dark:text-stone-400">{{ $report->report_number }}</span></td>
                        <td data-label="Klien" class="font-medium text-slate-900 dark:text-white">{{ $report->client->company_name }}</td>
                        <td data-label="Teknisi">{{ $report->technician->name }}</td>
                        <td data-label="Tanggal">{{ $report->schedule->visit_date->format('d M Y') }}</td>
                        <td data-label="Status"><span class="{{ $statusClasses[$report->status] ?? 'badge-gray' }}">{{ ucfirst($report->status) }}</span></td>
                        <td data-label="">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('reports.show', $report) }}" class="btn-secondary py-1 px-2.5 text-xs">Lihat</a>
                                <a href="{{ route('reports.edit', $report) }}" class="btn-secondary py-1 px-2.5 text-xs">Edit</a>
                                <a href="{{ route('pdf.report', $report) }}" target="_blank" class="btn-secondary py-1 px-2.5 text-xs">PDF</a>
                                @if($report->status !== 'draft')
                                <button wire:click="sendEmail({{ $report->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="sendEmail({{ $report->id }})"
                                        class="btn-secondary py-1 px-2.5 text-xs">
                                    <span wire:loading.remove wire:target="sendEmail({{ $report->id }})">Kirim</span>
                                    <span wire:loading wire:target="sendEmail({{ $report->id }})">...</span>
                                </button>
                                @php $rwaUrl = $report->getWhatsappUrl(); @endphp
                                @if($rwaUrl)
                                <a href="{{ $rwaUrl }}" target="_blank"
                                   class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-green-500 hover:bg-green-600 text-white transition-colors">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    WA
                                </a>
                                @endif
                                @endif
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
