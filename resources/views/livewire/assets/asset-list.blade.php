<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">Assets</h2>
            <p class="page-subtitle">Kelola hardware dan perangkat klien</p>
        </div>
        <a href="{{ route('assets.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Asset
        </a>
    </div>

    <div class="card">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex flex-wrap gap-3">
            <div class="flex-1 min-w-48">
                <input wire:model.live="search" type="search" placeholder="Cari nama, kode, atau brand..." class="form-input">
            </div>
            <select wire:model.live="clientFilter" class="form-select w-48">
                <option value="">Semua Klien</option>
                @foreach($clients as $client)
                <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                @endforeach
            </select>
            <select wire:model.live="typeFilter" class="form-select w-40">
                <option value="">Semua Tipe</option>
                @foreach($assetTypes as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="healthFilter" class="form-select w-36">
                <option value="">Semua Health</option>
                <option value="good">Good</option>
                <option value="fair">Fair</option>
                <option value="poor">Poor</option>
                <option value="critical">Critical</option>
            </select>
        </div>

        <div class="table-wrapper rounded-none border-0">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Asset</th>
                        <th>Tipe</th>
                        <th>Klien</th>
                        <th>Brand / Model</th>
                        <th>Lokasi</th>
                        <th>Health</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                    <tr>
                        <td data-label="Kode"><span class="font-mono text-xs text-stone-600 dark:text-stone-400">{{ $asset->asset_code }}</span></td>
                        <td data-label="Nama">
                            <a href="{{ route('assets.show', $asset) }}" class="font-medium text-slate-900 dark:text-white hover:text-stone-600 dark:hover:text-stone-400">
                                {{ $asset->asset_name }}
                            </a>
                        </td>
                        <td data-label="Tipe">{{ $asset->getTypeLabel() }}</td>
                        <td data-label="Klien">
                            <a href="{{ route('clients.show', $asset->client) }}" class="text-stone-600 dark:text-stone-400 hover:underline text-sm">
                                {{ $asset->client->company_name }}
                            </a>
                        </td>
                        <td data-label="Brand">{{ $asset->brand }} {{ $asset->model }}</td>
                        <td data-label="Lokasi">{{ $asset->location ?? '-' }}</td>
                        <td data-label="Health">
                            @php $healthClasses = ['good' => 'badge-green', 'fair' => 'badge-yellow', 'poor' => 'badge-medium', 'critical' => 'badge-critical']; @endphp
                            <span class="{{ $healthClasses[$asset->health_status] ?? 'badge-gray' }}">{{ ucfirst($asset->health_status) }}</span>
                        </td>
                        <td data-label="">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('assets.show', $asset) }}" class="btn-secondary py-1 px-2.5 text-xs">Lihat</a>
                                <a href="{{ route('assets.edit', $asset) }}" class="btn-secondary py-1 px-2.5 text-xs">Edit</a>
                                <button wire:click="confirmDelete({{ $asset->id }})" class="btn-danger py-1 px-2.5 text-xs">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="8" class="py-12 text-center">
                            <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                            <p class="text-slate-500 dark:text-slate-400 text-sm">Tidak ada asset</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assets->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-700">{{ $assets->links() }}</div>
        @endif
    </div>

    @if($deleteId)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="card p-6 w-full max-w-sm mx-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">Hapus Asset</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Yakin? Asset dan seluruh histori-nya akan dihapus.</p>
            <div class="flex gap-3 justify-end">
                <button wire:click="$set('deleteId', null)" class="btn-secondary">Batal</button>
                <button wire:click="deleteAsset" class="btn-danger">Hapus</button>
            </div>
        </div>
    </div>
    @endif
</div>
