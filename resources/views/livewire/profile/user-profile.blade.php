<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">My Profile</h2>
            <p class="page-subtitle">Manage your personal information and security</p>
        </div>
    </div>

    <div class="max-w-2xl space-y-6">
        <div class="card p-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Profile Information</h3>
            <form wire:submit="updateProfile" class="space-y-5">
                <div class="flex items-center gap-4">
                    <img src="{{ auth()->user()->avatar_url }}" class="w-16 h-16 rounded-xl" alt="">
                    <div class="flex-1">
                        <label class="form-label">Change Avatar</label>
                        <input wire:model="avatar" type="file" accept="image/*" class="form-input">
                        @error('avatar')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                        <input wire:model="name" type="text" class="form-input">
                        @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Email <span class="text-red-500">*</span></label>
                        <input wire:model="email" type="email" class="form-input">
                        @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <input wire:model="phone" type="text" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Position</label>
                        <input wire:model="position" type="text" class="form-input">
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Update Profile</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="card p-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Change Password</h3>
            <form wire:submit="updatePassword" class="space-y-4">
                <div>
                    <label class="form-label">Current Password</label>
                    <input wire:model="current_password" type="password" class="form-input" placeholder="••••••••">
                    @error('current_password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">New Password</label>
                    <input wire:model="new_password" type="password" class="form-input" placeholder="Min. 8 characters">
                    @error('new_password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Confirm New Password</label>
                    <input wire:model="new_password_confirmation" type="password" class="form-input" placeholder="••••••••">
                </div>
                <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                    <button type="submit" class="btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
