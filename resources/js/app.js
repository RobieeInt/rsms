// Dark mode initial state — runs before Alpine to prevent flash
const darkModeKey = 'rsms_dark_mode';
if (localStorage.getItem(darkModeKey) === 'true' ||
    (!localStorage.getItem(darkModeKey) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
}

// Register Alpine stores/data — Livewire v4 owns Alpine, use alpine:init
document.addEventListener('alpine:init', () => {
    // Signature pad store — each canvas tracked by ID to avoid state collision
    Alpine.store('signature', {
        pads: {},

        init(canvasId) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            // Match canvas resolution to its actual rendered size so coords align
            canvas.width  = canvas.offsetWidth  || 360;
            canvas.height = canvas.offsetHeight || 120;
            const ctx = canvas.getContext('2d');
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#1e293b';

            const pad = { canvas, ctx, drawing: false };
            this.pads[canvasId] = pad;

            canvas.addEventListener('mousedown',  (e) => this._start(canvasId, e));
            canvas.addEventListener('mousemove',  (e) => this._move(canvasId, e));
            canvas.addEventListener('mouseup',    ()  => this._stop(canvasId));
            canvas.addEventListener('mouseleave', ()  => this._stop(canvasId));
            canvas.addEventListener('touchstart', (e) => { e.preventDefault(); this._start(canvasId, e.touches[0]); }, { passive: false });
            canvas.addEventListener('touchmove',  (e) => { e.preventDefault(); this._move(canvasId, e.touches[0]); }, { passive: false });
            canvas.addEventListener('touchend',   ()  => this._stop(canvasId));
        },

        _pos(pad, e) {
            const rect = pad.canvas.getBoundingClientRect();
            return [
                (e.clientX - rect.left) * (pad.canvas.width  / rect.width),
                (e.clientY - rect.top)  * (pad.canvas.height / rect.height),
            ];
        },

        _start(canvasId, e) {
            const pad = this.pads[canvasId];
            if (!pad) return;
            pad.drawing = true;
            const [x, y] = this._pos(pad, e);
            pad.ctx.beginPath();
            pad.ctx.moveTo(x, y);
        },

        _move(canvasId, e) {
            const pad = this.pads[canvasId];
            if (!pad || !pad.drawing) return;
            const [x, y] = this._pos(pad, e);
            pad.ctx.lineTo(x, y);
            pad.ctx.stroke();
            pad.ctx.beginPath();
            pad.ctx.moveTo(x, y);
        },

        _stop(canvasId) {
            const pad = this.pads[canvasId];
            if (pad) pad.drawing = false;
        },

        clear(canvasId) {
            const pad = this.pads[canvasId];
            if (pad) pad.ctx.clearRect(0, 0, pad.canvas.width, pad.canvas.height);
        },

        getData(canvasId) {
            const pad = this.pads[canvasId];
            return pad ? pad.canvas.toDataURL('image/png') : null;
        }
    });

    // Toast notification system
    Alpine.data('toastStore', () => ({
        toasts: [],
        add(message, type = 'success') {
            const id = Date.now();
            this.toasts.push({ id, message, type });
            setTimeout(() => this.remove(id), 4000);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }));

    // Dark mode store
    Alpine.store('darkMode', {
        on: localStorage.getItem(darkModeKey) === 'true' ||
            (!localStorage.getItem(darkModeKey) && window.matchMedia('(prefers-color-scheme: dark)').matches),
        toggle() {
            this.on = !this.on;
            localStorage.setItem(darkModeKey, this.on);
            document.documentElement.classList.toggle('dark', this.on);
        },
        init() {
            document.documentElement.classList.toggle('dark', this.on);
        }
    });
});

// Livewire toast bridge
document.addEventListener('livewire:initialized', () => {
    Livewire.on('notify', (data) => {
        window.dispatchEvent(new CustomEvent('toast', { detail: data[0] }));
    });
});
