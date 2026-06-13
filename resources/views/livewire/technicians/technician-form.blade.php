<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">{{ $isEdit ? 'Edit Technician' : 'Add Technician' }}</h2>
            <p class="page-subtitle">{{ $isEdit ? 'Update technician information' : 'Add a new technician to the team' }}</p>
        </div>
        <a href="{{ route('technicians.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    <form wire:submit="save" class="max-w-2xl">
        <div class="card p-6 space-y-5">
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" class="form-input" placeholder="John Doe">
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Email Address <span class="text-red-500">*</span></label>
                    <input wire:model="email" type="email" class="form-input" placeholder="john@reconext.com">
                    @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                @if(!$isEdit)
                <div>
                    <label class="form-label">Password <span class="text-red-500">*</span></label>
                    <input wire:model="password" type="password" class="form-input" placeholder="Min. 8 characters">
                    @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                @endif
                <div>
                    <label class="form-label">Phone Number</label>
                    <input wire:model="phone" type="text" class="form-input" placeholder="+62 812 3456 7890">
                    @error('phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Position</label>
                    <input wire:model="position" type="text" class="form-input" placeholder="IT Technician">
                    @error('position')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center gap-3 pt-6">
                    <input wire:model="is_active" type="checkbox" id="is_active" class="w-4 h-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                    <label for="is_active" class="text-sm font-medium text-slate-700 dark:text-slate-300">Active</label>
                </div>
            </div>

            <div class="flex gap-3 pt-2 border-t border-slate-200 dark:border-slate-700">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ $isEdit ? 'Update Technician' : 'Create Technician' }}</span>
                    <span wire:loading>Saving...</span>
                </button>
                <a href="{{ route('technicians.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>
