<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">Invoices</h2>
            <p class="page-subtitle">Belum dibayar: <span class="font-bold text-red-600 dark:text-red-400">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</span></p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <button wire:click="$set('showRetainerModal', true)" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Generate Retainer
            </button>
            <a href="{{ route('invoices.create') }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Invoice Baru
            </a>
        </div>
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
            <select wire:model.live="typeFilter" class="form-select w-32">
                <option value="">Semua Tipe</option>
                <option value="retainer">Retainer</option>
                <option value="quotation">Quotation</option>
                <option value="manual">Manual</option>
            </select>
            <select wire:model.live="statusFilter" class="form-select w-32">
                <option value="">Semua Status</option>
                <option value="draft">Draft</option>
                <option value="sent">Sent</option>
                <option value="paid">Paid</option>
                <option value="overdue">Overdue</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <div class="table-wrapper rounded-none border-0">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Invoice</th>
                        <th>Klien</th>
                        <th>Tipe</th>
                        <th>Tanggal</th>
                        <th>Jatuh Tempo</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    @php $sc = ['draft' => 'badge-gray', 'sent' => 'badge-blue', 'paid' => 'badge-green', 'overdue' => 'badge-red', 'cancelled' => 'badge-gray']; @endphp
                    <tr>
                        <td data-label="Invoice"><span class="font-mono text-sm text-violet-600 dark:text-violet-400">{{ $invoice->invoice_number }}</span></td>
                        <td data-label="Klien" class="font-medium text-slate-900 dark:text-white">{{ $invoice->client->company_name }}</td>
                        <td data-label="Tipe"><span class="badge-gray">{{ ucfirst($invoice->type) }}</span></td>
                        <td data-label="Tanggal" class="text-sm">{{ $invoice->invoice_date->format('d M Y') }}</td>
                        <td data-label="Jatuh Tempo" class="text-sm {{ $invoice->isOverdue() ? 'text-red-500 font-semibold' : '' }}">{{ $invoice->due_date->format('d M Y') }}</td>
                        <td data-label="Jumlah" class="font-semibold">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                        <td data-label="Status"><span class="{{ $sc[$invoice->status] ?? 'badge-gray' }}">{{ ucfirst($invoice->status) }}</span></td>
                        <td data-label="">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('invoices.show', $invoice) }}" class="btn-secondary py-1 px-2.5 text-xs">Lihat</a>
                                <a href="{{ route('pdf.invoice', $invoice) }}" target="_blank" class="btn-secondary py-1 px-2.5 text-xs">PDF</a>
                                @if($invoice->status !== 'draft')
                                <button wire:click="resendEmail({{ $invoice->id }})" wire:loading.attr="disabled" wire:target="resendEmail({{ $invoice->id }})" class="btn-secondary py-1 px-2.5 text-xs">
                                    <span wire:loading.remove wire:target="resendEmail({{ $invoice->id }})">Email</span>
                                    <span wire:loading wire:target="resendEmail({{ $invoice->id }})">...</span>
                                </button>
                                @php $waUrl = $invoice->getWhatsappUrl(); @endphp
                                @if($waUrl)
                                <a href="{{ $waUrl }}" target="_blank" class="inline-flex items-center gap-1 py-1 px-2.5 text-xs rounded-lg font-medium bg-green-500 hover:bg-green-600 text-white transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    WA
                                </a>
                                @endif
                                @endif
                                <button wire:click="confirmDelete({{ $invoice->id }})" class="btn-danger py-1 px-2.5 text-xs">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="8" class="py-12 text-center text-slate-500 dark:text-slate-400 text-sm">Tidak ada invoice</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-700">{{ $invoices->links() }}</div>
        @endif
    </div>

    @if($deleteId)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="card p-6 w-full max-w-sm mx-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">Hapus Invoice</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Invoice ini akan dihapus permanen.</p>
            <div class="flex gap-3 justify-end">
                <button wire:click="$set('deleteId', null)" class="btn-secondary">Batal</button>
                <button wire:click="deleteInvoice" class="btn-danger">Hapus</button>
            </div>
        </div>
    </div>
    @endif

    @if($showRetainerModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="card p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-1">Generate Retainer Invoice</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-5">Buat invoice retainer untuk klien terpilih bulan <strong>{{ now()->format('F Y') }}</strong>.</p>
            <div class="mb-5">
                <label class="form-label">Klien <span class="text-red-500">*</span></label>
                <select wire:model="retainerClientId" class="form-select">
                    <option value="0">— Pilih klien —</option>
                    @foreach($clients->where('monthly_retainer_fee', '>', 0) as $c)
                    <option value="{{ $c->id }}">{{ $c->company_name }} — Rp {{ number_format($c->monthly_retainer_fee, 0, ',', '.') }}/bln</option>
                    @endforeach
                </select>
                @error('retainerClientId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-3 justify-end">
                <button wire:click="$set('showRetainerModal', false)" class="btn-secondary">Batal</button>
                <button wire:click="generateRetainerInvoice" wire:loading.attr="disabled" class="btn-primary">
                    <span wire:loading.remove wire:target="generateRetainerInvoice">Generate</span>
                    <span wire:loading wire:target="generateRetainerInvoice">Memproses...</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
