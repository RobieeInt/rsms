<div>
    <div class="page-header">
        <div>
            <h2 class="page-title">Notifikasi</h2>
            <p class="page-subtitle">{{ auth()->user()->belum dibacaNotifications->count() }} belum dibaca</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="markAllRead" class="btn-secondary">Tandai Semua Dibaca</button>
        </div>
    </div>

    <div class="card mb-4 p-4 flex items-center gap-4">
        <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 cursor-pointer">
            <input wire:model.live="belum dibacaOnly" type="checkbox" class="w-4 h-4 rounded border-slate-300 text-stone-600 focus:ring-stone-500">
            Tampilkan belum dibaca saja
        </label>
    </div>

    <div class="card divide-y divide-slate-100 dark:divide-slate-700">
        @forelse($notifications as $notification)
        @php
            $dotColor = match($notification->data['type'] ?? 'info') {
                'success' => 'bg-emerald-500',
                'warning' => 'bg-amber-500',
                'danger'  => 'bg-red-500',
                default   => 'bg-stone-500',
            };
            $url = $notification->data['url'] ?? null;
        @endphp
        <div class="flex items-start gap-4 p-4 {{ is_null($notification->read_at) ? 'bg-stone-50 dark:bg-stone-950/30' : '' }}"
             wire:click="markRead('{{ $notification->id }}')" style="cursor:default">
            <div class="w-2 h-2 rounded-full mt-2 flex-shrink-0 {{ is_null($notification->read_at) ? $dotColor : 'bg-slate-300 dark:bg-slate-600' }}"></div>
            <div class="flex-1">
                @if($url)
                <a href="{{ $url }}" class="text-sm font-medium text-slate-900 dark:text-white hover:text-stone-600 dark:hover:text-stone-400">{{ $notification->data['title'] ?? 'Notification' }}</a>
                @else
                <div class="text-sm font-medium text-slate-900 dark:text-white">{{ $notification->data['title'] ?? 'Notification' }}</div>
                @endif
                <div class="text-sm text-slate-600 dark:text-slate-400 mt-0.5">{{ $notification->data['message'] ?? '' }}</div>
                <div class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $notification->created_at->diffForHumans() }}</div>
            </div>
            @if(is_null($notification->read_at))
            <div class="w-2 h-2 rounded-full bg-stone-400 flex-shrink-0 mt-2"></div>
            @endif
        </div>
        @empty
        <div class="py-16 text-center">
            <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <p class="text-slate-500 dark:text-slate-400">Belum ada notifikasi</p>
        </div>
        @endforelse
    </div>
    @if($notifications->hasPages())
    <div class="mt-4">{{ $notifications->links() }}</div>
    @endif
</div>
