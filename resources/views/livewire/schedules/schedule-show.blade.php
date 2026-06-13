<div x-data="{ lat: null, lng: null }" x-init="
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            lat = pos.coords.latitude;
            lng = pos.coords.longitude;
            $wire.lat = lat;
            $wire.lng = lng;
        });
    }
">
    <div class="page-header">
        <div>
            <h2 class="page-title">Visit Schedule</h2>
            <p class="page-subtitle">{{ $schedule->client->company_name }} — {{ $schedule->visit_date->format('d F Y') }}</p>
        </div>
        <div class="flex gap-2">
            @if(auth()->user()->hasRole('admin'))
            <a href="{{ route('schedules.edit', $schedule) }}" class="btn-secondary">Edit</a>
            @endif
            <a href="{{ route('schedules.index') }}" class="btn-secondary">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="card p-6">
                <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div><dt class="text-slate-500 dark:text-slate-400">Client</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1">{{ $schedule->client->company_name }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Technician</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1">{{ $schedule->technician->name }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Visit Date</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1">{{ $schedule->visit_date->format('d F Y') }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Time</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1">{{ $schedule->start_time }}{{ $schedule->end_time ? ' – ' . $schedule->end_time : '' }}</dd></div>
                    @if($schedule->checked_in_at)
                    <div><dt class="text-slate-500 dark:text-slate-400">Checked In</dt><dd class="font-semibold text-emerald-600 dark:text-emerald-400 mt-1">{{ $schedule->checked_in_at->format('d M Y H:i') }}</dd></div>
                    @endif
                    @if($schedule->checked_out_at)
                    <div><dt class="text-slate-500 dark:text-slate-400">Checked Out</dt><dd class="font-semibold text-emerald-600 dark:text-emerald-400 mt-1">{{ $schedule->checked_out_at->format('d M Y H:i') }}</dd></div>
                    @endif
                    @if($schedule->notes)
                    <div class="col-span-2"><dt class="text-slate-500 dark:text-slate-400">Notes</dt><dd class="text-slate-700 dark:text-slate-300 mt-1">{{ $schedule->notes }}</dd></div>
                    @endif
                </dl>
            </div>

            @if($schedule->visitReport)
            <div class="card p-6">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-3">Visit Report</h3>
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium text-slate-900 dark:text-white">{{ $schedule->visitReport->report_number }}</div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">{{ ucfirst($schedule->visitReport->status) }}</div>
                    </div>
                    <a href="{{ route('reports.show', $schedule->visitReport) }}" class="btn-primary">View Report</a>
                </div>
            </div>
            @endif
        </div>

        <div class="space-y-4">
            @php $statusClasses = ['scheduled' => 'badge-blue', 'in_progress' => 'badge-yellow', 'completed' => 'badge-green', 'cancelled' => 'badge-gray']; @endphp

            <div class="card p-6">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-3">Status</h3>
                <span class="{{ $statusClasses[$schedule->status] ?? 'badge-gray' }} text-sm px-3 py-1">{{ ucfirst(str_replace('_', ' ', $schedule->status)) }}</span>
            </div>

            {{-- Actions --}}
            <div class="card p-6 space-y-3">
                <h3 class="font-semibold text-slate-900 dark:text-white">Actions</h3>

                @if($schedule->status === 'scheduled' && (auth()->user()->hasRole('admin') || auth()->id() === $schedule->technician_id))
                <button wire:click="$set('showCheckinModal', true)" class="btn-success w-full justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Check In
                </button>
                @endif

                @if($schedule->status === 'in_progress' && (auth()->user()->hasRole('admin') || auth()->id() === $schedule->technician_id))
                <button wire:click="$set('showCheckoutModal', true)" class="btn-primary w-full justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Check Out
                </button>
                @if(!$schedule->visitReport)
                <a href="{{ route('reports.create', $schedule) }}" class="btn-secondary w-full justify-center">
                    Create Visit Report
                </a>
                @endif
                @endif

                @if(in_array($schedule->status, ['scheduled', 'in_progress']) && auth()->user()->hasRole('admin'))
                <button wire:click="cancel" wire:confirm="Cancel this schedule?" class="btn-danger w-full justify-center">Cancel Schedule</button>
                @endif
            </div>
        </div>
    </div>

    {{-- Check In Modal --}}
    @if($showCheckinModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="card p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Check In</h3>
            <div class="space-y-4">
                <div class="text-sm text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-700 rounded-lg p-3">
                    <span x-show="lat">📍 GPS: <span x-text="lat?.toFixed(6)"></span>, <span x-text="lng?.toFixed(6)"></span></span>
                    <span x-show="!lat">Getting GPS location...</span>
                </div>
                <div>
                    <label class="form-label">Check-in Photo (Optional)</label>
                    <input wire:model="photo" type="file" accept="image/*" class="form-input">
                    @error('photo')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button wire:click="checkIn" class="btn-success flex-1 justify-center" wire:loading.attr="disabled">
                    <span wire:loading.remove>Confirm Check In</span>
                    <span wire:loading>Processing...</span>
                </button>
                <button wire:click="$set('showCheckinModal', false)" class="btn-secondary">Cancel</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Check Out Modal --}}
    @if($showCheckoutModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="card p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Check Out</h3>
            <div class="space-y-4">
                <div class="text-sm text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-700 rounded-lg p-3">
                    <span x-show="lat">📍 GPS: <span x-text="lat?.toFixed(6)"></span>, <span x-text="lng?.toFixed(6)"></span></span>
                </div>
                <div>
                    <label class="form-label">Check-out Photo (Optional)</label>
                    <input wire:model="photo" type="file" accept="image/*" class="form-input">
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button wire:click="checkOut" class="btn-primary flex-1 justify-center" wire:loading.attr="disabled">
                    <span wire:loading.remove>Confirm Check Out</span>
                    <span wire:loading>Processing...</span>
                </button>
                <button wire:click="$set('showCheckoutModal', false)" class="btn-secondary">Cancel</button>
            </div>
        </div>
    </div>
    @endif
</div>
