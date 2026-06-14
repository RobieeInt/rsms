<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">{{ $isEdit ? 'Edit Asset' : 'Add Asset' }}</h2>
            <p class="page-subtitle">{{ $isEdit ? 'Update asset information' : 'Register a new asset for a client' }}</p>
        </div>
        <a href="{{ route('assets.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    <form wire:submit="save" class="max-w-3xl">
        <div class="card p-6 space-y-6">
            <div>
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4 pb-2 border-b border-slate-200 dark:border-slate-700">Basic Information</h3>
                <div class="grid grid-cols-2 gap-5">
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
                    <div>
                        <label class="form-label">Asset Name <span class="text-red-500">*</span></label>
                        <input wire:model="asset_name" type="text" class="form-input" placeholder="e.g. Office PC #1">
                        @error('asset_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Asset Type <span class="text-red-500">*</span></label>
                        <select wire:model="asset_type" class="form-select">
                            @foreach($assetTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Location</label>
                        <input wire:model="location" type="text" class="form-input" placeholder="e.g. 2nd Floor, Room 201">
                    </div>
                    <div>
                        <label class="form-label">Health Status</label>
                        <select wire:model="health_status" class="form-select">
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4 pb-2 border-b border-slate-200 dark:border-slate-700">Hardware Details</h3>
                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">Brand</label>
                        <input wire:model="brand" type="text" class="form-input" placeholder="e.g. Dell, HP, Lenovo">
                    </div>
                    <div>
                        <label class="form-label">Model</label>
                        <input wire:model="model" type="text" class="form-input" placeholder="e.g. OptiPlex 7080">
                    </div>
                    <div>
                        <label class="form-label">Serial Number</label>
                        <input wire:model="serial_number" type="text" class="form-input" placeholder="Serial number">
                    </div>
                    <div>
                        <label class="form-label">Purchase Year</label>
                        <input wire:model="purchase_year" type="number" class="form-input" placeholder="2023" min="2000" max="{{ date('Y') }}">
                        @error('purchase_year')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">CPU</label>
                        <input wire:model="cpu" type="text" class="form-input" placeholder="e.g. Intel Core i5-10500">
                    </div>
                    <div>
                        <label class="form-label">RAM</label>
                        <input wire:model="ram" type="text" class="form-input" placeholder="e.g. 16 GB DDR4">
                    </div>
                    <div>
                        <label class="form-label">Storage</label>
                        <input wire:model="storage" type="text" class="form-input" placeholder="e.g. 512 GB SSD">
                    </div>
                    <div>
                        <label class="form-label">Operating System</label>
                        <input wire:model="operating_system" type="text" class="form-input" placeholder="e.g. Windows 11 Pro">
                    </div>
                </div>
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea wire:model="notes" rows="3" class="form-input" placeholder="Additional notes..."></textarea>
            </div>

            <div class="flex gap-3 pt-2 border-t border-slate-200 dark:border-slate-700">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ $isEdit ? 'Update Asset' : 'Create Asset' }}</span>
                    <span wire:loading>Saving...</span>
                </button>
                <a href="{{ route('assets.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>
