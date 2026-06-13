<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">{{ $asset->asset_name }}</h2>
            <p class="page-subtitle font-mono text-xs">{{ $asset->asset_code }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('assets.edit', $asset) }}" class="btn-secondary">Edit</a>
            <a href="{{ route('assets.index') }}" class="btn-secondary">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="card p-6">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Asset Details</h3>
                <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div><dt class="text-slate-500 dark:text-slate-400">Type</dt><dd class="font-medium text-slate-900 dark:text-white mt-1">{{ $asset->getTypeLabel() }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Client</dt><dd class="font-medium text-slate-900 dark:text-white mt-1">{{ $asset->client->company_name }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Brand</dt><dd class="font-medium text-slate-900 dark:text-white mt-1">{{ $asset->brand ?? '-' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Model</dt><dd class="font-medium text-slate-900 dark:text-white mt-1">{{ $asset->model ?? '-' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Serial Number</dt><dd class="font-mono text-xs font-medium text-slate-900 dark:text-white mt-1">{{ $asset->serial_number ?? '-' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Purchase Year</dt><dd class="font-medium text-slate-900 dark:text-white mt-1">{{ $asset->purchase_year ?? '-' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">CPU</dt><dd class="font-medium text-slate-900 dark:text-white mt-1">{{ $asset->cpu ?? '-' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">RAM</dt><dd class="font-medium text-slate-900 dark:text-white mt-1">{{ $asset->ram ?? '-' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Storage</dt><dd class="font-medium text-slate-900 dark:text-white mt-1">{{ $asset->storage ?? '-' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">OS</dt><dd class="font-medium text-slate-900 dark:text-white mt-1">{{ $asset->operating_system ?? '-' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Location</dt><dd class="font-medium text-slate-900 dark:text-white mt-1">{{ $asset->location ?? '-' }}</dd></div>
                </dl>
                @if($asset->notes)
                <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <dt class="text-slate-500 dark:text-slate-400 text-sm mb-1">Notes</dt>
                    <dd class="text-sm text-slate-700 dark:text-slate-300">{{ $asset->notes }}</dd>
                </div>
                @endif
            </div>

            <div class="card">
                <div class="p-5 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="font-semibold text-slate-900 dark:text-white">Maintenance History</h3>
                </div>
                @forelse($asset->checklists as $checklist)
                <div class="p-4 border-b border-slate-100 dark:border-slate-700 last:border-0">
                    <div class="text-sm font-medium text-slate-900 dark:text-white">{{ $checklist->visitReport->report_number ?? '-' }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $checklist->created_at->format('d M Y') }}</div>
                </div>
                @empty
                <div class="p-10 text-center text-sm text-slate-500 dark:text-slate-400">No maintenance history yet</div>
                @endforelse
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-3">Health Status</h3>
                @php
                $healthClasses = ['good' => 'badge-green', 'fair' => 'badge-yellow', 'poor' => 'badge-medium', 'critical' => 'badge-critical'];
                @endphp
                <span class="{{ $healthClasses[$asset->health_status] ?? 'badge-gray' }} text-sm px-3 py-1">{{ ucfirst($asset->health_status) }}</span>
            </div>

            <div class="card p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-slate-900 dark:text-white">Open Findings</h3>
                    <span class="badge-gray">{{ $asset->findings->where('status', '!=', 'resolved')->count() }}</span>
                </div>
                @foreach($asset->findings->where('status', '!=', 'resolved') as $finding)
                <a href="{{ route('findings.show', $finding) }}" class="flex items-center gap-2 py-2 hover:text-violet-600 dark:hover:text-violet-400 transition-colors">
                    <span class="{{ $finding->getSeverityBadgeClass() }}">{{ ucfirst($finding->severity) }}</span>
                    <span class="text-sm text-slate-700 dark:text-slate-300 truncate">{{ $finding->title }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
