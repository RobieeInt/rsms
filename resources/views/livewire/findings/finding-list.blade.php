<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">Findings</h2>
            <p class="page-subtitle">Pantau semua temuan teknis</p>
        </div>
        <a href="{{ route('findings.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Finding
        </a>
    </div>

    <div class="card">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex flex-wrap gap-3">
            <div class="flex-1 min-w-48">
                <input wire:model.live="search" type="search" placeholder="Cari finding..." class="form-input">
            </div>
            @if(auth()->user()->hasRole('admin'))
            <select wire:model.live="clientFilter" class="form-select w-44">
                <option value="">Semua Klien</option>
                @foreach($clients as $client)
                <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                @endforeach
            </select>
            @endif
            <select wire:model.live="severityFilter" class="form-select w-32">
                <option value="">Semua Severity</option>
                <option value="critical">Critical</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select>
            <select wire:model.live="statusFilter" class="form-select w-32">
                <option value="">Semua Status</option>
                <option value="open">Open</option>
                <option value="monitoring">Monitoring</option>
                <option value="resolved">Resolved</option>
            </select>
        </div>

        <div class="table-wrapper rounded-none border-0">
            <table class="table">
                <thead>
                    <tr>
                        <th>Finding</th>
                        <th>Klien</th>
                        <th>Aset</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($findings as $finding)
                    <tr>
                        <td data-label="Finding">
                            <div>
                                <a href="{{ route('findings.show', $finding) }}" class="font-medium text-slate-900 dark:text-white hover:text-stone-600 dark:hover:text-stone-400">
                                    {{ $finding->title }}
                                </a>
                                @if($finding->category)
                                <span class="badge-gray ml-1">{{ $finding->category }}</span>
                                @endif
                            </div>
                        </td>
                        <td data-label="Klien" class="text-sm">{{ $finding->client->company_name }}</td>
                        <td data-label="Aset" class="text-sm">{{ $finding->asset?->asset_name ?? '-' }}</td>
                        <td data-label="Severity"><span class="{{ $finding->getSeverityBadgeClass() }}">{{ ucfirst($finding->severity) }}</span></td>
                        <td data-label="Status">
                            @php $sc = ['open' => 'badge-red', 'monitoring' => 'badge-yellow', 'resolved' => 'badge-green']; @endphp
                            <span class="{{ $sc[$finding->status] ?? 'badge-gray' }}">{{ ucfirst($finding->status) }}</span>
                        </td>
                        <td data-label="Tanggal" class="text-sm text-slate-500 dark:text-slate-400">{{ $finding->created_at->format('d M Y') }}</td>
                        <td data-label="">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('findings.show', $finding) }}" class="btn-secondary py-1 px-2.5 text-xs">Lihat</a>
                                <a href="{{ route('findings.edit', $finding) }}" class="btn-secondary py-1 px-2.5 text-xs">Edit</a>
                                <button wire:click="confirmDelete({{ $finding->id }})" class="btn-danger py-1 px-2.5 text-xs">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="7" class="py-12 text-center">
                            <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-slate-500 dark:text-slate-400 text-sm">Tidak ada finding</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($findings->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-700">{{ $findings->links() }}</div>
        @endif
    </div>

    @if($deleteId)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="card p-6 w-full max-w-sm mx-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">Hapus Finding</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Hapus finding ini beserta semua rekomendasinya?</p>
            <div class="flex gap-3 justify-end">
                <button wire:click="$set('deleteId', null)" class="btn-secondary">Batal</button>
                <button wire:click="deleteFinding" class="btn-danger">Hapus</button>
            </div>
        </div>
    </div>
    @endif
</div>
