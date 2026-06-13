<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">Clients</h2>
            <p class="page-subtitle">Kelola akun klien</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <button wire:click="export" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export
            </button>
            <a href="{{ route('clients.create') }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Klien
            </a>
        </div>
    </div>

    <div class="card">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex flex-wrap gap-3">
            <div class="flex-1 min-w-48">
                <input type="text" wire:model.live="search" placeholder="Cari nama perusahaan, PIC, atau email..." class="form-input">
            </div>
            <select wire:model.live="statusFilter" class="form-select w-36">
                <option value="">Semua Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <select wire:model.live="healthFilter" class="form-select w-36">
                <option value="">Semua Health</option>
                <option value="healthy">Healthy</option>
                <option value="warning">Warning</option>
                <option value="critical">Critical</option>
            </select>
        </div>

        <div class="table-wrapper rounded-none border-0">
            <table class="table">
                <thead>
                    <tr>
                        <th>Perusahaan</th>
                        <th>PIC</th>
                        <th>Retainer</th>
                        <th>Status</th>
                        <th>Health</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                    <tr>
                        <td data-label="Perusahaan">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center text-violet-700 dark:text-violet-300 font-semibold text-sm shrink-0">
                                    {{ strtoupper(substr($client->company_name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('clients.show', $client) }}" class="font-medium text-slate-900 dark:text-white hover:text-violet-600 dark:hover:text-violet-400 block truncate">
                                        {{ $client->company_name }}
                                    </a>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $client->address ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td data-label="PIC">
                            <div class="text-sm text-slate-900 dark:text-white">{{ $client->pic_name }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $client->pic_email }}</div>
                        </td>
                        <td data-label="Retainer" class="text-sm font-medium">Rp {{ number_format($client->monthly_retainer_fee, 0, ',', '.') }}</td>
                        <td data-label="Status">
                            @if ($client->is_active)
                                <span class="badge-green">Active</span>
                            @else
                                <span class="badge-gray">Inactive</span>
                            @endif
                        </td>
                        <td data-label="Health">
                            @php
                            $hc = ['healthy' => 'badge-green', 'warning' => 'badge-yellow', 'critical' => 'badge-critical'];
                            @endphp
                            <span class="{{ $hc[$client->health_status] ?? 'badge-gray' }}">
                                {{ ucfirst($client->health_status ?? 'unknown') }}
                                @if ($client->health_score !== null)({{ number_format($client->health_score) }})@endif
                            </span>
                        </td>
                        <td data-label="">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('clients.show', $client) }}" class="btn-secondary py-1 px-2.5 text-xs">Lihat</a>
                                <a href="{{ route('clients.edit', $client) }}" class="btn-secondary py-1 px-2.5 text-xs">Edit</a>
                                <button wire:click="confirmDelete({{ $client->id }})" class="btn-danger py-1 px-2.5 text-xs">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="6" class="py-16 text-center">
                            <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <p class="text-slate-500 dark:text-slate-400 font-medium">Tidak ada klien</p>
                            <a href="{{ route('clients.create') }}" class="mt-3 btn-primary inline-flex">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Tambah Klien Pertama
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($clients->hasPages())
        <div class="border-t border-slate-200 dark:border-slate-700 px-4 py-4">{{ $clients->links() }}</div>
        @endif
    </div>

    @if ($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data @keydown.escape.window="$wire.cancelDelete()">
        <div class="card p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">Hapus Klien</h3>
            <p class="text-slate-600 dark:text-slate-300 text-sm mb-6">Yakin mau hapus klien ini? Semua data terkait akan di-soft delete.</p>
            <div class="flex items-center justify-end gap-3">
                <button wire:click="cancelDelete" class="btn-secondary">Batal</button>
                <button wire:click="deleteClient" wire:loading.attr="disabled" class="btn-danger">
                    <span wire:loading.remove wire:target="deleteClient">Hapus Klien</span>
                    <span wire:loading wire:target="deleteClient">Menghapus...</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
