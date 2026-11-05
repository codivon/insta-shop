{{-- ═══════════════════════════════════════════════════════════════
  Instagram-style story viewer (nested on the customer home page).
  • segmented progress bars — images 5s, videos follow real playback
  • tap LEFT = next · tap RIGHT = prev (RTL, client v0.17.7) ·
    swipe down = close · ESC closes
  • STOP button (pause/resume) · video stories: MUTE toggle
  • long-press/hold opens NO system/Google menu on the media
  • shop meta (avatar + name + «چند لحظه پیش»)
  • caption · attached PRODUCT card · external link WITH its title
  • likes + comments per-story togglable (seller/admin) — customers
    only; guests get a hint toast
  data-ptr-ignore keeps the pull-to-refresh module away from it.
  The outer <div> is the persistent Livewire single root (the overlay
  renders inside it only while a story is open).
═══════════════════════════════════════════════════════════════ --}}
<div>
@if ($isOpen && $current)
<div data-ptr-ignore
         x-data="{
            p: 0, prev: -1, paused: false, stopped: false, muted: true, y0: null,
            hold: false, holdT: null,
            tick() {
                if ($wire.shopId === null) return;
                if (this.prev !== $wire.index) { this.prev = $wire.index; this.p = 0; this.stopped = false; this.paused = false; this.hold = false; clearTimeout(this.holdT); this.resumeVideo(); return; }
                if (this.hold || this.paused || this.stopped) return;
                if (this.$refs.storyVideo) return; // video drives p via timeupdate
                this.p += 1;
                if (this.p >= 100) { this.p = 100; this.go('next'); }
            },
            go(dir) {
                // one tap = one commit — reset the bar NOW and freeze the
                // tick's auto-advance until the morph lands (client v0.17.15:
                // «درصد پیشرفت جابه‌جا می‌شود یا از استوری می‌پرد بیرون» —
                // the 50ms tick kept advancing the OLD story while the commit
                // was in flight and could fire $wire.next() again at p=100)
                this.p = 0;
                this.hold = dir === 'next';
                clearTimeout(this.holdT);
                if (this.hold) this.holdT = setTimeout(() => { this.hold = false }, 1600);
                // serialize commits (client v0.17.15: «دکمه‌ها به خوبی کار
                // نمی‌کنند» — E2E showed 3 rapid taps advanced only 2 stories:
                // Livewire CANCELS a pending superseded commit, so the middle
                // tap was simply lost). Chaining every call behind the previous
                // promise means a new commit is never created while one is in
                // flight → nothing to cancel → every tap counts (they drain
                // sequentially, exactly like Instagram's queued skips).
                this.chain = (this.chain || Promise.resolve())
                    .then(() => $wire[dir]())
                    .catch(() => {});
            },
            toggleStop() {
                this.stopped = !this.stopped;
                const v = this.$refs.storyVideo;
                if (v) { this.stopped ? v.pause() : v.play().catch(() => {}); }
            },
            toggleMute() {
                this.muted = !this.muted;
                const v = this.$refs.storyVideo;
                if (v) v.muted = this.muted;
            },
            resumeVideo() {
                const v = this.$refs.storyVideo;
                if (v) { v.muted = this.muted; v.play().catch(() => {}); }
            }
         }"
         x-init="setInterval(() => tick(), 50)"
         x-effect="if ($refs.storyVideo) $refs.storyVideo.muted = muted"
         @keydown.escape.window="$wire.close()"
         x-on:touchstart.passive="y0 = $event.touches[0].clientY"
         x-on:touchmove.passive="if (y0 !== null && $event.touches[0].clientY - y0 > 90) { y0 = null; $wire.close(); }"
         class="fixed inset-0 z-[80] flex flex-col bg-black ip-fade-up" role="dialog" aria-modal="true" aria-label="نمایش استوری">

        {{-- ── progress segments ── --}}
        <div class="absolute inset-x-0 top-0 z-20 flex gap-1 px-3 pt-3" style="padding-top: calc(12px + env(safe-area-inset-top, 0px));">
            @foreach ($stories as $s)
                <div class="h-[3px] flex-1 overflow-hidden rounded-full bg-white/30">
                    @if ($loop->index < $index)
                        <div class="h-full rounded-full bg-white" style="width:100%"></div>
                    @elseif ($loop->index === $index)
                        <div class="h-full rounded-full bg-white" :style="'width:' + p + '%'"></div>
                    @else
                        <div class="h-full rounded-full bg-white" style="width:0%"></div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- ── shop meta + stop/mute + close ── --}}
        <div class="absolute inset-x-0 top-0 z-20 flex items-center justify-between gap-2 px-3 pb-2 pt-8" style="padding-top: calc(28px + env(safe-area-inset-top, 0px));">
            <div class="flex min-w-0 items-center gap-2.5">
                <span class="grid size-9 shrink-0 place-items-center overflow-hidden rounded-full bg-white/15 p-[2px]">
                    @if ($current->shop->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($current->shop->logo_path) }}" alt="" class="size-full rounded-full object-cover">
                    @else
                        <span class="grid size-full place-items-center rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-[11px] font-black text-white">{{ mb_substr($current->shop->name, 0, 1) }}</span>
                    @endif
                </span>
                <span class="min-w-0">
                    <span class="block truncate text-xs font-extrabold text-white">{{ $current->shop->name }}</span>
                    <span class="block text-[10px] text-white/70">{{ \App\Support\JDate::ago($current->starts_at) }}</span>
                </span>
            </div>
            <div class="flex shrink-0 items-center gap-1">
                {{-- stop / resume (pause the story progression) --}}
                <button type="button" x-on:click="toggleStop" aria-label="{{ 'توقف یا ادامهٔ استوری' }}"
                        class="grid size-9 place-items-center rounded-full text-white/90 transition hover:bg-white/10 active:scale-90">
                    <span x-show="!stopped"><x-icon name="pause" class="size-4.5" /></span>
                    <span x-show="stopped" x-cloak><x-icon name="play" class="size-4.5" /></span>
                </button>
                {{-- mute toggle — video stories only --}}
                @if ($current->type === \App\Models\Story::TYPE_VIDEO)
                    <button type="button" x-on:click="toggleMute" aria-label="قطع یا وصل صدا"
                            class="grid size-9 place-items-center rounded-full text-white/90 transition hover:bg-white/10 active:scale-90">
                        <span x-show="muted"><x-icon name="volume-x" class="size-4.5" /></span>
                        <span x-show="!muted" x-cloak><x-icon name="volume-2" class="size-4.5" /></span>
                    </button>
                @endif
                <button type="button" wire:click="close" aria-label="بستن استوری"
                        class="grid size-9 shrink-0 place-items-center rounded-full text-white/90 transition hover:bg-white/10 active:scale-90">
                    <x-icon name="x" class="size-5" />
                </button>
            </div>
        </div>

        {{-- ── media + tap zones (hold = pause · NO context/long-press menu) ── --}}
        <div class="relative flex-1 overflow-hidden"
             x-on:pointerdown="if ($event.target.closest('[data-tap]')) return; paused = true; const v = $refs.storyVideo; if (v) v.pause()"
             x-on:pointerup="paused = false; const v = $refs.storyVideo; if (v && !stopped) v.play().catch(() => {})"
             x-on:pointerleave="paused = false"
             x-on:contextmenu.prevent
             x-on:dragstart.prevent>
            @if ($current->type === \App\Models\Story::TYPE_VIDEO && $current->videoUrl())
                <video x-ref="storyVideo"
                       src="{{ $current->videoUrl() }}"
                       autoplay muted playsinline preload="auto"
                       x-on:ended="go('next')"
                       x-on:timeupdate="const v = $refs.storyVideo; if (v && v.duration > 0) p = Math.min(99, v.currentTime / v.duration * 100)"
                       x-on:error="p = 100; setTimeout(() => go('next'), 700)"
                       class="story-media absolute inset-0 size-full bg-black object-contain"
                       draggable="false" aria-label="ویدیوی استوری {{ $current->shop->name }}"></video>
            @else
                <img src="{{ $current->mediaUrl() }}" alt="استوری {{ $current->shop->name }}"
                     class="story-media absolute inset-0 size-full object-contain" draggable="false"
                     x-bind:class="paused ? 'opacity-95' : ''">
            @endif

            {{-- tap zones — RTL: RIGHT third = previous, LEFT third = next.
                 Full media height (client v0.17.7: the old upper-55% cap left
                 the mid/lower screen dead — taps there did nothing). The
                 middle third stays free for hold-to-pause. data-tap keeps
                 the hold-to-pause pointer handlers OFF these zones (client
                 v0.17.15: «وقتی سمت چپ کلید می‌شود مدام پیغام مکث می‌دهد» —
                 pointerdown used to fire on the whole box incl. these
                 buttons); go() routes the commit race-free. --}}
            <button type="button" data-tap x-on:click="go('prev')" aria-label="استوری قبلی"
                    class="absolute inset-y-0 start-0 z-10 w-1/3"></button>
            <button type="button" data-tap x-on:click="go('next')" aria-label="استوری بعدی"
                    class="absolute inset-y-0 end-0 z-10 w-1/3"></button>

            @if ($current->caption)
                {{-- caption — clamped to 2 lines (client v0.17.7:
                     «نمایش عنوان استوری‌ها طولانی نباشه»).
                     w-fit + mx-auto instead of inline-block — inline-block
                     would override the clamp's -webkit-box display. --}}
                <div class="pointer-events-none absolute inset-x-4 top-[30%] z-[5] text-center">
                    <span class="line-clamp-2 mx-auto w-fit max-w-full rounded-2xl bg-black/45 px-4 py-2.5 text-sm font-extrabold leading-6 text-white backdrop-blur-sm">{{ $current->caption }}</span>
                </div>
            @endif

            <span x-show="paused && !stopped" x-cloak
                  class="pointer-events-none absolute left-1/2 top-1/2 z-20 -translate-x-1/2 -translate-y-1/2 rounded-full bg-black/50 px-4 py-2 text-[10px] font-bold text-white">مکث — برای ادامه رها کنید</span>
            <span x-show="stopped" x-cloak
                  class="pointer-events-none absolute left-1/2 top-1/2 z-20 -translate-x-1/2 -translate-y-1/2 rounded-full bg-black/55 px-4 py-2 text-[10px] font-bold text-white">متوقف شد — دکمهٔ ▶ برای ادامه</span>
        </div>

        {{-- ── bottom sheet: product + link + likes + comments ── --}}
        <div class="relative z-20 shrink-0 space-y-2.5 rounded-t-3xl bg-gradient-to-t from-black via-black/95 to-black/80 px-3 pb-[calc(14px_+_env(safe-area-inset-bottom,0px))] pt-3">

            {{-- attached product --}}
            @if ($current->product)
                @php $storyProduct = $current->product; @endphp
                <a href="{{ $storyProduct->publicUrl() }}" wire:navigate
                   class="flex items-center gap-3 rounded-2xl bg-white/10 p-2.5 backdrop-blur transition hover:bg-white/15">
                    <span class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-xl bg-white/10">
                        @if ($storyProduct->mainImage)
                            <img src="{{ $storyProduct->mainImage->url() }}" alt="{{ $storyProduct->name }}" class="size-full object-cover">
                        @else
                            <x-icon name="package" class="size-5 text-white/60" />
                        @endif
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="line-clamp-1 block text-[11px] font-extrabold text-white">{{ $storyProduct->name }}</span>
                        <span class="mt-0.5 block text-[10px] font-bold text-white/80">{{ \App\Support\Fmt::moneyShort($storyProduct->price) }} <span class="font-semibold text-white/60">تومان</span></span>
                    </span>
                    <span class="shrink-0 rounded-xl bg-white px-3 py-2 text-[10px] font-black text-black">مشاهده</span>
                </a>
            @endif

            {{-- external link — WITH its title (falls back to the generic label) --}}
            @if ($current->link_url)
                <a href="{{ $current->link_url }}" target="_blank" rel="noopener"
                   class="flex items-center justify-center gap-2 rounded-2xl bg-white/10 px-4 py-2.5 text-[11px] font-extrabold text-white backdrop-blur transition hover:bg-white/15">
                    <x-icon name="external-link" class="size-4 shrink-0" />
                    <span class="truncate">{{ $current->link_title !== '' && $current->link_title !== null ? $current->link_title : 'مشاهدهٔ لینک' }}</span>
                </a>
            @endif

            {{-- comments list (collapsible) --}}
            @if ($current->comments_enabled && $comments->isNotEmpty())
                <div class="scroll-slim max-h-40 space-y-2 overflow-y-auto pe-1" aria-label="نظرات استوری">
                    @foreach ($comments as $comment)
                        <div wire:key="cmt-{{ $comment->id }}" class="flex items-start gap-2">
                            <span class="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full bg-white/10 text-[9px] font-black text-white">{{ mb_substr($comment->customer?->full_name ?: 'م', 0, 1) }}</span>
                            <div class="min-w-0 rounded-2xl rounded-ss-md bg-white/10 px-3 py-1.5">
                                <span class="block text-[10px] font-extrabold text-white/90">{{ $comment->customer?->full_name ?: 'کاربر' }}</span>
                                <span class="mt-0.5 block break-words text-[11px] leading-5 text-white/85">{{ $comment->body }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- like + comment composer — per-story togglable --}}
            @if ($current->likes_enabled || $current->comments_enabled)
                <div class="flex items-center gap-2">
                    @if ($current->likes_enabled)
                        <button type="button" wire:click="toggleLike"
                                class="flex shrink-0 items-center gap-1.5 rounded-2xl bg-white/10 px-3 py-2.5 backdrop-blur transition hover:bg-white/15 active:scale-95"
                                aria-label="{{ $liked ? 'برگرداندن لایک' : 'لایک استوری' }}">
                            <x-icon name="heart" class="size-4.5 {{ $liked ? 'fill-rose-500 text-rose-500' : 'text-white' }}" />
                            <span class="text-[11px] font-extrabold {{ $liked ? 'text-rose-400' : 'text-white' }}">{{ \App\Support\Fmt::fa($current->likes_count) }}</span>
                        </button>
                    @endif

                    @if ($current->comments_enabled)
                        <div class="flex min-w-0 flex-1 items-center gap-1.5 rounded-2xl bg-white/10 px-3 py-1.5 backdrop-blur focus-within:bg-white/15">
                            <input type="text" wire:model="commentBody" maxlength="300"
                                   @if (! auth('customer')->check()) disabled placeholder="برای نظر دادن وارد شوید…" @else placeholder="نظر بدهید…" @endif
                                   x-on:keydown.enter="$wire.addComment()"
                                   class="w-full bg-transparent text-[11px] text-white outline-none placeholder:text-white/45 disabled:cursor-not-allowed"
                                   aria-label="متن نظر">
                            <button type="button" wire:click="addComment" wire:loading.attr="disabled" wire:target="addComment"
                                    @if (! auth('customer')->check()) disabled @endif
                                    class="grid size-8 shrink-0 place-items-center rounded-xl bg-white text-black transition active:scale-90 disabled:opacity-40"
                                    aria-label="ارسال نظر">
                                <x-icon name="arrow-left" class="size-4" />
                            </button>
                        </div>
                    @endif
                </div>
                @error('commentBody') <p class="text-[10px] font-bold text-rose-400">{{ $message }}</p> @enderror
            @endif
        </div>
    </div>
@endif
</div>
