{{-- ═══════════════════════════════════════════════════════════
  PWA sheets — include ONCE per mobile panel layout (before </body>).

  ① Install bottom-sheet — appears when the browser fires
     beforeinstallprompt (Chrome/Android) or on iOS (manual
     «Add to Home Screen» guide). Includes the «turn your VPN
     ON to install» notice (the domain is filtered in Iran).
  ② VPN-active bottom-sheet — DEFINITIVE reachability probe: inside
     Iran the big foreign platforms (Google/YouTube/gstatic) are FILTERED,
     so a plain connection can never complete a request to them. Tiny
     no-cors probes are raced against those domains; if ANY answers, the
     traffic must be leaving the country through a tunnel and the user
     is asked to turn the VPN off for speed/stability. If they all fail
     while the site itself is obviously reachable (this page loaded!),
     the connection is a direct Iranian one — VPN off, no message.
     (The old avg-latency heuristic fired on ordinary slow mobile
     networks and wrongly announced an active VPN.)

  Both sheets: «دیگه نمایش نده» → localStorage (per role).
  Debug/E2E API: window.__ipwa
═══════════════════════════════════════════════════════════ --}}
@php
    $pwaRole = $role ?? (\App\Support\PanelContext::current()->key() === 'seller' ? 'seller' : 'customer');
    $pwaIsSeller = $pwaRole === 'seller';
    $pwaApp = \App\Support\Theme::resolve()['app_name'];
    $pwaIcon = "/pwa/icons/{$pwaRole}-192.png";
    $pwaInstallTitle = $pwaIsSeller ? 'اپ فروشنده را روی گوشی نصب کنید' : ($pwaApp.' را روی گوشی نصب کنید');
@endphp

