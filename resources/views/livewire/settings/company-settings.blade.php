<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">Company Settings</h2>
            <p class="page-subtitle">Configure company information used in PDFs and emails</p>
        </div>
    </div>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="card p-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Company Information</h3>
            <div class="space-y-5">
                @if($settings->logo_url)
                <div>
                    <label class="form-label">Current Logo</label>
                    <img src="{{ $settings->logo_url }}" alt="Logo" class="h-16 object-contain rounded-lg border border-slate-200 dark:border-slate-700 p-2">
                </div>
                @endif
                <div>
                    <label class="form-label">Upload Logo</label>
                    <input wire:model="logo" type="file" accept="image/*" class="form-input">
                    @error('logo')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Company Name <span class="text-red-500">*</span></label>
                    <input wire:model="company_name" type="text" class="form-input">
                    @error('company_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">Email</label>
                        <input wire:model="email" type="email" class="form-input" placeholder="info@company.com">
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <input wire:model="phone" type="text" class="form-input" placeholder="+62 21 ...">
                    </div>
                </div>
                <div>
                    <label class="form-label">Address</label>
                    <textarea wire:model="address" rows="3" class="form-input" placeholder="Full company address"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">Website</label>
                        <input wire:model="website" type="url" class="form-input" placeholder="https://...">
                    </div>
                    <div>
                        <label class="form-label">Tax Number (NPWP)</label>
                        <input wire:model="tax_number" type="text" class="form-input" placeholder="01.234.567.8-901.000">
                    </div>
                </div>
            </div>
        </div>

        <div class="card p-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Bank Account</h3>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Bank Name</label>
                    <input wire:model="bank_name" type="text" class="form-input" placeholder="e.g. Bank BCA">
                </div>
                <div>
                    <label class="form-label">Account Number</label>
                    <input wire:model="bank_account_number" type="text" class="form-input" placeholder="1234567890">
                </div>
                <div>
                    <label class="form-label">Account Holder</label>
                    <input wire:model="bank_account_holder" type="text" class="form-input" placeholder="PT Company Name">
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove>Save Settings</span>
                <span wire:loading>Saving...</span>
            </button>
        </div>
    </form>
</div>
