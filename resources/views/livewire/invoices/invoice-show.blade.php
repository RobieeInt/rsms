<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">{{ $invoice->invoice_number }}</h2>
            <p class="page-subtitle">{{ $invoice->client->company_name }}</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            @if($invoice->status === 'draft')
            <button wire:click="send" class="btn-primary">Kirim ke Klien</button>
            @endif
            @if($invoice->status !== 'draft')
            <button wire:click="resendEmail" wire:loading.attr="disabled" wire:target="resendEmail" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span wire:loading.remove wire:target="resendEmail">Email</span>
                <span wire:loading wire:target="resendEmail">Mengirim...</span>
            </button>
            @php $waUrl = $invoice->getWhatsappUrl(); @endphp
            @if($waUrl)
            <a href="{{ $waUrl }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium text-sm bg-green-500 hover:bg-green-600 text-white transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WhatsApp
            </a>
            @endif
            @endif
            @if(in_array($invoice->status, ['sent', 'overdue']))
            <button wire:click="$set('showPaymentModal', true)" class="btn-success">Tandai Lunas</button>
            @endif
            <a href="{{ route('pdf.invoice', $invoice) }}" target="_blank" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                PDF
            </a>
            <a href="{{ route('invoices.edit', $invoice) }}" class="btn-secondary">Edit</a>
            <a href="{{ route('invoices.index') }}" class="btn-secondary">Kembali</a>
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
                            <span class="text-stone-600 dark:text-stone-400">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
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
                <a href="{{ asset('storage/' . $invoice->payment_proof) }}" target="_blank" class="text-sm text-stone-600 dark:text-stone-400 hover:underline">View proof</a>
            </div>
            @endif

            {{-- Send Log --}}
            <div class="card p-5">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Riwayat Pengiriman
                </h3>
                @if($invoice->sendLogs->isEmpty())
                <p class="text-sm text-slate-400 dark:text-slate-500 italic">Belum ada email yang dikirim.</p>
                @else
                <ul class="space-y-2">
                    @foreach($invoice->sendLogs as $log)
                    @php
                        $typeColor = match(true) {
                            str_contains($log->type, 'overdue') => 'text-red-500',
                            $log->type === 'due_today'          => 'text-orange-500',
                            $log->type === 'sent'               => 'text-stone-500',
                            default                             => 'text-blue-500',
                        };
                    @endphp
                    <li class="flex items-start gap-2.5 text-xs">
                        <span class="mt-0.5 w-1.5 h-1.5 rounded-full bg-current {{ $typeColor }} shrink-0 mt-1.5"></span>
                        <div class="min-w-0">
                            <span class="font-medium text-slate-700 dark:text-slate-300 {{ $typeColor }}">{{ (new \App\Models\InvoiceSendLog(['type' => $log->type]))->typeLabel() }}</span>
                            @if($log->sent_to)
                            <span class="text-slate-400"> → {{ $log->sent_to }}</span>
                            @endif
                            <div class="text-slate-400 dark:text-slate-500 mt-0.5">
                                {{ $log->sent_at->locale('id')->translatedFormat('d M Y, H:i') }}
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
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