{{-- ─────────── ① Install bottom-sheet ─────────── --}}
<div x-data="{ open: false, ios: false, dont: false }"
     x-on:ip-pwa-install.window="open = !!$event.detail; ios = false"
     x-on:ip-pwa-install-ios.window="open = !!$event.detail; ios = true"
     x-cloak x-show="open"
     class="fixed inset-0 z-[95]"
     role="dialog" aria-modal="true" aria-labelledby="ip-pwa-install-title">
    <div x-show="open" x-transition.opacity.duration.200ms
         class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
         x-on:click="open = false; if (dont) { window.__ipwa && window.__ipwa.dismissForever(); } window.dispatchEvent(new CustomEvent('ip-pwa-closed'))"></div>

    <div x-show="open"
         x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
         x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
         class="absolute inset-x-0 bottom-0 mx-auto flex max-w-[430px] flex-col rounded-t-3xl border border-b-0 border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">

        {{-- drag handle (also swipe-down to close) --}}
        <div class="mx-auto mt-2.5 h-1.5 w-12 rounded-full bg-slate-300 dark:bg-slate-700"
             x-on:touchstart="window.__ipwaSwipe = $event.touches[0].clientY"
             x-on:touchmove="if (window.__ipwaSwipe !== null && $event.touches[0].clientY - window.__ipwaSwipe > 70) { window.__ipwaSwipe = null; open = false; if (dont) { window.__ipwa && window.__ipwa.dismissForever(); } window.dispatchEvent(new CustomEvent('ip-pwa-closed')); }"></div>

        <div class="scroll-slim max-h-[86dvh] overflow-y-auto px-5 pb-[calc(1.25rem+env(safe-area-inset-bottom))] pt-4">
            <div class="flex items-start justify-between">
                <button type="button" x-on:click="open = false; if (dont) { window.__ipwa && window.__ipwa.dismissForever(); } window.dispatchEvent(new CustomEvent('ip-pwa-closed'))"
                        class="grid size-9 place-items-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
                        aria-label="بستن">
                    <x-icon name="x" class="size-5" />
                </button>
            </div>

            <div class="-mt-7 flex flex-col items-center text-center">
                <img src="{{ $pwaIcon }}" alt="آیکون {{ $pwaIsSeller ? 'اپ فروشنده' : $pwaApp }}" class="size-20 rounded-[1.4rem] border border-slate-200 bg-white object-cover shadow-xl shadow-slate-300/40 dark:border-slate-700 dark:bg-slate-800 dark:shadow-black/40" width="80" height="80">
                <h2 id="ip-pwa-install-title" class="mt-4 text-lg font-extrabold text-slate-900 dark:text-white" x-text="ios ? 'راهنمای نصب روی آیفون' : {{ json_encode($pwaInstallTitle) }}">
                    {{ $pwaInstallTitle }}
                </h2>
                <p class="mt-1.5 text-[13px] leading-6 text-slate-500 dark:text-slate-400" x-text="ios ? 'در سافاری سه قدم ساده تا داشتن اپ روی صفحهٔ اصلی گوشی:' : {{ json_encode('اپلیکیشن '.($pwaIsSeller ? 'فروشنده' : $pwaApp).' سریع‌تر از مرورگر باز می‌شود و همیشه در دسترس شماست.') }}">
                    اپلیکیشن {{ $pwaIsSeller ? 'فروشنده' : $pwaApp }} سریع‌تر از مرورگر باز می‌شود و همیشه در دسترس شماست.
                </p>
            </div>

            {{-- iOS manual guide (Safari has no install prompt) --}}
            <div x-show="ios">
                <ol class="mt-4 space-y-2.5 text-right">
                    @foreach ([
                        'روی نوار پایین، دکمهٔ اشتراک‌گذاری (مربع با فلش رو به بالا) را بزنید.',
                        'از فهرست، گزینهٔ Add to Home Screen را انتخاب کنید.',
                        'نام را تأیید و Add را بزنید — اپ به صفحهٔ اصلی اضافه می‌شود.',
                    ] as $i => $step)
                        <li class="flex items-start gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-3.5 dark:border-slate-800 dark:bg-slate-800/60">
                            <span class="grid size-7 shrink-0 place-items-center rounded-full bg-gradient-to-br from-fuchsia-600 via-pink-500 to-orange-500 text-xs font-extrabold text-white">{{ $i + 1 }}</span>
                            <span class="text-[13px] leading-6 text-slate-700 dark:text-slate-300">{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>
                <button type="button" x-on:click="open = false; if (dont) { window.__ipwa && window.__ipwa.dismissForever(); } window.dispatchEvent(new CustomEvent('ip-pwa-closed'))"
                        class="mt-4 w-full rounded-2xl bg-gradient-to-l from-fuchsia-600 via-pink-500 to-orange-500 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-pink-600/30 transition active:scale-[0.98]">
                    متوجه شدم
                </button>
            </div>

            {{-- Chrome/Android: native install prompt path --}}
            <div x-show="!ios">
                {{-- VPN notice — the domain is filtered inside Iran --}}
                <div class="mt-4 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-3.5 text-right dark:border-amber-500/30 dark:bg-amber-500/10">
                    <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">
                        <x-icon name="zap" class="size-4.5" />
                    </span>
                    <div>
                        <div class="text-[13px] font-extrabold text-amber-800 dark:text-amber-300">برای نصب، فیلترشکن را روشن کنید</div>
                        <div class="mt-0.5 text-xs leading-5 text-amber-700/90 dark:text-amber-400/90">این آدرس در ایران فیلتر شده است؛ تا زمانی که فیلترشکن روشن نباشد دکمهٔ نصب کار نمی‌کند.</div>
                    </div>
                </div>

                <div class="mt-3 space-y-2 text-right">
                    @foreach ([
                        ['icon' => 'rocket', 'title' => 'دسترسی سریع به پنل', 'sub' => $pwaIsSeller ? 'بدون باز کردن مرورگر و جست‌وجو' : 'بدون باز کردن مرورگر و جست‌وجو'],
                        ['icon' => 'wifi',   'title' => 'حتی وقتی اینترنت قطع است', 'sub' => 'صفحه‌های بازشدهٔ اخیر در دسترس می‌مانند'],
                        ['icon' => 'bell',   'title' => 'پیگیری لحظه‌ای سفارش‌ها', 'sub' => $pwaIsSeller ? 'اعلان سفارش جدید و وضعیت فروشگاه همیشه همراه شما' : 'اعلان‌ها و وضعیت سفارش همیشه همراه شما'],
                    ] as $b)
                        <div class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-3.5 dark:border-slate-800 dark:bg-slate-800/60">
                            <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400">
                                <x-icon name="{{ $b['icon'] }}" class="size-4.5" />
                            </span>
                            <div>
                                <div class="text-[13px] font-extrabold text-slate-800 dark:text-slate-200">{{ $b['title'] }}</div>
                                <div class="mt-0.5 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $b['sub'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" x-on:click="if (dont) { window.__ipwa && window.__ipwa.dismissForever(); } window.__ipwa && window.__ipwa.installNow()"
                        class="mt-4 flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-l from-fuchsia-600 via-pink-500 to-orange-500 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-pink-600/30 transition active:scale-[0.98]">
                    <x-icon name="download" class="size-4.5" />
                    نصب اپلیکیشن
                </button>
            </div>

            <label class="mt-3 flex cursor-pointer items-center justify-center gap-2 text-xs text-slate-400 select-none">
                <input type="checkbox" x-model="dont" class="size-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-800">
                دیگه نمایش نده
            </label>
        </div>
    </div>
</div>

{{-- ─────────── ② VPN-active bottom-sheet ─────────── --}}
<div x-data="{ open: false, dont: false }"
     x-on:ip-pwa-vpn.window="open = !!$event.detail"
     x-cloak x-show="open"
     class="fixed inset-0 z-[95]"
     role="dialog" aria-modal="true" aria-labelledby="ip-vpn-title">
    <div x-show="open" x-transition.opacity.duration.200ms
         class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
         x-on:click="open = false; if (dont) { window.__ipwa && window.__ipwa.dismissVpn(); }"></div>

    <div x-show="open"
         x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
         x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
         class="absolute inset-x-0 bottom-0 mx-auto flex max-w-[430px] flex-col rounded-t-3xl border border-b-0 border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">

        <div class="mx-auto mt-2.5 h-1.5 w-12 rounded-full bg-slate-300 dark:bg-slate-700"
             x-on:touchstart="window.__ipwaSwipe = $event.touches[0].clientY"
             x-on:touchmove="if (window.__ipwaSwipe !== null && $event.touches[0].clientY - window.__ipwaSwipe > 70) { window.__ipwaSwipe = null; open = false; if (dont) { window.__ipwa && window.__ipwa.dismissVpn(); } }"></div>

        <div class="scroll-slim max-h-[86dvh] overflow-y-auto px-5 pb-[calc(1.25rem+env(safe-area-inset-bottom))] pt-4">
            <div class="flex items-start justify-between">
                <button type="button" x-on:click="open = false; if (dont) { window.__ipwa && window.__ipwa.dismissVpn(); }"
                        class="grid size-9 place-items-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
                        aria-label="بستن">
                    <x-icon name="x" class="size-5" />
                </button>
            </div>

            <div class="-mt-7 flex flex-col items-center text-center">
                <span class="grid size-20 place-items-center rounded-[1.4rem] bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-xl shadow-primary-600/30">
                    <x-icon name="shield-off" class="size-9" />
                </span>
                <h2 id="ip-vpn-title" class="mt-4 text-lg font-extrabold text-slate-900 dark:text-white">فیلترشکن شما فعال است</h2>
                <p class="mt-1.5 text-[13px] leading-6 text-slate-500 dark:text-slate-400">برای بهره‌بری سریع‌تر، اتصال پایدار و سرعت بهتر، لطفاً فیلترشکن خود را خاموش کنید.</p>
                <span class="mt-3 inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-1.5 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                    <span class="size-2 rounded-full bg-emerald-500"></span>
                    اتصال فعال از طریق: فیلترشکن
                </span>
            </div>

            <div class="mt-4 space-y-2 text-right">
                @foreach ([
                    ['icon' => 'zap',    'title' => 'سرعت بارگذاری بالاتر',   'sub' => 'ترافیک مستقیم به سرور داخلی، بدون دورزدن از سرور خارجی'],
                    ['icon' => 'check',  'title' => 'اتصال پایدارتر در پنل‌ها', 'sub' => 'قطع‌وصل کمتر هنگام کار، گفتگو و آپلود مدارک'],
                    ['icon' => 'cpu',    'title' => 'مصرف بهینهٔ اینترنت',     'sub' => 'ترافیک داخلی معمولاً نیم‌بها محاسبه می‌شود'],
                ] as $b)
                    <div class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-3.5 dark:border-slate-800 dark:bg-slate-800/60">
                        <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400">
                            <x-icon name="{{ $b['icon'] }}" class="size-4.5" />
                        </span>
                        <div>
                            <div class="text-[13px] font-extrabold text-slate-800 dark:text-slate-200">{{ $b['title'] }}</div>
                            <div class="mt-0.5 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $b['sub'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" x-on:click="open = false; if (dont) { window.__ipwa && window.__ipwa.dismissVpn(); }"
                    class="mt-4 w-full rounded-2xl bg-gradient-to-l from-fuchsia-600 via-pink-500 to-orange-500 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-pink-600/30 transition active:scale-[0.98]">
                متوجه شدم، خاموش می‌کنم
            </button>

            <label class="mt-3 flex cursor-pointer items-center justify-center gap-2 text-xs text-slate-400 select-none">
                <input type="checkbox" x-model="dont" class="size-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-800">
                دیگه نمایش نده
            </label>

            <p class="mt-2 text-center text-[11px] leading-5 text-slate-400 dark:text-slate-500">پس از خاموش‌کردن فیلترشکن، صفحه را یک بار رفرش کنید تا اتصال جدید برقرار شود.</p>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var ROLE = '{{ $pwaRole }}';
    var DAY = 864e5;

    function ls(key, val) {
        try {
            if (arguments.length === 2) { localStorage.setItem(key, val); return; }
            return localStorage.getItem(key);
        } catch (e) { return null; }
    }

    function fire(name, detail) { window.dispatchEvent(new CustomEvent(name, { detail: detail })); }

    function standalone() {
        return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    }

    var isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent)
        || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

    var deferredPrompt = null;
    var installShownThisLoad = false;
    var installOpen = false;
    var vpnProbeDone = false;
    var vpnState = 'unknown'; // unknown | probing | on | off

    // ── eligibility helpers ─────────────────────────────────
    function dismissedForever() { return ls('ip.pwa.dismissed.' + ROLE) === '1'; }
    function snoozed() {
        var until = Number(ls('ip.pwa.snooze.' + ROLE) || 0);
        return until && Date.now() < until;
    }
    function installEligible() {
        return deferredPrompt !== null && !standalone() && !dismissedForever() && !snoozed() && !installShownThisLoad;
    }

    function maybeQueueVpn() {
        // the VPN check waits for its turn while the install sheet is open —
        // closing that sheet (ip-pwa-closed) re-schedules the probe
        if (!installOpen) runVpnProbe();
    }

    function runVpnProbe() {
        if (vpnProbeDone) return;
        vpnProbeDone = true;

        probeVpn().then(function (on) {
            if (on && ls('ip.vpn.dismissed') !== '1') {
                fire('ip-pwa-vpn', true);
            }
        });
    }

    // ── DEFINITIVE VPN detection ─────────────────────────────────
    // Domains that are filtered inside Iran WITHOUT a VPN. A request that
    // COMPLETES against any of them proves the traffic is tunnelled
    // (فیلترشکن روشن است). All failing while this very page obviously
    // loaded ⇒ direct Iranian connection ⇒ VPN off ⇒ stay silent.
    // No latency guessing: slow mobile networks used to trip the old
    // RTT threshold and falsely report an active VPN.
    var VPN_PROBES = [
        'https://www.google.com/generate_204',
        'https://www.youtube.com/generate_204',
        'https://www.gstatic.com/generate_204',
    ];

    function probeVpn() {
        if (vpnState === 'on' || vpnState === 'off') {
            return Promise.resolve(vpnState === 'on');
        }
        vpnState = 'probing';

        return new Promise(function (resolve) {
            var settled = false;
            var finished = 0;

            function finish(on) {
                if (settled) return;
                settled = true;
                vpnState = on ? 'on' : 'off';
                resolve(on);
            }

            VPN_PROBES.forEach(function (url) {
                var ctrl = typeof AbortController === 'function' ? new AbortController() : null;
                var timer = setTimeout(function () { if (ctrl) ctrl.abort(); }, 7000);

                fetch(url, { mode: 'no-cors', cache: 'no-store', credentials: 'omit', signal: ctrl ? ctrl.signal : undefined })
                    .then(function () { clearTimeout(timer); finish(true); })  // any response = domain reachable = tunnel
                    .catch(function () { clearTimeout(timer); })               // blocked / RST / timeout = this probe failed
                    .then(function () {
                        if (++finished === VPN_PROBES.length) finish(false);   // every probe failed → VPN off
                    });
            });
        });
    }

    // ── public API (also the E2E/debug surface) ─────────────────
    window.__ipwa = {
        role: ROLE,
        isIOS: isIOS,
        standalone: standalone,
        showInstall: function () { fire('ip-pwa-install', true); installShownThisLoad = true; installOpen = true; },
        hideInstall: function () { fire('ip-pwa-install', false); installOpen = false; },
        showVpn: function () { fire('ip-pwa-vpn', true); },
        hideVpn: function () { fire('ip-pwa-vpn', false); },
        installNow: function () {
            if (!deferredPrompt) {
                // no browser prompt available (iOS / already installed) —
                // fall back to the manual guide
                fire('ip-pwa-install-ios', true);
                installShownThisLoad = true;
                installOpen = true;
                return;
            }
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function (choice) {
                if (choice && choice.outcome === 'accepted') {
                    ls('ip.pwa.installed.' + ROLE, '1');
                }
                deferredPrompt = null;
                fire('ip-pwa-install', false);
            });
        },
        dismissForever: function () { ls('ip.pwa.dismissed.' + ROLE, '1'); },
        dismissVpn: function () { ls('ip.vpn.dismissed', '1'); },
        probeVpn: probeVpn,
        state: function () {
            return {
                role: ROLE,
                deferred: !!deferredPrompt,
                standalone: standalone(),
                isIOS: isIOS,
                dismissed: dismissedForever(),
                snoozed: snoozed(),
                vpnDismissed: ls('ip.vpn.dismissed') === '1',
                vpn: vpnState,
            };
        },
    };

    // closing the sheet by X / backdrop = «maybe later» (3-day snooze)
    window.addEventListener('ip-pwa-closed', function () {
        if (!dismissedForever()) ls('ip.pwa.snooze.' + ROLE, String(Date.now() + 3 * DAY));
        installOpen = false;
        setTimeout(runVpnProbe, 1500); // VPN notice gets its turn afterwards
    });

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;

        if (installEligible()) {
            installShownThisLoad = true;
            installOpen = true;
            setTimeout(function () { fire('ip-pwa-install', true); }, 2500);
        }
    });

    window.addEventListener('appinstalled', function () {
        ls('ip.pwa.installed.' + ROLE, '1');
        installOpen = false;
        fire('ip-pwa-install', false);
        fire('ip-pwa-vpn', false);
    });

    // ── iOS: no beforeinstallprompt → manual guide, at most once a week ──
    if (isIOS && !standalone() && !dismissedForever() && !snoozed()) {
        var lastHint = Number(ls('ip.pwa.ios.hint.' + ROLE) || 0);
        var ran = false;
        setTimeout(function () {
            if (ran || deferredPrompt || installShownThisLoad) return;
            ran = true;
            if (Date.now() - lastHint > 7 * DAY) {
                ls('ip.pwa.ios.hint.' + ROLE, String(Date.now()));
                fire('ip-pwa-install-ios', true);
            }
        }, 5000);
    }

    // ── VPN reachability probe: a few seconds after load (only while the
    //    tab is visible — background tabs throttle timers and would make
    //    the probes look blocked when they are not) ──────────────────
    setTimeout(function () {
        if (document.visibilityState === 'visible') { maybeQueueVpn(); return; }
        var onVisible = function () {
            if (document.visibilityState !== 'visible') return;
            document.removeEventListener('visibilitychange', onVisible);
            maybeQueueVpn();
        };
        document.addEventListener('visibilitychange', onVisible);
    }, 4500);

    // ── service worker (needed for the install prompt) ──────────
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js').catch(function () {});
        });
    }
})();
</script>
