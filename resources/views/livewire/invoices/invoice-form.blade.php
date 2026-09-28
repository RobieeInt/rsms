<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">{{ $isEdit ? 'Edit Invoice' : 'New Invoice' }}</h2>
        </div>
        <a href="{{ route('invoices.index') }}" class="btn-secondary">Back</a>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="card p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                @if($terminMode)
                <div class="col-span-2">
                    <label class="form-label">Penawaran <span class="text-red-500">*</span></label>
                    <select wire:model.live="quotation_id" class="form-select">
                        <option value="0">Pilih penawaran...</option>
                        @foreach($quotations as $q)
                        <option value="{{ $q->id }}">{{ $q->quotation_number }} — {{ $q->client->company_name }} (sisa Rp {{ number_format($q->remainingBalance(), 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                    @error('quotation_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    @if($quotations->isEmpty())
                    <p class="mt-1 text-xs text-slate-500">Belum ada penawaran berstatus approved yang masih punya sisa tagihan.</p>
                    @endif
                </div>
                @else
                <div class="col-span-2">
                    <label class="form-label">Client <span class="text-red-500">*</span></label>
                    <select x-select wire:model="client_id" class="form-select">
                        <option value="">Select client...</option>
                        @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                        @endforeach
                    </select>
                    @error('client_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                @endif
                <div>
                    <label class="form-label">Type</label>
                    <select wire:model.live="type" class="form-select">
                        <option value="manual">Manual</option>
                        <option value="retainer">Retainer</option>
                        <option value="quotation">From Quotation (Termin)</option>
                    </select>
                </div>
                <div>
                    @if($linkedQuotation)
                    <label class="form-label">Dari Penawaran</label>
                    <a href="{{ route('quotations.show', $linkedQuotation) }}" class="block py-2 text-sm font-semibold text-stone-600 dark:text-stone-400 hover:underline">
                        {{ $linkedQuotation->quotation_number }}@if($invoice->installment_number) · Termin ke-{{ $invoice->installment_number }}@endif
                    </a>
                    @endif
                </div>
                @unless($terminMode)
                <div>
                    <label class="form-label">Invoice Date <span class="text-red-500">*</span></label>
                    <input wire:model="invoice_date" type="date" class="form-input">
                </div>
                <div>
                    <label class="form-label">Due Date <span class="text-red-500">*</span></label>
                    <input wire:model="due_date" type="date" class="form-input">
                </div>
                @endunless
            </div>
        </div>

        @if($terminMode)
        <div class="card p-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Termin</h3>
            @if($selectedQuotation)
            @php $billed = $selectedQuotation->invoices->where('status', '!=', 'cancelled'); @endphp
            <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-5">
                <div><dt class="text-slate-500 dark:text-slate-400">Client</dt><dd class="font-semibold mt-1">{{ $selectedQuotation->client->company_name }}</dd></div>
                <div><dt class="text-slate-500 dark:text-slate-400">Total Penawaran</dt><dd class="font-semibold mt-1">Rp {{ number_format($selectedQuotation->total_amount, 0, ',', '.') }}</dd></div>
                <div><dt class="text-slate-500 dark:text-slate-400">Sudah Ditagih ({{ $billed->count() }} termin)</dt><dd class="font-semibold mt-1">Rp {{ number_format($selectedQuotation->totalInvoiced(), 0, ',', '.') }}</dd></div>
                <div><dt class="text-slate-500 dark:text-slate-400">Sisa</dt><dd class="font-semibold mt-1 text-stone-600 dark:text-stone-400">Rp {{ number_format($selectedQuotation->remainingBalance(), 0, ',', '.') }}</dd></div>
            </dl>
            @php $next = $selectedQuotation->nextTerm(); $terms = $selectedQuotation->scheduledTerms(); @endphp
            @if(count($terms))
            <div class="mb-5 text-sm">
                <div class="text-slate-500 dark:text-slate-400 mb-1">Skema yang disepakati</div>
                <div class="flex flex-wrap gap-2">
                    @foreach($terms as $i => $term)
                    <span class="px-2.5 py-1 rounded-lg text-xs {{ $i < $billed->count() ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : ($i === $billed->count() ? 'bg-stone-100 text-stone-800 ring-1 ring-stone-300 dark:bg-stone-800 dark:text-stone-200 dark:ring-stone-600' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300') }}">
                        {{ $i + 1 }}. {{ $term['label'] }} · {{ \App\Models\Quotation::formatPercent($term['percent']) }}%
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
            <div class="flex flex-wrap gap-2 mb-4">
                @if($next['scheduled'])
                <button type="button" wire:click="useScheduledTerm" class="btn-secondary py-1 px-2.5 text-xs">Sesuai skema: {{ $next['label'] }}</button>
                @endif
                <button type="button" wire:click="payOffRemaining" class="btn-secondary py-1 px-2.5 text-xs">Lunasi sisa</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                <div>
                    <label class="form-label">Persen</label>
                    <div class="relative">
                        <input wire:model.live.debounce.400ms="termin_percent" type="number" min="0" max="100" step="any" class="form-input pr-7 text-right">
                        <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs text-slate-400">%</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Dari total penawaran.</p>
                </div>
                <div>
                    <label class="form-label">Nominal Termin Ini <span class="text-red-500">*</span></label>
                    <input wire:model.live.debounce.400ms="termin_amount" type="number" min="0" step="any" class="form-input text-right">
                    @error('termin_amount')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-slate-500">Bebas diubah — mau dicicil lebih kecil atau langsung lunas.</p>
                </div>
                <div class="md:col-span-2">
                    <label class="form-label">Deskripsi</label>
                    <input wire:model="termin_description" type="text" class="form-input" placeholder="Contoh: Termin 1 - DP 50%">
                    @error('termin_description')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-slate-500">Kosongkan untuk pakai "Termin ke-N — {{ $selectedQuotation->quotation_number }}". Tanggal & jatuh tempo diisi otomatis dari setting client.</p>
                </div>
            </div>
            @else
            <p class="text-sm text-slate-500 dark:text-slate-400">Pilih penawaran dulu — nanti kelihatan total, yang sudah ditagih, dan sisa saldonya.</p>
            @endif
        </div>
        @else
        <div class="card p-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Line Items</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700">
                            <th class="text-left py-2 font-medium text-slate-600 dark:text-slate-400">Description</th>
                            <th class="py-2 font-medium text-slate-600 dark:text-slate-400 w-20 text-right">Qty</th>
                            <th class="py-2 font-medium text-slate-600 dark:text-slate-400 w-20 text-center">Unit</th>
                            <th class="py-2 font-medium text-slate-600 dark:text-slate-400 w-36 text-right">Unit Price</th>
                            @if(auth()->user()->hasRole('admin'))
                            <th class="py-2 font-medium text-amber-600 dark:text-amber-500 w-36 text-right">Modal (Internal)</th>
                            @endif
                            <th class="py-2 font-medium text-slate-600 dark:text-slate-400 w-36 text-right">Total</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $i => $item)
                        <tr class="border-b border-slate-100 dark:border-slate-700">
                            <td class="py-2 pr-3">
                                <input wire:model="items.{{ $i }}.description" type="text" class="form-input py-1.5" placeholder="Description">
                            </td>
                            <td class="py-2 px-2">
                                <input wire:model.blur="items.{{ $i }}.quantity" wire:change="updateItemTotal({{ $i }})" type="number" min="0.01" step="0.01" class="form-input py-1.5 text-right">
                            </td>
                            <td class="py-2 px-2">
                                <input wire:model="items.{{ $i }}.unit" type="text" class="form-input py-1.5 text-center" placeholder="unit">
                            </td>
                            <td class="py-2 px-2">
                                <input wire:model.blur="items.{{ $i }}.unit_price" wire:change="updateItemTotal({{ $i }})" type="number" min="0" step="1000" class="form-input py-1.5 text-right">
                            </td>
                            @if(auth()->user()->hasRole('admin'))
                            <td class="py-2 px-2">
                                <input wire:model.live.debounce.500ms="items.{{ $i }}.cost_price" type="number" min="0" step="1000" class="form-input py-1.5 text-right" placeholder="Opsional">
                            </td>
                            @endif
                            <td class="py-2 px-2 text-right font-semibold text-slate-900 dark:text-white">
                                Rp {{ number_format($item['total_price'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-2 pl-2">
                                <button type="button" wire:click="removeItem({{ $i }})" class="p-1 text-red-400 hover:text-red-600">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="button" wire:click="addItem" class="mt-4 btn-secondary text-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Item
            </button>

            <div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                <div class="w-72 space-y-2 text-sm">
                    <div class="flex justify-between text-slate-600 dark:text-slate-400"><span>Subtotal</span><span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span></div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-slate-600 dark:text-slate-400">Tax (%)</span>
                            <input wire:model.blur="tax_percent" type="number" min="0" max="100" class="form-input py-1 px-2 w-16 text-center text-xs">
                        </div>
                        <span class="text-slate-600 dark:text-slate-400">Rp {{ number_format($tax_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600 dark:text-slate-400">Discount</span>
                        <input wire:model.blur="discount_amount" type="number" min="0" class="form-input py-1 px-2 w-32 text-right text-xs">
                    </div>
                    <div class="flex justify-between font-bold text-base border-t border-slate-200 dark:border-slate-700 pt-2 text-slate-900 dark:text-white">
                        <span>Total</span>
                        <span class="text-stone-600 dark:text-stone-400">Rp {{ number_format($total_amount, 0, ',', '.') }}</span>
                    </div>
                    @if(auth()->user()->hasRole('admin'))
                    @php
                        $totalCost = collect($items)->sum(fn($i) => ($i['cost_price'] ?? null) !== null ? $i['cost_price'] * $i['quantity'] : 0);
                        $margin = $total_amount - $totalCost;
                    @endphp
                    <div class="flex justify-between text-amber-600 dark:text-amber-500 pt-2 border-t border-dashed border-slate-200 dark:border-slate-700">
                        <span>Total Modal (Internal)</span>
                        <span class="font-medium">Rp {{ number_format($totalCost, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-emerald-600 dark:text-emerald-500">
                        <span>Margin (Internal)</span>
                        <span class="font-medium">Rp {{ number_format($margin, 0, ',', '.') }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card p-6">
            <label class="form-label">Notes</label>
            <textarea wire:model="notes" rows="3" class="form-input" placeholder="Payment instructions or notes..."></textarea>
        </div>
        @endif

        <div class="flex gap-3">
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove>{{ $isEdit ? 'Update Invoice' : ($terminMode ? 'Buat Invoice Termin' : 'Create Invoice') }}</span>
                <span wire:loading>Saving...</span>
            </button>
            <a href="{{ route('invoices.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
