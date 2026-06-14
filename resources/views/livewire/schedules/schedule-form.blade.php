<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">{{ $isEdit ? 'Edit Schedule' : 'Create Schedule' }}</h2>
            <p class="page-subtitle">{{ $isEdit ? 'Update schedule details' : 'Schedule a new maintenance visit' }}</p>
        </div>
        <a href="{{ route('schedules.index') }}" class="btn-secondary">Back</a>
    </div>

    <form wire:submit="save" class="max-w-2xl">
        <div class="card p-6 space-y-5">
            <div>
                <label class="form-label">Client <span class="text-red-500">*</span></label>
                <select x-select wire:model="client_id" class="form-select">
                    <option value="">Select client...</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                    @endforeach
                </select>
                @error('client_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            @if(auth()->user()->hasRole('admin'))
            <div>
                <label class="form-label">Assigned Technician <span class="text-red-500">*</span></label>
                <select x-select wire:model="technician_id" class="form-select">
                    <option value="">Select technician...</option>
                    @foreach($technicians as $tech)
                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                    @endforeach
                </select>
                @error('technician_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            @else
            <div>
                <label class="form-label">Technician</label>
                <input type="text" value="{{ auth()->user()->name }}" class="form-input bg-slate-50 dark:bg-slate-800" disabled>
            </div>
            @endif

            <div>
                <label class="form-label">Visit Date <span class="text-red-500">*</span></label>
                <input wire:model="visit_date" type="date" class="form-input">
                @error('visit_date')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Start Time <span class="text-red-500">*</span></label>
                    <input wire:model="start_time" type="time" class="form-input">
                    @error('start_time')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">End Time</label>
                    <input wire:model="end_time" type="time" class="form-input">
                </div>
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea wire:model="notes" rows="3" class="form-input" placeholder="Visit notes or special instructions..."></textarea>
            </div>

            <div class="flex gap-3 pt-2 border-t border-slate-200 dark:border-slate-700">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ $isEdit ? 'Update Schedule' : 'Create Schedule' }}</span>
                    <span wire:loading>Saving...</span>
                </button>
                <a href="{{ route('schedules.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>
