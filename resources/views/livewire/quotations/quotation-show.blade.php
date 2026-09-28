<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">{{ $quotation->quotation_number }}</h2>
            <p class="page-subtitle">{{ $quotation->client->company_name }}</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            @if(in_array($quotation->status, ['draft']))
            <button wire:click="send" class="btn-primary">Send to Client</button>
            @endif
            @if($quotation->status === 'approved' && $quotation->remainingBalance() > 0)
            <button wire:click="openInvoiceModal" class="btn-success">
                @php $nextTerm = $quotation->nextTerm(); @endphp
                {{ $quotation->invoices->isEmpty() ? 'Convert to Invoice' : ($nextTerm['label'] ? 'Tagih ' . $nextTerm['label'] : 'Buat Invoice Termin Berikutnya') }}
            </button>
            @endif
            @php $waUrl = $quotation->getWhatsappUrl(); @endphp
            @if($waUrl)
            <a href="{{ $waUrl }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium text-sm bg-green-500 hover:bg-green-600 text-white transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WhatsApp
            </a>
            @endif
            <a href="{{ route('pdf.quotation', $quotation) }}" target="_blank" class="btn-secondary">Download PDF</a>
            <a href="{{ route('quotations.edit', $quotation) }}" class="btn-secondary">Edit</a>
            <a href="{{ route('quotations.index') }}" class="btn-secondary">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="card p-6">
                <div class="grid grid-cols-2 gap-4 text-sm mb-6">
                    <div><dt class="text-slate-500 dark:text-slate-400">Client</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1">{{ $quotation->client->company_name }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Date</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1">{{ $quotation->date->format('d F Y') }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Valid Until</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1">{{ $quotation->expiry_date->format('d F Y') }}</dd></div>
                    @if($quotation->approved_at)
                    <div><dt class="text-slate-500 dark:text-slate-400">Approved By</dt><dd class="font-semibold text-emerald-600 dark:text-emerald-400 mt-1">{{ $quotation->approved_by_name }} · {{ $quotation->approved_at->format('d M Y') }}</dd></div>
                    @endif
                </div>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400">
                            <th class="text-left py-2 font-medium">Description</th>
                            <th class="py-2 font-medium text-right w-16">Qty</th>
                            <th class="py-2 font-medium text-right w-36">Unit Price</th>
                            <th class="py-2 font-medium text-right w-28">Discount</th>
                            <th class="py-2 font-medium text-right w-36">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($quotation->items as $item)
                        <tr>
                            <td class="py-3">
                                <div>{{ $item->description }}</div>
                                @if($item->detail)
                                <div class="mt-1 text-xs text-slate-500 dark:text-slate-400 whitespace-pre-line">{{ $item->detail }}</div>
                                @endif
                                @if(auth()->user()->hasRole('admin') && $item->cost_price !== null)
                                <div class="mt-1 text-xs text-amber-600 dark:text-amber-500">Modal: Rp {{ number_format($item->cost_price, 0, ',', '.') }}</div>
                                @endif
                            </td>
                            <td class="py-3 text-right text-slate-600 dark:text-slate-400">{{ $item->quantity }} {{ $item->unit }}</td>
                            <td class="py-3 text-right text-slate-600 dark:text-slate-400">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="py-3 text-right text-slate-600 dark:text-slate-400">
                                @if($item->discount_amount > 0)
                                -Rp {{ number_format($item->discount_amount, 0, ',', '.') }}
                                @else
                                -
                                @endif
                            </td>
                            <td class="py-3 text-right font-medium text-slate-900 dark:text-white">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                    <div class="w-60 space-y-2 text-sm">
                        <div class="flex justify-between text-slate-600 dark:text-slate-400"><span>Subtotal</span><span>Rp {{ number_format($quotation->subtotal, 0, ',', '.') }}</span></div>
                        @if($quotation->tax_percent > 0)
                        <div class="flex justify-between text-slate-600 dark:text-slate-400"><span>Tax ({{ $quotation->tax_percent }}%)</span><span>Rp {{ number_format($quotation->tax_amount, 0, ',', '.') }}</span></div>
                        @endif
                        @if($quotation->discount_amount > 0)
                        <div class="flex justify-between text-slate-600 dark:text-slate-400"><span>Discount</span><span>-Rp {{ number_format($quotation->discount_amount, 0, ',', '.') }}</span></div>
                        @endif
                        <div class="flex justify-between font-bold text-base border-t border-slate-200 dark:border-slate-700 pt-2 text-slate-900 dark:text-white">
                            <span>Total</span><span class="text-stone-600 dark:text-stone-400">Rp {{ number_format($quotation->total_amount, 0, ',', '.') }}</span>
                        </div>
                        @if(auth()->user()->hasRole('admin'))
                        <div class="flex justify-between text-amber-600 dark:text-amber-500 pt-2 border-t border-dashed border-slate-200 dark:border-slate-700">
                            <span>Total Modal (Internal)</span><span class="font-medium">Rp {{ number_format($quotation->totalCost(), 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-emerald-600 dark:text-emerald-500">
                            <span>Margin (Internal)</span><span class="font-medium">Rp {{ number_format($quotation->total_amount - $quotation->totalCost(), 0, ',', '.') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            @php $sc = ['draft' => 'badge-gray', 'sent' => 'badge-blue', 'approved' => 'badge-green', 'rejected' => 'badge-red']; @endphp
            <div class="card p-5">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-2">Status</h3>
                <span class="{{ $sc[$quotation->status] ?? 'badge-gray' }} text-sm px-3 py-1">{{ ucfirst($quotation->status) }}</span>
                @if($quotation->approval_notes)
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $quotation->approval_notes }}</p>
                @endif
            </div>

            @php $terms = $quotation->scheduledTerms(); $billed = $quotation->billedInvoices(); @endphp
            @if(count($terms))
            <div class="card p-5">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-3">Skema Pembayaran</h3>
                <div class="space-y-2 text-sm">
                    @foreach($terms as $i => $term)
                    @php $inv = $billed[$i] ?? null; @endphp
                    <div class="flex justify-between items-start gap-3">
                        <div>
                            <div class="font-medium text-slate-900 dark:text-white">{{ $i + 1 }}. {{ $term['label'] }} <span class="text-slate-500 font-normal">({{ \App\Models\Quotation::formatPercent($term['percent']) }}%)</span></div>
                            <div class="text-xs {{ $inv ? ($inv->status === 'paid' ? 'text-emerald-600 dark:text-emerald-500' : 'text-blue-600 dark:text-blue-400') : 'text-slate-400' }}">
                                @if($inv)
                                    {{ $inv->status === 'paid' ? 'Lunas' : 'Ditagih' }} · {{ $inv->invoice_number }}
                                    @if(abs((float) $inv->total_amount - $term['amount']) > 0.5) · Rp {{ number_format($inv->total_amount, 0, ',', '.') }} @endif
                                @else
                                    Belum ditagih
                                @endif
                            </div>
                        </div>
                        <div class="font-semibold text-slate-900 dark:text-white whitespace-nowrap">Rp {{ number_format($term['amount'], 0, ',', '.') }}</div>
                    </div>
                    @endforeach
                </div>
                @if($billed->count() > count($terms))
                <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">+{{ $billed->count() - count($terms) }} invoice tambahan di luar skema.</p>
                @endif
            </div>
            @endif

            @if($quotation->invoices->isNotEmpty())
            <div class="card p-5">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-3">Invoice / Termin</h3>
                <div class="space-y-2 mb-3">
                    @foreach($quotation->invoices as $inv)
                    <a href="{{ route('invoices.show', $inv) }}" class="flex justify-between items-center text-sm p-2 -mx-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700">
                        <div>
                            <div class="font-medium text-slate-900 dark:text-white">{{ $inv->invoice_number }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                @if($inv->installment_number) Termin ke-{{ $inv->installment_number }}@if((float) $quotation->total_amount > 0) ({{ \App\Models\Quotation::formatPercent($inv->total_amount / $quotation->total_amount * 100) }}%)@endif · @endif
                                {{ ucfirst($inv->status) }}
                            </div>
                        </div>
                        <div class="font-semibold text-slate-900 dark:text-white">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</div>
                    </a>
                    @endforeach
                </div>
                <div class="pt-3 border-t border-slate-200 dark:border-slate-700 space-y-1 text-sm">
                    <div class="flex justify-between text-slate-600 dark:text-slate-400"><span>Total Ditagih</span><span>Rp {{ number_format($quotation->totalInvoiced(), 0, ',', '.') }}</span></div>
                    <div class="flex justify-between font-semibold text-slate-900 dark:text-white"><span>Sisa Saldo</span><span>Rp {{ number_format($quotation->remainingBalance(), 0, ',', '.') }}</span></div>
                </div>
            </div>
            @endif

            <div class="card p-5">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-2">Approval Link</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Share this link with the client to approve or reject:</p>
                <div class="bg-slate-50 dark:bg-slate-700 rounded-lg p-2 break-all text-xs font-mono text-slate-600 dark:text-slate-300">{{ $quotation->getApprovalUrl() }}</div>
            </div>
        </div>
    </div>

    {{-- Create Invoice / Termin Modal --}}
    @if($showInvoiceModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="card p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-1">Buat Invoice</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Sisa saldo penawaran: Rp {{ number_format($quotation->remainingBalance(), 0, ',', '.') }}</p>
            <div class="space-y-4">
                @php $modalNext = $quotation->nextTerm(); @endphp
                <div class="flex flex-wrap gap-2">
                    @if($modalNext['scheduled'])
                    <button type="button" wire:click="useScheduledTerm" class="btn-secondary py-1 px-2.5 text-xs">Sesuai skema: {{ $modalNext['label'] }}</button>
                    @endif
                    <button type="button" wire:click="payOffRemaining" class="btn-secondary py-1 px-2.5 text-xs">Lunasi sisa (Rp {{ number_format($quotation->remainingBalance(), 0, ',', '.') }})</button>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="form-label">Persen</label>
                        <div class="relative">
                            <input wire:model.live.debounce.400ms="newInvoicePercent" type="number" step="any" min="0" max="100" class="form-input pr-7 text-right">
                            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs text-slate-400">%</span>
                        </div>
                    </div>
                    <div class="col-span-2">
                        <label class="form-label">Jumlah Invoice <span class="text-red-500">*</span></label>
                        <input wire:model.live.debounce.400ms="newInvoiceAmount" type="number" step="any" min="0" class="form-input text-right">
                    </div>
                </div>
                @error('newInvoiceAmount')<p class="text-xs text-red-500 -mt-2">{{ $message }}</p>@enderror
                <p class="text-xs text-slate-500 dark:text-slate-400 -mt-2">Persen dihitung dari total penawaran. Bebas diubah kalau klien minta dicicil lebih kecil atau mau langsung lunas.</p>
                <div>
                    <label class="form-label">Deskripsi (Opsional)</label>
                    <textarea wire:model="newInvoiceDescription" rows="2" class="form-input" placeholder="Contoh: Termin 2 - Progress (diturunkan jadi 20%)"></textarea>
                    @error('newInvoiceDescription')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <input wire:model="newInvoiceSendImmediately" type="checkbox" class="w-4 h-4 rounded border-slate-300 text-stone-600 focus:ring-stone-500">
                    Kirim langsung ke email client sekarang
                </label>
            </div>
            <div class="flex gap-3 mt-6">
                <button wire:click="createInvoice" class="btn-success flex-1 justify-center" wire:loading.attr="disabled">
                    <span wire:loading.remove>Buat Invoice</span>
                    <span wire:loading>Processing...</span>
                </button>
                <button wire:click="$set('showInvoiceModal', false)" class="btn-secondary">Cancel</button>
            </div>
        </div>
    </div>
    @endif
</div>
