<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">{{ $report->report_number }}</h2>
            <p class="page-subtitle">{{ $report->client->company_name }} — {{ $report->schedule->visit_date->format('d F Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('pdf.report', $report) }}" target="_blank" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Download PDF
            </a>
            <a href="{{ route('reports.edit', $report) }}" class="btn-secondary">Edit</a>
            <a href="{{ route('reports.index') }}" class="btn-secondary">Back</a>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Report Header --}}
        <div class="card p-6">
            <div class="grid grid-cols-3 gap-6 text-sm">
                <div><dt class="text-slate-500 dark:text-slate-400">Client</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1">{{ $report->client->company_name }}</dd></div>
                <div><dt class="text-slate-500 dark:text-slate-400">Technician</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1">{{ $report->technician->name }}</dd></div>
                <div><dt class="text-slate-500 dark:text-slate-400">Status</dt><dd class="mt-1">
                    @php $sc = ['draft' => 'badge-yellow', 'completed' => 'badge-blue', 'signed' => 'badge-green']; @endphp
                    <span class="{{ $sc[$report->status] ?? 'badge-gray' }}">{{ ucfirst($report->status) }}</span>
                </dd></div>
                @if($report->summary)
                <div class="col-span-3"><dt class="text-slate-500 dark:text-slate-400">Summary</dt><dd class="text-slate-700 dark:text-slate-300 mt-1">{{ $report->summary }}</dd></div>
                @endif
            </div>
        </div>

        {{-- Asset Checklists --}}
        @foreach($report->assetChecklists as $checklist)
        <div class="card p-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">{{ $checklist->asset->asset_name }}</h3>
            <div class="grid grid-cols-3 gap-3 text-sm">
                @foreach(['storage_check' => 'Storage', 'ram_check' => 'RAM', 'temp_files_cleanup' => 'Temp Files', 'ssd_health_check' => 'SSD Health', 'windows_update_check' => 'Windows Update', 'driver_check' => 'Drivers', 'virus_scan' => 'Virus Scan', 'printer_check' => 'Printer', 'hardware_cleaning' => 'HW Cleaning'] as $field => $label)
                <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 dark:bg-slate-750">
                    <span class="text-slate-600 dark:text-slate-400">{{ $label }}</span>
                    @php $v = $checklist->$field; $vc = ['passed' => 'text-emerald-600', 'failed' => 'text-red-500', 'na' => 'text-slate-400']; @endphp
                    <span class="font-semibold {{ $vc[$v] ?? '' }}">{{ strtoupper($v) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        {{-- Network Checklist --}}
        @if($report->networkChecklist)
        <div class="card p-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Network Checklist</h3>
            <div class="grid grid-cols-3 gap-3 text-sm">
                @foreach(['internet_connectivity' => 'Internet', 'speed_test' => 'Speed Test', 'router_check' => 'Router', 'lan_cable_check' => 'LAN Cable', 'ip_conflict_check' => 'IP Conflict'] as $field => $label)
                <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 dark:bg-slate-750">
                    <span class="text-slate-600 dark:text-slate-400">{{ $label }}</span>
                    @php $v = $report->networkChecklist->$field; $vc = ['passed' => 'text-emerald-600', 'failed' => 'text-red-500', 'na' => 'text-slate-400']; @endphp
                    <span class="font-semibold {{ $vc[$v] ?? '' }}">{{ strtoupper($v) }}</span>
                </div>
                @endforeach
            </div>
            @if($report->networkChecklist->download_speed || $report->networkChecklist->upload_speed)
            <div class="mt-3 text-sm text-slate-600 dark:text-slate-400">
                Speed: ↓ {{ $report->networkChecklist->download_speed ?? '-' }} / ↑ {{ $report->networkChecklist->upload_speed ?? '-' }}
            </div>
            @endif
        </div>
        @endif

        {{-- Photos --}}
        @if($report->photos->count())
        <div class="card p-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Photos ({{ $report->photos->count() }})</h3>
            <div class="grid grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($report->photos as $photo)
                <a href="{{ $photo->url }}" target="_blank" class="group relative aspect-square rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-700">
                    <img src="{{ $photo->url }}" alt="{{ $photo->caption }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 p-2">
                        <span class="badge {{ $photo->photo_type === 'before' ? 'badge-yellow' : ($photo->photo_type === 'after' ? 'badge-green' : 'badge-gray') }} text-xs">{{ ucfirst($photo->photo_type) }}</span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Findings --}}
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-900 dark:text-white">Findings ({{ $report->findings->count() }})</h3>
                <a href="{{ route('findings.create', ['report_id' => $report->id]) }}" class="btn-primary py-1.5 px-3 text-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Finding
                </a>
            </div>
            @if($report->findings->count())
            <div class="space-y-3">
                @foreach($report->findings as $finding)
                <div class="flex items-start gap-3 p-3 rounded-lg border border-slate-200 dark:border-slate-700">
                    @php
                    $sevColor = ['critical' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                 'high'     => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                 'medium'   => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                 'low'      => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'];
                    $stColor  = ['open'       => 'badge-red',
                                 'monitoring' => 'badge-yellow',
                                 'resolved'   => 'badge-green'];
                    @endphp
                    <span class="shrink-0 mt-0.5 px-2 py-0.5 rounded text-xs font-bold {{ $sevColor[$finding->severity] ?? '' }}">
                        {{ strtoupper($finding->severity) }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-medium text-slate-900 dark:text-white text-sm">{{ $finding->title }}</span>
                            @if($finding->category)
                            <span class="text-xs text-slate-400">{{ $finding->category }}</span>
                            @endif
                            <span class="{{ $stColor[$finding->status] ?? 'badge-gray' }}">{{ ucfirst($finding->status) }}</span>
                        </div>
                        @if($finding->description)
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $finding->description }}</p>
                        @endif
                    </div>
                    <a href="{{ route('findings.show', $finding) }}" class="shrink-0 btn-secondary py-1 px-2.5 text-xs">Detail</a>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-slate-400 dark:text-slate-500">Belum ada finding untuk report ini.</p>
            @endif
        </div>

        {{-- Signatures --}}
        @if($report->technician_signature || $report->client_signature)
        <div class="card p-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Signatures</h3>
            <div class="grid grid-cols-2 gap-6">
                @if($report->technician_signature)
                <div>
                    <div class="text-sm text-slate-500 dark:text-slate-400 mb-2">Technician: {{ $report->technician->name }}</div>
                    <img src="{{ $report->technician_signature }}" alt="Technician Signature" class="border border-slate-200 dark:border-slate-700 rounded-lg bg-white h-20 object-contain">
                </div>
                @endif
                @if($report->client_signature)
                <div>
                    <div class="text-sm text-slate-500 dark:text-slate-400 mb-2">Client: {{ $report->client_signed_by }}</div>
                    <img src="{{ $report->client_signature }}" alt="Client Signature" class="border border-slate-200 dark:border-slate-700 rounded-lg bg-white h-20 object-contain">
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
