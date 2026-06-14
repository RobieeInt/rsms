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
        <div class="flex flex-wrap gap-2">
            @if(auth()->user()->hasRole('admin'))
            @php
                $techPhone = $schedule->technician->phone ?? null;
                $waUrl = null;
                if ($techPhone) {
                    $p = preg_replace('/[^0-9]/', '', $techPhone);
                    if (str_starts_with($p, '0')) $p = '62' . substr($p, 1);
                    elseif (!str_starts_with($p, '62')) $p = '62' . $p;
                    $waUrl = 'https://wa.me/' . $p . '?text=' . rawurlencode(\App\Notifications\TechnicianScheduleNotification::waText($schedule, 'created'));
                }
            @endphp
            @if($waUrl)
            <a href="{{ $waUrl }}" target="_blank" class="inline-flex items-center gap-1.5 py-2 px-3 text-sm rounded-lg font-medium bg-green-500 hover:bg-green-600 text-white transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WA Teknisi
            </a>
            @endif
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
