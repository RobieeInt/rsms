<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">{{ $isEdit ? 'Edit Quotation' : 'New Quotation' }}</h2>
        </div>
        <a href="{{ route('quotations.index') }}" class="btn-secondary">Back</a>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="card p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
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
                <div>
                    <label class="form-label">Date <span class="text-red-500">*</span></label>
                    <input wire:model="date" type="date" class="form-input">
                </div>
                <div>
                    <label class="form-label">Valid Until <span class="text-red-500">*</span></label>
                    <input wire:model="expiry_date" type="date" class="form-input">
                </div>
            </div>
        </div>

        {{-- Line Items --}}
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
                            <th class="py-2 font-medium text-slate-600 dark:text-slate-400 w-28 text-right">Discount</th>
                            <th class="py-2 font-medium text-slate-600 dark:text-slate-400 w-36 text-right">Total</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $i => $item)
                        <tr class="border-b border-slate-100 dark:border-slate-700">
                            <td class="py-2 pr-3">
                                <input wire:model.blur="items.{{ $i }}.description" type="text" class="form-input py-1.5" placeholder="Service or product description">
                                <textarea wire:model.blur="items.{{ $i }}.detail" rows="2" class="form-input py-1.5 mt-1 text-xs" placeholder="Isi detail item (e.g. Setup Meta API, Chatbot, ...)"></textarea>
                                @error("items.$i.description")<p class="text-xs text-red-500">{{ $message }}</p>@enderror
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
                            <td class="py-2 px-2">
                                <input wire:model.blur="items.{{ $i }}.discount_amount" wire:change="updateItemTotal({{ $i }})" type="number" min="0" step="1000" class="form-input py-1.5 text-right" placeholder="0">
                            </td>
                            <td class="py-2 px-2">
                                <div class="text-right font-semibold text-slate-900 dark:text-white">
                                    Rp {{ number_format($item['total_price'] ?? 0, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="py-2 pl-2">
                                <button type="button" wire:click="removeItem({{ $i }})" class="p-1 text-red-400 hover:text-red-600 transition-colors">
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

            {{-- Totals --}}
            <div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                <div class="w-72 space-y-2 text-sm">
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>Subtotal</span>
                        <span class="font-medium text-slate-900 dark:text-white">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-slate-600 dark:text-slate-400">Tax (%)</span>
                            <input wire:model.blur="tax_percent" type="number" min="0" max="100" step="0.5" class="form-input py-1 px-2 w-16 text-center text-xs">
                        </div>
                        <span class="text-slate-600 dark:text-slate-400">Rp {{ number_format($tax_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-slate-600 dark:text-slate-400">Discount</span>
                            <input wire:model.blur="discount_amount" type="number" min="0" step="1000" class="form-input py-1 px-2 w-28 text-right text-xs">
                        </div>
                        <span class="text-slate-600 dark:text-slate-400">-Rp {{ number_format($discount_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-base border-t border-slate-200 dark:border-slate-700 pt-2">
                        <span class="text-slate-900 dark:text-white">Total</span>
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

        {{-- Skema Pembayaran / Termin --}}
        <div class="card p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <h3 class="font-semibold text-slate-900 dark:text-white">Skema Pembayaran (Termin)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kosongkan kalau dibayar penuh sekali. Kalau diisi, saat klien approve cuma termin pertama yang otomatis ditagih — termin berikutnya dibuat dari halaman penawaran dan nominalnya tetap bisa diubah.</p>
                </div>
                <button type="button" wire:click="addPaymentTerm" class="btn-secondary text-sm shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Termin
                </button>
            </div>

            @if(count($payment_terms))
            @php $termsSum = collect($payment_terms)->sum(fn($t) => (float) ($t['percent'] ?: 0)); @endphp
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700">
                        <th class="text-left py-2 font-medium text-slate-600 dark:text-slate-400 w-10">#</th>
                        <th class="text-left py-2 font-medium text-slate-600 dark:text-slate-400">Nama Termin</th>
                        <th class="py-2 font-medium text-slate-600 dark:text-slate-400 w-28 text-right">Persen</th>
                        <th class="py-2 font-medium text-slate-600 dark:text-slate-400 w-40 text-right">Nominal</th>
                        <th class="w-10"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payment_terms as $i => $term)
                    <tr class="border-b border-slate-100 dark:border-slate-700">
                        <td class="py-2 text-slate-500">{{ $i + 1 }}</td>
                        <td class="py-2 pr-3">
                            <input wire:model="payment_terms.{{ $i }}.label" type="text" class="form-input py-1.5" placeholder="Contoh: DP, Progress 50%, Pelunasan">
                            @error("payment_terms.$i.label")<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </td>
                        <td class="py-2 px-2">
                            <div class="relative">
                                <input wire:model.live.debounce.400ms="payment_terms.{{ $i }}.percent" type="number" min="0" max="100" step="any" class="form-input py-1.5 pr-7 text-right">
                                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs text-slate-400">%</span>
                            </div>
                            @error("payment_terms.$i.percent")<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </td>
                        <td class="py-2 px-2 text-right font-semibold text-slate-900 dark:text-white">
                            Rp {{ number_format($total_amount * (float) ($term['percent'] ?: 0) / 100, 0, ',', '.') }}
                        </td>
                        <td class="py-2 pl-2">
                            <button type="button" wire:click="removePaymentTerm({{ $i }})" class="p-1 text-red-400 hover:text-red-600">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td class="py-2 text-right font-medium text-slate-600 dark:text-slate-400">Total</td>
                        <td class="py-2 px-2 text-right font-bold {{ abs($termsSum - 100) > 0.001 ? 'text-red-500' : 'text-emerald-600 dark:text-emerald-500' }}">{{ \App\Models\Quotation::formatPercent($termsSum) }}%</td>
                        <td class="py-2 px-2 text-right font-bold text-slate-900 dark:text-white">Rp {{ number_format($total_amount * $termsSum / 100, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            @error('payment_terms')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
            @else
            <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada termin — penawaran ini akan ditagih penuh dalam 1 invoice.</p>
            @endif
        </div>

        <div class="card p-6">
            <label class="form-label">Notes</label>
            <textarea wire:model="notes" rows="3" class="form-input" placeholder="Additional terms or notes..."></textarea>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove>{{ $isEdit ? 'Update Quotation' : 'Create Quotation' }}</span>
                <span wire:loading>Saving...</span>
            </button>
            <a href="{{ route('quotations.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
