@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="stat-card">
            <div class="stat-icon bg-violet-100 dark:bg-violet-900">
                <svg class="w-6 h-6 text-violet-600 dark:text-violet-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($stats['total_clients']) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Active Clients</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-blue-100 dark:bg-blue-900">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($stats['active_assets']) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Active Assets</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-emerald-100 dark:bg-emerald-900">
                <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($stats['upcoming_visits']) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Upcoming Visits</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-red-100 dark:bg-red-900">
                <svg class="w-6 h-6 text-red-600 dark:text-red-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($stats['open_findings']) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Open Findings</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-amber-100 dark:bg-amber-900">
                <svg class="w-6 h-6 text-amber-600 dark:text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($stats['pending_quotations']) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pending Quotations</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-orange-100 dark:bg-orange-900">
                <svg class="w-6 h-6 text-orange-600 dark:text-orange-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($stats['unpaid_invoices']) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Unpaid Invoices</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-teal-100 dark:bg-teal-900">
                <svg class="w-6 h-6 text-teal-600 dark:text-teal-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="text-xl font-bold text-slate-900 dark:text-white">Rp {{ number_format($stats['monthly_revenue'], 0, ',', '.') }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Monthly Revenue</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-indigo-100 dark:bg-indigo-900">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div>
                <div class="text-xl font-bold text-slate-900 dark:text-white">Rp {{ number_format($stats['annual_revenue'], 0, ',', '.') }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Annual Revenue</div>
            </div>
        </div>
    </div>

    {{-- Main content grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Upcoming Visits --}}
        <div class="lg:col-span-2 card">
            <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h3 class="font-semibold text-slate-900 dark:text-white">Upcoming Visits</h3>
                <a href="{{ route('schedules.index') }}" class="text-xs text-violet-600 dark:text-violet-400 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($recentSchedules as $schedule)
                <a href="{{ route('schedules.show', $schedule) }}" class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50 dark:hover:bg-slate-750 transition-colors">
                    <div class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-violet-600 dark:text-violet-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ $schedule->client->company_name }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $schedule->technician->name }} · {{ $schedule->start_time }}</div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <div class="text-sm font-medium text-slate-900 dark:text-white">{{ $schedule->visit_date->format('d M') }}</div>
                        <span class="badge-{{ $schedule->status === 'scheduled' ? 'blue' : 'yellow' }}">{{ ucfirst($schedule->status) }}</span>
                    </div>
                </a>
                @empty
                <div class="px-5 py-12 text-center">
                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="text-sm text-slate-500 dark:text-slate-400">No upcoming visits</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Right column --}}
        <div class="space-y-6">
            {{-- Critical Findings --}}
            <div class="card">
                <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-900 dark:text-white">Critical Findings</h3>
                    <a href="{{ route('findings.index') }}" class="text-xs text-violet-600 dark:text-violet-400 hover:underline">View all</a>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($recentFindings as $finding)
                    <a href="{{ route('findings.show', $finding) }}" class="flex items-start gap-3 px-5 py-3.5 hover:bg-slate-50 dark:hover:bg-slate-750 transition-colors">
                        <div class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0 mt-1.5"></div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ $finding->title }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $finding->client->company_name }}</div>
                        </div>
                        <span class="badge-critical flex-shrink-0">{{ ucfirst($finding->severity) }}</span>
                    </a>
                    @empty
                    <div class="px-5 py-8 text-center">
                        <p class="text-sm text-slate-500 dark:text-slate-400">No critical findings</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Overdue Invoices --}}
            <div class="card">
                <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-900 dark:text-white">Overdue Invoices</h3>
                    <a href="{{ route('invoices.index') }}" class="text-xs text-violet-600 dark:text-violet-400 hover:underline">View all</a>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($overdueInvoices as $invoice)
                    <a href="{{ route('invoices.show', $invoice) }}" class="flex items-center gap-3 px-5 py-3.5 hover:bg-slate-50 dark:hover:bg-slate-750 transition-colors">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ $invoice->client->company_name }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $invoice->invoice_number }}</div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="text-sm font-semibold text-red-600 dark:text-red-400">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</div>
                            <span class="badge-red">Overdue</span>
                        </div>
                    </a>
                    @empty
                    <div class="px-5 py-8 text-center">
                        <p class="text-sm text-slate-500 dark:text-slate-400">No overdue invoices</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue Chart (simple bar chart using CSS) --}}
    <div class="card p-6">
        <h3 class="font-semibold text-slate-900 dark:text-white mb-6">Revenue Trend (Last 12 Months)</h3>
        <div class="flex items-end gap-2 h-32">
            @php $maxRevenue = collect($revenueData)->max('revenue') ?: 1; @endphp
            @foreach($revenueData as $data)
            <div class="flex-1 flex flex-col items-center gap-1 group">
                <div class="relative w-full">
                    <div
                        class="w-full bg-violet-500 dark:bg-violet-600 rounded-t-sm group-hover:bg-violet-600 dark:group-hover:bg-violet-500 transition-colors cursor-default"
                        style="height: {{ max(4, ($data['revenue'] / $maxRevenue) * 120) }}px"
                        title="Rp {{ number_format($data['revenue'], 0, ',', '.') }}"
                    ></div>
                </div>
                <div class="text-[10px] text-slate-400 dark:text-slate-500 truncate w-full text-center">{{ \Illuminate\Support\Str::limit($data['month'], 6, '') }}</div>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
