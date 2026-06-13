<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">Technicians</h2>
            <p class="page-subtitle">Kelola tim teknisi IT</p>
        </div>
        <a href="{{ route('technicians.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Teknisi
        </a>
    </div>

    <div class="card">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex flex-wrap gap-3">
            <div class="flex-1 min-w-48">
                <input wire:model.live="search" type="search" placeholder="Cari nama atau email..." class="form-input">
            </div>
            <select wire:model.live="statusFilter" class="form-select w-40">
                <option value="">Semua Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>

        <div class="table-wrapper rounded-none border-0">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Posisi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($technicians as $tech)
                    <tr>
                        <td data-label="Nama">
                            <div class="flex items-center gap-3">
                                <img src="{{ $tech->avatar_url }}" class="w-8 h-8 rounded-full shrink-0" alt="">
                                <span class="font-medium text-slate-900 dark:text-white">{{ $tech->name }}</span>
                            </div>
                        </td>
                        <td data-label="Email">{{ $tech->email }}</td>
                        <td data-label="Telepon">{{ $tech->phone ?? '-' }}</td>
                        <td data-label="Posisi">{{ $tech->position ?? '-' }}</td>
                        <td data-label="Status">
                            @if($tech->is_active)
                                <span class="badge-green">Active</span>
                            @else
                                <span class="badge-gray">Inactive</span>
                            @endif
                        </td>
                        <td data-label="">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('technicians.edit', $tech) }}" class="btn-secondary py-1 px-2.5 text-xs">Edit</a>
                                <button wire:click="confirmDelete({{ $tech->id }})" class="btn-danger py-1 px-2.5 text-xs">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="6" class="py-12 text-center">
                            <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <p class="text-slate-500 dark:text-slate-400 text-sm">Tidak ada teknisi</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($technicians->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-700">{{ $technicians->links() }}</div>
        @endif
    </div>

    @if($deleteId)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="$set('deleteId', null)">
        <div class="card p-6 w-full max-w-sm mx-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">Hapus Teknisi</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Yakin mau hapus teknisi ini? Tidak bisa dibatalkan.</p>
            <div class="flex gap-3 justify-end">
                <button wire:click="$set('deleteId', null)" class="btn-secondary">Batal</button>
                <button wire:click="deleteTechnician" class="btn-danger">Hapus</button>
            </div>
        </div>
    </div>
    @endif
</div>
