<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">{{ $isEdit ? 'Edit Finding' : 'New Finding' }}</h2>
        </div>
        <a href="{{ route('findings.index') }}" class="btn-secondary">Back</a>
    </div>

    <form wire:submit="save" class="max-w-2xl">
        <div class="card p-6 space-y-5">
            <div>
                <label class="form-label">Client <span class="text-red-500">*</span></label>
                <select wire:model.live="client_id" class="form-select">
                    <option value="">Select client...</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                    @endforeach
                </select>
                @error('client_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            @if(!empty($clientReports))
            <div>
                <label class="form-label">Kaitkan ke Visit Report</label>
                <select wire:model="visit_report_id" class="form-select">
                    <option value="">Tidak dikaitkan ke report</option>
                    @foreach($clientReports as $r)
                    <option value="{{ $r['id'] }}">{{ $r['label'] }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if(!empty($clientAssets))
            <div>
                <label class="form-label">Aset Terkait</label>
                <select wire:model="asset_id" class="form-select">
                    <option value="">Tidak spesifik ke aset</option>
                    @foreach($clientAssets as $asset)
                    <option value="{{ $asset['id'] }}">{{ $asset['asset_name'] }} ({{ $asset['asset_code'] }})</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="form-label">Title <span class="text-red-500">*</span></label>
                <input wire:model="title" type="text" class="form-input" placeholder="Brief description of the issue">
                @error('title')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Category</label>
                <input wire:model="category" type="text" class="form-input" placeholder="e.g. Hardware, Software, Network">
            </div>

            <div>
                <label class="form-label">Description</label>
                <textarea wire:model="description" rows="4" class="form-input" placeholder="Detailed description of the finding..."></textarea>
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Severity <span class="text-red-500">*</span></label>
                    <select wire:model="severity" class="form-select">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Status <span class="text-red-500">*</span></label>
                    <select wire:model="status" class="form-select">
                        <option value="open">Open</option>
                        <option value="monitoring">Monitoring</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-3 pt-2 border-t border-slate-200 dark:border-slate-700">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ $isEdit ? 'Update Finding' : 'Create Finding' }}</span>
                    <span wire:loading>Saving...</span>
                </button>
                <a href="{{ route('findings.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>
