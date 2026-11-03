{{-- ═══════════════════════════════════════════════════════════
  Seller layout — mobile-app shell (phone width on desktop),
  RTL, sticky header + bottom tab bar + safe-area support.
═════════════════════════════════════════════════════════════ --}}
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-panel="seller">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#D62976">
    <title>@yield('title', 'اپ فروشنده') | {{ \App\Support\Theme::resolve()['app_name'] }}</title>
    <link rel="stylesheet" href="/fonts/vazirmatn.css">
    @include('partials.brand-favicon')
    @include('partials.theme-vars')
    @include('partials.pwa-head', ['role' => 'seller'])
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="ip-fit-dvh flex flex-col bg-slate-200 text-slate-900 dark:bg-slate-950 dark:text-slate-100 transition-colors">

    {{-- App shell: exactly viewport height on every device → the header and
         tab bar stay pinned and ONLY <main> scrolls (also on real phones). --}}
    <div class="relative mx-auto flex h-dvh w-full max-w-[430px] flex-col overflow-hidden bg-slate-50 shadow-2xl dark:bg-slate-950 lg:my-6 lg:h-[calc(100vh-3rem)] lg:min-h-0 lg:rounded-[2.5rem] lg:border lg:border-slate-300 dark:lg:border-slate-800">

        {{-- ───────── App header (pinned top) ───────── --}}
        <header class="relative z-30 shrink-0 border-b border-slate-200/80 bg-white/90 pt-[env(safe-area-inset-top)] backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
            @yield('header')
        </header>

        {{-- ───────── Scrollable content (the only scroll container) ─────────
              Focused pages (focus=true, e.g. the ticket chat) own an INNER
              scroller instead — <main> becomes a flex column and the page
              pins its own bottom composer. ───────── --}}
        @php $focus = $focus ?? false; @endphp
        <main class="min-h-0 flex-1 overscroll-contain {{ $focus ? 'flex flex-col overflow-hidden' : 'scroll-slim overflow-y-auto' }} {{ (($hideTabbar ?? false) && ! $focus) ? 'pb-4' : '' }}">
            @yield('content')
        </main>

        {{-- ───────── Bottom tab bar (pinned bottom · hidden on focused full-screen pages) ───────── --}}
        @php
            // Notifications live in the HEADER bell (x-seller-notif-bell),
            // not the tab bar — keeps the bar at a clean 5 tabs.
            $sellerTabs = [
                ['route' => 'seller.home',        'icon' => 'home',   'label' => 'خانه'],
                ['route' => 'seller.shop',        'icon' => 'store',  'label' => 'فروشگاه من'],
                ['route' => 'seller.orders',      'icon' => 'bag',    'label' => 'سفارش‌ها'],
                ['route' => 'seller.wallet',      'icon' => 'wallet', 'label' => 'کیف پول'],
                ['route' => 'seller.profile',     'icon' => 'user',   'label' => 'پروفایل'],
            ];
        @endphp
        @unless ($hideTabbar ?? false)
        <nav class="shrink-0 border-t border-slate-200 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/95"
             aria-label="تب‌های اصلی فروشنده">
            <div class="grid grid-cols-5">
                @foreach ($sellerTabs as $tab)
                    @php
                        $active = request()->routeIs($tab['route'], $tab['route'].'.*');
                        // routes that don't exist yet fall back gracefully
                        $href = \Illuminate\Support\Facades\Route::has($tab['route']) ? route($tab['route']) : '#';
                    @endphp
                    <a href="{{ $href }}" @if ($href !== '#') wire:navigate @endif
                       aria-current="{{ $active ? 'page' : 'false' }}"
                       class="relative flex flex-col items-center gap-1 py-2.5 text-[9.5px] font-semibold transition
                              {{ $active ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }}">
                        <span class="grid h-8 w-14 place-items-center rounded-full transition {{ $active ? 'bg-primary-100 dark:bg-primary-950' : '' }}">
                            <x-icon name="{{ $tab['icon'] }}" class="size-5" />
                        </span>
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </div>
        </nav>
        @endunless
    </div>

    @include('partials.panel-toast')

    {{-- Web-Push registration helper for the seller notifications page —
         lives in the LAYOUT because Livewire components may only have one
         root element. Devices are stored scoped to THIS seller. --}}
    @once
        <script>
            (function () {
                function urlB64ToUint8Array(base64String) {
                    const padding = '='.repeat((4 - base64String.length % 4) % 4);
                    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                    const raw = atob(base64);
                    const output = new Uint8Array(raw.length);
                    for (let i = 0; i < output.length; ++i) output[i] = raw.charCodeAt(i);
                    return output;
                }

                window.__ipVapidKey = @js(\App\Support\WebPusher::publicKey());
                window.__ipPushRoute = @js(route('seller.notifications.subscribe'));

                function csrf() {
                    return document.querySelector('meta[name="csrf-token"]')?.content
                        || window.livewireScriptConfig?.csrf
                        || '';
                }

                /* Save the subscription on the server (idempotent upsert) and
                   flip the page to the green «فعال است» state. */
                async function upsertSubscription(sub) {
                    const json = sub.toJSON();

                    const res = await fetch(window.__ipPushRoute, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf(),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            endpoint: json.endpoint,
                            keys: json.keys,
                            device_label: navigator.userAgent.slice(0, 100),
                        }),
                    });

                    if (!res.ok) throw new Error('push upsert failed: ' + res.status);

                    window.dispatchEvent(new CustomEvent('ip-push-subscribed'));
                }

                window.__ipEnablePush = async function () {
                    try {
                        if (!('serviceWorker' in navigator) || !('PushManager' in window) || !window.Notification) {
                            return;
                        }

                        const perm = await Notification.requestPermission();
                        if (perm !== 'granted') {
                            if (perm === 'denied') {
                                window.dispatchEvent(new CustomEvent('ip-push-state', { detail: { state: 'denied' } }));
                            }
                            return;
                        }

                        const reg = await navigator.serviceWorker.register('/sw.js');
                        await navigator.serviceWorker.ready;

                        let sub = await reg.pushManager.getSubscription();
                        if (!sub) {
                            sub = await reg.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: urlB64ToUint8Array(window.__ipVapidKey || ''),
                            });
                        }

                        await upsertSubscription(sub);
                    } catch (e) {
                        console.warn('push subscribe failed', e);
                    }
                };

                /* Restore the push state on every page load / wire:navigate —
                   the notifications page is a fresh Livewire component each
                   time (always «default»), but the BROWSER remembers the
                   subscription, so ask it and sync the row back to the
                   server. Without this the green «نوتیفیکیشن دستگاه فعال
                   است» chip vanished on every refresh. */
                window.__ipSyncPush = async function () {
                    if (Date.now() - (window.__ipLastPushSync || 0) < 1500) return;
                    window.__ipLastPushSync = Date.now();

                    try {
                        if (!('serviceWorker' in navigator) || !('PushManager' in window) || !window.Notification) {
                            window.dispatchEvent(new CustomEvent('ip-push-state', { detail: { state: 'unsupported' } }));
                            return;
                        }

                        if (Notification.permission === 'denied') {
                            window.dispatchEvent(new CustomEvent('ip-push-state', { detail: { state: 'denied' } }));
                            return;
                        }

                        if (Notification.permission !== 'granted') return;

                        let reg = await navigator.serviceWorker.getRegistration();
                        if (!reg) reg = await navigator.serviceWorker.register('/sw.js');
                        if (!reg) return;

                        const sub = await reg.pushManager.getSubscription();
                        if (!sub) return; /* this device never subscribed — keep the enable button */

                        await upsertSubscription(sub);
                    } catch (e) {
                        /* restore is best-effort — never break the panel */
                    }
                };

                /* livewire:navigated also fires on the FIRST load, so one
                   hook covers both full reloads and SPA navigations. */
                if (!window.__ipPushSyncHooked) {
                    window.__ipPushSyncHooked = true;
                    document.addEventListener('livewire:navigated', () => window.__ipSyncPush && window.__ipSyncPush());
                }
                window.__ipSyncPush();
            })();
        </script>
    @endonce

    @include('partials.pwa-sheets', ['role' => 'seller'])

    @include('partials.build-check')

    @stack('scripts')
</body>
</html>
