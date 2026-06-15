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
            @if($quotation->status === 'approved' && !$quotation->invoice)
            <button wire:click="convertToInvoice" class="btn-success">Convert to Invoice</button>
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
                            <th class="py-2 font-medium text-right w-36">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($quotation->items as $item)
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

            @if($quotation->invoice)
            <div class="card p-5">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-2">Invoice</h3>
                <a href="{{ route('invoices.show', $quotation->invoice) }}" class="text-sm text-stone-600 dark:text-stone-400 hover:underline">
                    {{ $quotation->invoice->invoice_number }}
                </a>
            </div>
            @endif

            <div class="card p-5">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-2">Approval Link</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Share this link with the client to approve or reject:</p>
                <div class="bg-slate-50 dark:bg-slate-700 rounded-lg p-2 break-all text-xs font-mono text-slate-600 dark:text-slate-300">{{ $quotation->getApprovalUrl() }}</div>
            </div>
        </div>
    </div>
</div>
