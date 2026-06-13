<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">Finding Detail</h2>
            <p class="page-subtitle">{{ $finding->client->company_name }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('findings.edit', $finding) }}" class="btn-secondary">Edit</a>
            <a href="{{ route('findings.index') }}" class="btn-secondary">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">{{ $finding->title }}</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex gap-6">
                        <div><dt class="text-slate-500 dark:text-slate-400">Severity</dt><dd class="mt-1"><span class="{{ $finding->getSeverityBadgeClass() }}">{{ ucfirst($finding->severity) }}</span></dd></div>
                        <div><dt class="text-slate-500 dark:text-slate-400">Status</dt>
                            @php $sc = ['open' => 'badge-red', 'monitoring' => 'badge-yellow', 'resolved' => 'badge-green']; @endphp
                            <dd class="mt-1"><span class="{{ $sc[$finding->status] ?? 'badge-gray' }}">{{ ucfirst($finding->status) }}</span></dd>
                        </div>
                        @if($finding->category)
                        <div><dt class="text-slate-500 dark:text-slate-400">Category</dt><dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $finding->category }}</dd></div>
                        @endif
                    </div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Asset</dt><dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $finding->asset?->asset_name ?? 'Not specific' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Reported By</dt><dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $finding->reporter->name }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Date</dt><dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $finding->created_at->format('d F Y H:i') }}</dd></div>
                    @if($finding->description)
                    <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                        <dt class="text-slate-500 dark:text-slate-400 mb-2">Description</dt>
                        <dd class="text-slate-700 dark:text-slate-300">{{ $finding->description }}</dd>
                    </div>
                    @endif
                    @if($finding->resolved_at)
                    <div><dt class="text-slate-500 dark:text-slate-400">Resolved At</dt><dd class="mt-1 font-medium text-emerald-600 dark:text-emerald-400">{{ $finding->resolved_at->format('d F Y H:i') }}</dd></div>
                    @endif
                </dl>
            </div>

            {{-- Recommendations --}}
            <div class="card">
                <div class="p-5 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="font-semibold text-slate-900 dark:text-white">Recommendations ({{ $finding->recommendations->count() }})</h3>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($finding->recommendations as $rec)
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-sm text-slate-700 dark:text-slate-300">{{ $rec->recommendation }}</p>
                            @php $pc = ['high' => 'badge-high', 'medium' => 'badge-medium', 'low' => 'badge-low']; @endphp
                            <span class="{{ $pc[$rec->priority] ?? 'badge-gray' }} flex-shrink-0">{{ ucfirst($rec->priority) }}</span>
                        </div>
                        <div class="mt-1 text-xs text-slate-400 dark:text-slate-500">By {{ $rec->creator->name }} · {{ $rec->created_at->format('d M Y') }}</div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-sm text-slate-500 dark:text-slate-400">No recommendations yet</div>
                    @endforelse
                </div>

                <div class="p-5 border-t border-slate-200 dark:border-slate-700 space-y-3">
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Add Recommendation</h4>
                    <textarea wire:model="recommendation" rows="3" class="form-input" placeholder="Describe the recommended action..."></textarea>
                    @error('recommendation')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                    <div class="flex gap-3">
                        <select wire:model="priority" class="form-select w-32">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                        <button wire:click="addRecommendation" class="btn-primary">Add</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="card p-5">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-3">Update Status</h3>
                <div class="space-y-2">
                    @foreach(['open' => ['badge-red', 'Mark as Open'], 'monitoring' => ['badge-yellow', 'Mark as Monitoring'], 'resolved' => ['badge-green', 'Mark as Resolved']] as $status => [$cls, $label])
                    <button wire:click="updateStatus('{{ $status }}')"
                        class="w-full text-left px-4 py-2.5 rounded-lg border text-sm transition-all {{ $finding->status === $status ? 'border-violet-400 bg-violet-50 dark:bg-violet-950 text-violet-700 dark:text-violet-300 font-medium' : 'border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            @if(auth()->user()->hasRole('admin'))
            <div class="card p-5">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-3">Create Quotation</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">Turn this finding into a quotation for the client.</p>
                <a href="{{ route('quotations.create', ['finding' => $finding->id]) }}" class="btn-primary w-full justify-center">Create Quotation</a>
            </div>
            @endif
        </div>
    </div>
</div>
