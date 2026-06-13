<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('clients.index') }}" class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center text-violet-700 dark:text-violet-300 font-bold text-lg">
                    {{ strtoupper(substr($client->company_name, 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $client->company_name }}</h1>
                        @if ($client->is_active)
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">Active</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">Inactive</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $client->pic_name }} &bull; {{ $client->pic_email }}</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('clients.assets.create', $client) }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Asset
            </a>
            <a href="{{ route('clients.edit', $client) }}" class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-3 py-2 text-sm font-medium text-white hover:bg-violet-700 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Client
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Assets</p>
            <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['assets'] }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Visits</p>
            <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['visits'] }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
            <p class="text-sm text-gray-500 dark:text-gray-400">Open Findings</p>
            <p class="mt-1 text-3xl font-bold {{ $stats['open_findings'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ $stats['open_findings'] }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
            <p class="text-sm text-gray-500 dark:text-gray-400">Unpaid Invoices</p>
            <p class="mt-1 text-3xl font-bold {{ $stats['unpaid_invoices'] > 0 ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-900 dark:text-white' }}">{{ $stats['unpaid_invoices'] }}</p>
        </div>
    </div>

    {{-- Health Score --}}
    @if ($client->health_status)
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm flex items-center gap-4">
        <div class="flex-1">
            <div class="flex items-center justify-between mb-1">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Health Score</span>
                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($client->health_score) }}/100</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="h-2 rounded-full {{ $client->health_status === 'healthy' ? 'bg-green-500' : ($client->health_status === 'warning' ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $client->health_score }}%"></div>
            </div>
        </div>
        @php
            $healthColors = ['healthy' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300', 'warning' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300', 'critical' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'];
        @endphp
        <span class="px-3 py-1 rounded-full text-sm font-medium {{ $healthColors[$client->health_status] ?? 'bg-gray-100 text-gray-600' }}">
            {{ ucfirst($client->health_status) }}
        </span>
    </div>
    @endif

    {{-- Tabs --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex gap-1 px-4 pt-4" role="tablist">
                @foreach (['assets' => 'Assets', 'schedules' => 'Schedules', 'findings' => 'Findings', 'invoices' => 'Invoices'] as $tab => $label)
                    <button wire:click="setTab('{{ $tab }}')" class="px-4 py-2.5 text-sm font-medium rounded-t-lg transition-colors {{ $activeTab === $tab ? 'bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-300 border-b-2 border-violet-600' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        </div>

        <div class="p-4">
            {{-- Assets Tab --}}
            @if ($activeTab === 'assets')
                @if ($assets && $assets->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Code</th>
                                    <th class="py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                                    <th class="py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                                    <th class="py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Location</th>
                                    <th class="py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Health</th>
                                    <th class="py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($assets as $asset)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="py-3 text-sm font-mono text-gray-600 dark:text-gray-400">{{ $asset->asset_code }}</td>
                                        <td class="py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $asset->asset_name }}</td>
                                        <td class="py-3 text-sm text-gray-500 dark:text-gray-400">{{ $asset->getTypeLabel() }}</td>
                                        <td class="py-3 text-sm text-gray-500 dark:text-gray-400">{{ $asset->location ?? '-' }}</td>
                                        <td class="py-3">
                                            @php $hc = ['healthy' => 'bg-green-100 text-green-700', 'warning' => 'bg-yellow-100 text-yellow-700', 'critical' => 'bg-red-100 text-red-700']; @endphp
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $hc[$asset->health_status] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst($asset->health_status ?? 'unknown') }}</span>
                                        </td>
                                        <td class="py-3 text-right">
                                            <a href="{{ route('assets.show', $asset) }}" class="text-violet-600 dark:text-violet-400 text-xs hover:underline">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $assets->links() }}
                @else
                    <div class="py-12 text-center text-gray-400 dark:text-gray-500">No assets found for this client.</div>
                @endif
            @endif

            {{-- Schedules Tab --}}
            @if ($activeTab === 'schedules')
                @if ($schedules && $schedules->count())
                    <div class="space-y-3">
                        @foreach ($schedules as $schedule)
                            <div class="flex items-center justify-between rounded-lg border border-gray-100 dark:border-gray-700 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $schedule->visit_date->format('d M Y') }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $schedule->technician->name ?? 'Unassigned' }} &bull; {{ $schedule->start_time }} - {{ $schedule->end_time }}</p>
                                </div>
                                @php $sc = ['scheduled' => 'bg-blue-100 text-blue-700', 'in_progress' => 'bg-yellow-100 text-yellow-700', 'completed' => 'bg-green-100 text-green-700', 'cancelled' => 'bg-red-100 text-red-700']; @endphp
                                <div class="flex items-center gap-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc[$schedule->status] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst(str_replace('_', ' ', $schedule->status)) }}</span>
                                    <a href="{{ route('schedules.show', $schedule) }}" class="text-violet-600 dark:text-violet-400 text-xs hover:underline">View</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    {{ $schedules->links() }}
                @else
                    <div class="py-12 text-center text-gray-400 dark:text-gray-500">No schedules found for this client.</div>
                @endif
            @endif

            {{-- Findings Tab --}}
            @if ($activeTab === 'findings')
                @if ($findings && $findings->count())
                    <div class="space-y-3">
                        @foreach ($findings as $finding)
                            <div class="flex items-center justify-between rounded-lg border border-gray-100 dark:border-gray-700 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $finding->title }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $finding->asset->asset_name ?? 'General' }} &bull; {{ $finding->category }}</p>
                                </div>
                                @php $fc = ['critical' => 'bg-red-100 text-red-700', 'high' => 'bg-orange-100 text-orange-700', 'medium' => 'bg-yellow-100 text-yellow-700', 'low' => 'bg-blue-100 text-blue-700']; @endphp
                                <div class="flex items-center gap-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $fc[$finding->severity] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst($finding->severity) }}</span>
                                    <a href="{{ route('findings.show', $finding) }}" class="text-violet-600 dark:text-violet-400 text-xs hover:underline">View</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    {{ $findings->links() }}
                @else
                    <div class="py-12 text-center text-gray-400 dark:text-gray-500">No findings for this client.</div>
                @endif
            @endif

            {{-- Invoices Tab --}}
            @if ($activeTab === 'invoices')
                @if ($client->monthly_retainer_fee > 0)
                <div class="mb-4 flex items-center justify-between rounded-xl border border-violet-200 dark:border-violet-800 bg-violet-50 dark:bg-violet-900/20 px-4 py-3">
                    <div>
                        <p class="text-sm font-medium text-violet-900 dark:text-violet-200">Monthly Retainer: <span class="font-bold">Rp {{ number_format($client->monthly_retainer_fee, 0, ',', '.') }}</span></p>
                        <p class="text-xs text-violet-600 dark:text-violet-400">Generate invoice for {{ now()->format('F Y') }}</p>
                    </div>
                    <button wire:click="generateRetainerInvoice" wire:loading.attr="disabled" class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-3 py-2 text-sm font-medium text-white hover:bg-violet-700 disabled:opacity-60 transition-colors">
                        <span wire:loading.remove wire:target="generateRetainerInvoice">
                            <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Generate Retainer Invoice
                        </span>
                        <span wire:loading wire:target="generateRetainerInvoice">Generating...</span>
                    </button>
                </div>
                @endif

                @if ($invoices && $invoices->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                                    <th class="py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="py-3 text-left text-xs font-medium text-gray-500 uppercase">Due</th>
                                    <th class="py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($invoices as $invoice)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="py-3 text-sm font-mono text-gray-600 dark:text-gray-400">{{ $invoice->invoice_number }}</td>
                                        <td class="py-3 text-sm text-gray-900 dark:text-white">{{ $invoice->invoice_date->format('d M Y') }}</td>
                                        <td class="py-3 text-sm text-gray-500 dark:text-gray-400">{{ $invoice->due_date->format('d M Y') }}</td>
                                        <td class="py-3 text-sm font-medium text-gray-900 dark:text-white">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                                        <td class="py-3">
                                            @php $ic = ['draft' => 'bg-gray-100 text-gray-600', 'sent' => 'bg-blue-100 text-blue-700', 'paid' => 'bg-green-100 text-green-700', 'overdue' => 'bg-red-100 text-red-700', 'cancelled' => 'bg-gray-100 text-gray-500']; @endphp
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $ic[$invoice->status] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst($invoice->status) }}</span>
                                        </td>
                                        <td class="py-3 text-right"><a href="{{ route('invoices.show', $invoice) }}" class="text-violet-600 dark:text-violet-400 text-xs hover:underline">View</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $invoices->links() }}
                @else
                    <div class="py-12 text-center text-gray-400 dark:text-gray-500">No invoices for this client.</div>
                @endif
            @endif
        </div>
    </div>
</div>
