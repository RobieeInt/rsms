<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">Quotations</h2>
            <p class="page-subtitle">Kelola penawaran layanan</p>
        </div>
        <a href="{{ route('quotations.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Quotation Baru
        </a>
    </div>

    <div class="card">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex flex-wrap gap-3">
            <div class="flex-1 min-w-48">
                <input wire:model.live="search" type="search" placeholder="Cari nomor atau klien..." class="form-input">
            </div>
            <select wire:model.live="clientFilter" class="form-select w-44">
                <option value="">Semua Klien</option>
                @foreach($clients as $client)
                <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                @endforeach
            </select>
            <select wire:model.live="statusFilter" class="form-select w-32">
                <option value="">Semua Status</option>
                <option value="draft">Draft</option>
                <option value="sent">Sent</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>

        <div class="table-wrapper rounded-none border-0">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Quotation</th>
                        <th>Klien</th>
                        <th>Tanggal</th>
                        <th>Berlaku s/d</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotations as $quo)
                    @php $sc = ['draft' => 'badge-gray', 'sent' => 'badge-blue', 'approved' => 'badge-green', 'rejected' => 'badge-red']; @endphp
                    <tr>
                        <td data-label="Quotation"><span class="font-mono text-sm text-stone-600 dark:text-stone-400">{{ $quo->quotation_number }}</span></td>
                        <td data-label="Klien" class="font-medium text-slate-900 dark:text-white">{{ $quo->client->company_name }}</td>
                        <td data-label="Tanggal" class="text-sm">{{ $quo->date->format('d M Y') }}</td>
                        <td data-label="Berlaku" class="text-sm {{ $quo->expiry_date->isPast() && $quo->status === 'sent' ? 'text-red-500' : '' }}">{{ $quo->expiry_date->format('d M Y') }}</td>
                        <td data-label="Jumlah" class="font-semibold">Rp {{ number_format($quo->total_amount, 0, ',', '.') }}</td>
                        <td data-label="Status"><span class="{{ $sc[$quo->status] ?? 'badge-gray' }}">{{ ucfirst($quo->status) }}</span></td>
                        <td data-label="">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('quotations.show', $quo) }}" class="btn-secondary py-1 px-2.5 text-xs">Lihat</a>
                                <a href="{{ route('quotations.edit', $quo) }}" class="btn-secondary py-1 px-2.5 text-xs">Edit</a>
                                <a href="{{ route('pdf.quotation', $quo) }}" target="_blank" class="btn-secondary py-1 px-2.5 text-xs">PDF</a>
                                <button wire:click="confirmDelete({{ $quo->id }})" class="btn-danger py-1 px-2.5 text-xs">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="7" class="py-12 text-center text-slate-500 dark:text-slate-400 text-sm">Tidak ada quotation</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quotations->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-700">{{ $quotations->links() }}</div>
        @endif
    </div>

    @if($deleteId)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="card p-6 w-full max-w-sm mx-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">Hapus Quotation</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Quotation dan semua item-nya akan dihapus permanen.</p>
            <div class="flex gap-3 justify-end">
                <button wire:click="$set('deleteId', null)" class="btn-secondary">Batal</button>
                <button wire:click="deleteQuotation" class="btn-danger">Hapus</button>
            </div>
        </div>
    </div>
    @endif
</div>
