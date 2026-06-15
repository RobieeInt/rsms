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
                            <th class="py-2 font-medium text-slate-600 dark:text-slate-400 w-36 text-right">Total</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $i => $item)
                        <tr class="border-b border-slate-100 dark:border-slate-700">
                            <td class="py-2 pr-3">
                                <input wire:model.blur="items.{{ $i }}.description" type="text" class="form-input py-1.5" placeholder="Service or product description">
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
                </div>
            </div>
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
