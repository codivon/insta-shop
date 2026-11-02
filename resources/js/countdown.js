//
// sfCountdown — «فروش ویژه» live countdown (special-sale deadline).
//
// Usage:  x-data="sfCountdown(1762054399000)"   (millisecond epoch)
// Exposes: d / h / m / s (Persian-digit strings), over (bool), pad(), left.
// Attached on window so it works inside Livewire morphs & wire:navigate,
// mirroring how jdatePicker registers itself.
//
function sfCountdown(endsMs) {
    return {
        ends: Number(endsMs) || 0,
        now: Date.now(),
        interval: null,

        // Alpine 3's lifecycle hook is init() — NOT mounted(). The old
        // mounted() hook was never called, so tick() never ran and the
        // countdown froze on whatever it computed at first paint.
        init() {
            this.tick();
            if (this.interval) clearInterval(this.interval); // re-init guard
            this.interval = setInterval(() => this.tick(), 1000);
        },

        destroy() {
            if (this.interval) clearInterval(this.interval);
        },

        tick() {
            this.now = Date.now();
        },

        get left() {
            return Math.max(0, Math.floor((this.ends - this.now) / 1000));
        },

        get over() {
            return this.left <= 0;
        },

        get d() { return this.fa(Math.floor(this.left / 86400)); },
        get h() { return this.fa(Math.floor((this.left % 86400) / 3600)); },
        get m() { return this.fa(Math.floor((this.left % 3600) / 60)); },
        get s() { return this.fa(this.left % 60); },

        pad(n) {
            return this.fa(String(n).padStart(2, '0'));
        },

        fa(n) {
            return String(n).replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);
        },
    };
}

window.sfCountdown = sfCountdown;
