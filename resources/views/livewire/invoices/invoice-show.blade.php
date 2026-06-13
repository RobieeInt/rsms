<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">{{ $invoice->invoice_number }}</h2>
            <p class="page-subtitle">{{ $invoice->client->company_name }}</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            @if($invoice->status === 'draft')
            <button wire:click="send" class="btn-primary">Send to Client</button>
            @endif
            @if($invoice->status !== 'draft')
            <button wire:click="resendEmail" wire:loading.attr="disabled" wire:target="resendEmail" class="btn-secondary">
                <span wire:loading.remove wire:target="resendEmail">Resend Email</span>
                <span wire:loading wire:target="resendEmail">Sending...</span>
            </button>
            @endif
            @if(in_array($invoice->status, ['sent', 'overdue']))
            <button wire:click="$set('showPaymentModal', true)" class="btn-success">Mark as Paid</button>
            @endif
            <a href="{{ route('pdf.invoice', $invoice) }}" target="_blank" class="btn-secondary">Download PDF</a>
            <a href="{{ route('invoices.edit', $invoice) }}" class="btn-secondary">Edit</a>
            <a href="{{ route('invoices.index') }}" class="btn-secondary">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="card p-6">
                <div class="grid grid-cols-2 gap-4 text-sm mb-6">
                    <div><dt class="text-slate-500 dark:text-slate-400">Client</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1">{{ $invoice->client->company_name }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Invoice Date</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1">{{ $invoice->invoice_date->format('d F Y') }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Due Date</dt><dd class="font-semibold {{ $invoice->isOverdue() ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white' }} mt-1">{{ $invoice->due_date->format('d F Y') }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Type</dt><dd class="mt-1"><span class="badge-gray">{{ ucfirst($invoice->type) }}</span></dd></div>
                    @if($invoice->payment_date)
                    <div><dt class="text-slate-500 dark:text-slate-400">Payment Date</dt><dd class="font-semibold text-emerald-600 dark:text-emerald-400 mt-1">{{ $invoice->payment_date->format('d F Y') }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Payment Method</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1">{{ $invoice->payment_method }}</dd></div>
                    @endif
                </div>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400">
                            <th class="text-left py-2 font-medium">Description</th>
                            <th class="py-2 font-medium text-right w-16">Qty</th>
                            <th class="py-2 font-medium text-right w-36">Unit Price</th>
                            <th class="py-2 font-medium text-right w-36">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($invoice->items as $item)
                        <tr>
                            <td class="py-3">{{ $item->description }}</td>
                            <td class="py-3 text-right text-slate-600 dark:text-slate-400">{{ $item->quantity }} {{ $item->unit }}</td>
                            <td class="py-3 text-right text-slate-600 dark:text-slate-400">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="py-3 text-right font-medium text-slate-900 dark:text-white">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                    <div class="w-60 space-y-2 text-sm">
                        <div class="flex justify-between text-slate-600 dark:text-slate-400"><span>Subtotal</span><span>Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span></div>
                        @if($invoice->tax_percent > 0)
                        <div class="flex justify-between text-slate-600 dark:text-slate-400"><span>Tax ({{ $invoice->tax_percent }}%)</span><span>Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</span></div>
                        @endif
                        @if($invoice->discount_amount > 0)
                        <div class="flex justify-between text-slate-600 dark:text-slate-400"><span>Discount</span><span>-Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</span></div>
                        @endif
                        <div class="flex justify-between font-bold text-base border-t border-slate-200 dark:border-slate-700 pt-2 text-slate-900 dark:text-white">
                            <span>Total</span>
                            <span class="text-violet-600 dark:text-violet-400">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            @php $sc = ['draft' => 'badge-gray', 'sent' => 'badge-blue', 'paid' => 'badge-green', 'overdue' => 'badge-red', 'cancelled' => 'badge-gray']; @endphp
            <div class="card p-5">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-2">Status</h3>
                <span class="{{ $sc[$invoice->status] ?? 'badge-gray' }} text-sm px-3 py-1">{{ ucfirst($invoice->status) }}</span>
            </div>

            @if($invoice->payment_proof)
            <div class="card p-5">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-2">Payment Proof</h3>
                <a href="{{ asset('storage/' . $invoice->payment_proof) }}" target="_blank" class="text-sm text-violet-600 dark:text-violet-400 hover:underline">View proof</a>
            </div>
            @endif
        </div>
    </div>

    {{-- Payment Modal --}}
    @if($showPaymentModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="card p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Record Payment</h3>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Payment Date <span class="text-red-500">*</span></label>
                    <input wire:model="payment_date" type="date" class="form-input">
                    @error('payment_date')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Payment Method <span class="text-red-500">*</span></label>
                    <select wire:model="payment_method" class="form-select">
                        <option value="">Select method...</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Cash">Cash</option>
                        <option value="Cheque">Cheque</option>
                        <option value="Virtual Account">Virtual Account</option>
                    </select>
                    @error('payment_method')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Payment Proof (Optional)</label>
                    <input wire:model="payment_proof" type="file" class="form-input">
                </div>
                <div>
                    <label class="form-label">Notes</label>
                    <textarea wire:model="payment_notes" rows="2" class="form-input" placeholder="Transfer reference, etc."></textarea>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button wire:click="markAsPaid" class="btn-success flex-1 justify-center" wire:loading.attr="disabled">
                    <span wire:loading.remove>Confirm Payment</span>
                    <span wire:loading>Processing...</span>
                </button>
                <button wire:click="$set('showPaymentModal', false)" class="btn-secondary">Cancel</button>
            </div>
        </div>
    </div>
    @endif
</div>
