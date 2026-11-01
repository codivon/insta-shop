//
// productGallery — touch-swipe gallery + fullscreen zoom modal for the
// single product page. Pure Alpine (scroll-snap), so swiping between the
// images is instant — no Livewire roundtrip per picture.
//
// Usage:  x-data="productGallery({{ count }})"
// Refs:   $refs.track (main slider) / $refs.zoomTrack (fullscreen slider)
//
// RTL notes: the page is dir="rtl" so in every modern browser the native
// scrollLeft of the track is 0 at the first (rightmost) slide and goes
// NEGATIVE while swiping to the left — hence Math.abs() everywhere and
// scrollTo({left: -i * width}).
//
function productGallery(count) {
    return {
        count: Number(count) || 0,
        active: 0,          // main slider index
        zoom: false,        // fullscreen modal open?
        zoomIndex: 0,       // slider index inside the modal

        mounted() {
            this.onKey = (event) => {
                if (!this.zoom) return;

                if (event.key === 'Escape') this.closeZoom();

                // RTL visual direction: ← = تصویر بعدی
                if (event.key === 'ArrowLeft') this.goToZoom(Math.min(this.zoomIndex + 1, this.count - 1));
                if (event.key === 'ArrowRight') this.goToZoom(Math.max(this.zoomIndex - 1, 0));
            };

            window.addEventListener('keydown', this.onKey);
        },

        destroy() {
            window.removeEventListener('keydown', this.onKey);
        },

        fa(n) {
            return Number(n).toLocaleString('fa-IR');
        },

        /* ── scroll-position → active index ── */

        onTrackScroll() {
            const el = this.$refs.track;

            if (!el || !el.clientWidth) return;

            const idx = Math.round(Math.abs(el.scrollLeft) / el.clientWidth);

            if (idx !== this.active) this.active = idx;
        },

        onZoomScroll() {
            const el = this.$refs.zoomTrack;

            if (!el || !el.clientWidth) return;

            const idx = Math.round(Math.abs(el.scrollLeft) / el.clientWidth);

            if (idx !== this.zoomIndex) this.zoomIndex = idx;
        },

        /* ── navigation ── */

        goTo(i) {
            this.scrollTo(this.$refs.track, i);
        },

        goToZoom(i) {
            this.scrollTo(this.$refs.zoomTrack, i);
        },

        scrollTo(el, i, smooth = true) {
            if (!el) return;

            el.scrollTo({
                left: -i * el.clientWidth,
                behavior: smooth ? 'smooth' : 'auto',
            });
        },

        /* ── fullscreen zoom modal ── */

        openZoom() {
            this.zoomIndex = this.active;
            this.zoom = true;

            this.$nextTick(() => this.scrollTo(this.$refs.zoomTrack, this.zoomIndex, false));
        },

        closeZoom() {
            this.zoom = false;
        },
    };
}

window.productGallery = productGallery;
