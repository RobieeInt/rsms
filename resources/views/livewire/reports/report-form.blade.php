<div x-data="reportForm()">
    <div class="page-header">
        <div>
            <h2 class="page-title">{{ $isEdit ? 'Edit Report' : 'Create Visit Report' }}</h2>
            <p class="page-subtitle">{{ $schedule->client->company_name }} — {{ $schedule->visit_date->format('d F Y') }}</p>
        </div>
        <a href="{{ route('schedules.show', $schedule) }}" class="btn-secondary">Back</a>
    </div>

    <form wire:submit.prevent="saveReport('draft')" class="space-y-6">

        {{-- Summary --}}
        <div class="card p-4 lg:p-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Visit Summary</h3>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Summary</label>
                    <textarea wire:model="summary" rows="3" class="form-input" placeholder="Brief summary of the visit..."></textarea>
                </div>
                <div>
                    <label class="form-label">Overall Notes</label>
                    <textarea wire:model="overall_notes" rows="3" class="form-input" placeholder="General observations and notes..."></textarea>
                </div>
            </div>
        </div>

        {{-- Asset Selection --}}
        @if($availableAssets->count() > 0)
        <div class="card p-4 lg:p-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-1">Pilih Aset yang Diperiksa</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Centang aset yang dikunjungi pada kunjungan ini.</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach($availableAssets as $asset)
                <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition
                    {{ in_array($asset->id, $selectedAssetIds)
                        ? 'border-violet-400 bg-violet-50 dark:bg-violet-900/20 dark:border-violet-500'
                        : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600' }}">
                    <input type="checkbox"
                        wire:model.live="selectedAssetIds"
                        value="{{ $asset->id }}"
                        class="rounded text-violet-600 focus:ring-violet-500">
                    <div class="min-w-0">
                        <div class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ $asset->asset_name }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $asset->asset_code }}</div>
                    </div>
                </label>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Asset Checklists — only for selected assets --}}
        @foreach($assetChecklists as $assetId => $checklist)
        @if(in_array($assetId, $selectedAssetIds))
        <div class="card p-4 lg:p-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">
                <svg class="w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                {{ $checklist['asset_name'] }}
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700">
                            <th class="text-left py-2 font-medium text-slate-600 dark:text-slate-400 w-48">Item</th>
                            <th class="py-2 text-center font-medium text-slate-600 dark:text-slate-400 w-24">Passed</th>
                            <th class="py-2 text-center font-medium text-slate-600 dark:text-slate-400 w-24">Failed</th>
                            <th class="py-2 text-center font-medium text-slate-600 dark:text-slate-400 w-24">N/A</th>
                            <th class="text-left py-2 font-medium text-slate-600 dark:text-slate-400">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach(['storage_check' => 'Storage Check', 'ram_check' => 'RAM Usage', 'temp_files_cleanup' => 'Temp Files Cleanup', 'ssd_health_check' => 'SSD Health', 'windows_update_check' => 'Windows Update', 'driver_check' => 'Driver Check', 'virus_scan' => 'Virus Scan', 'printer_check' => 'Printer Check', 'hardware_cleaning' => 'Hardware Cleaning'] as $field => $label)
                        <tr>
                            <td class="py-2.5 font-medium text-slate-700 dark:text-slate-300">{{ $label }}</td>
                            <td class="py-2.5 text-center">
                                <input type="radio" wire:model="assetChecklists.{{ $assetId }}.{{ $field }}" value="passed" class="text-emerald-500 focus:ring-emerald-500">
                            </td>
                            <td class="py-2.5 text-center">
                                <input type="radio" wire:model="assetChecklists.{{ $assetId }}.{{ $field }}" value="failed" class="text-red-500 focus:ring-red-500">
                            </td>
                            <td class="py-2.5 text-center">
                                <input type="radio" wire:model="assetChecklists.{{ $assetId }}.{{ $field }}" value="na" class="text-slate-400 focus:ring-slate-400">
                            </td>
                            <td class="py-2.5">
                                <input type="text" wire:model="assetChecklists.{{ $assetId }}.{{ $field }}_notes" class="form-input py-1 text-xs" placeholder="Notes...">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <label class="form-label text-xs">General Notes for this Asset</label>
                <textarea wire:model="assetChecklists.{{ $assetId }}.general_notes" rows="2" class="form-input text-sm" placeholder="Additional observations..."></textarea>
            </div>
        </div>
        @endif
        @endforeach

        {{-- Network Checklist --}}
        <div class="card p-4 lg:p-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Network Checklist</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700">
                            <th class="text-left py-2 font-medium text-slate-600 dark:text-slate-400 w-48">Item</th>
                            <th class="py-2 text-center font-medium text-slate-600 dark:text-slate-400 w-24">Passed</th>
                            <th class="py-2 text-center font-medium text-slate-600 dark:text-slate-400 w-24">Failed</th>
                            <th class="py-2 text-center font-medium text-slate-600 dark:text-slate-400 w-24">N/A</th>
                            <th class="text-left py-2 font-medium text-slate-600 dark:text-slate-400">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach(['internet_connectivity' => 'Internet Connectivity', 'speed_test' => 'Speed Test', 'router_check' => 'Router Check', 'lan_cable_check' => 'LAN Cable Check', 'ip_conflict_check' => 'IP Conflict Check'] as $field => $label)
                        <tr>
                            <td class="py-2.5 font-medium text-slate-700 dark:text-slate-300">{{ $label }}</td>
                            <td class="py-2.5 text-center"><input type="radio" wire:model="networkChecklist.{{ $field }}" value="passed" class="text-emerald-500 focus:ring-emerald-500"></td>
                            <td class="py-2.5 text-center"><input type="radio" wire:model="networkChecklist.{{ $field }}" value="failed" class="text-red-500 focus:ring-red-500"></td>
                            <td class="py-2.5 text-center"><input type="radio" wire:model="networkChecklist.{{ $field }}" value="na" class="text-slate-400 focus:ring-slate-400"></td>
                            <td class="py-2.5"><input type="text" wire:model="networkChecklist.{{ $field }}_notes" class="form-input py-1 text-xs" placeholder="Notes..."></td>
                        </tr>
                        @endforeach
                        <tr>
                            <td class="py-2.5 font-medium text-slate-700 dark:text-slate-300">Download Speed</td>
                            <td colspan="3"></td>
                            <td class="py-2.5"><input type="text" wire:model="networkChecklist.download_speed" class="form-input py-1 text-xs" placeholder="e.g. 50 Mbps"></td>
                        </tr>
                        <tr>
                            <td class="py-2.5 font-medium text-slate-700 dark:text-slate-300">Upload Speed</td>
                            <td colspan="3"></td>
                            <td class="py-2.5"><input type="text" wire:model="networkChecklist.upload_speed" class="form-input py-1 text-xs" placeholder="e.g. 20 Mbps"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <label class="form-label text-xs">Network Notes</label>
                <textarea wire:model="networkChecklist.general_notes" rows="2" class="form-input text-sm" placeholder="Network observations..."></textarea>
            </div>
        </div>

        {{-- Photo Upload --}}
        <div class="card p-4 lg:p-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Photo Documentation</h3>
            <div>
                <label class="form-label">Upload Photos (Before / After / General)</label>
                <input wire:model="photos" type="file" multiple accept="image/*" class="form-input">
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">You can select multiple photos</p>
            </div>
            <div wire:loading wire:target="photos" class="mt-2 text-sm text-slate-500 dark:text-slate-400">Uploading...</div>
        </div>

        {{-- Signatures --}}
        <div class="card p-4 lg:p-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Tanda Tangan</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6">
                <div>
                    <label class="form-label">Tanda Tangan Teknisi</label>
                    <canvas id="techSignature" width="360" height="140"
                        class="border border-slate-300 dark:border-slate-600 rounded-lg bg-white w-full cursor-crosshair touch-none"
                        style="height: 140px;"
                        x-init="$store.signature.init('techSignature')"
                        @mouseup="$wire.saveSignature('technician_signature', $store.signature.getData('techSignature'))"
                        @touchend="$wire.saveSignature('technician_signature', $store.signature.getData('techSignature'))">
                    </canvas>
                    <button type="button" @click="$store.signature.clear('techSignature'); $wire.saveSignature('technician_signature', '')" class="mt-1 text-xs text-red-500 hover:underline">Hapus</button>
                </div>
                <div>
                    <label class="form-label">Tanda Tangan Klien</label>
                    <canvas id="clientSignature" width="360" height="140"
                        class="border border-slate-300 dark:border-slate-600 rounded-lg bg-white w-full cursor-crosshair touch-none"
                        style="height: 140px;"
                        x-init="$store.signature.init('clientSignature')"
                        @mouseup="$wire.saveSignature('client_signature', $store.signature.getData('clientSignature'))"
                        @touchend="$wire.saveSignature('client_signature', $store.signature.getData('clientSignature'))">
                    </canvas>
                    <button type="button" @click="$store.signature.clear('clientSignature'); $wire.saveSignature('client_signature', '')" class="mt-1 text-xs text-red-500 hover:underline">Hapus</button>
                </div>
            </div>
            <div class="mt-4">
                <label class="form-label">Ditandatangani Oleh (Klien)</label>
                <input wire:model="client_signed_by" type="text" class="form-input max-w-sm" placeholder="Nama perwakilan klien">
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-wrap gap-3">
            <button type="button" wire:click="saveReport('draft')" class="btn-secondary flex-1 sm:flex-none justify-center" wire:loading.attr="disabled">Simpan Draft</button>
            <button type="button" wire:click="saveReport('completed')" class="btn-primary flex-1 sm:flex-none justify-center" wire:loading.attr="disabled">
                <span wire:loading.remove>Selesaikan Report</span>
                <span wire:loading>Menyimpan...</span>
            </button>
            <a href="{{ route('schedules.show', $schedule) }}" class="btn-secondary w-full sm:w-auto justify-center">Batal</a>
        </div>
    </form>
</div>

<script>
function reportForm() {
    return {};
}
</script>
