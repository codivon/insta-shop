{{-- ═══════════════════════════════════════════════════════════
  Mobile-auth layout — shared by seller & customer login pages.
  Phone-width app frame (like the panel shells), centered card,
  animated gradient backdrop, no bottom tab bar.
═══════════════════════════════════════════════════════════ --}}
@php
    $panel = \App\Support\PanelContext::current();
    $panelKey = $panel->key(); // seller | customer
@endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-panel="{{ $panelKey }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#D62976">
    <title>@yield('title', 'ورود') | InstaShop</title>
    <link rel="stylesheet" href="/fonts/vazirmatn.css">
    @include('partials.brand-favicon')
    @include('partials.theme-vars')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-200 text-slate-900 dark:bg-slate-950 dark:text-slate-100 transition-colors">

    {{-- ───────── animated gradient backdrop ───────── --}}
    <div class="fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-24 right-[12%] size-[22rem] rounded-full bg-gradient-to-br from-fuchsia-400/50 to-pink-400/40 blur-3xl motion-safe:animate-[ip-float_7s_ease-in-out_infinite] dark:from-fuchsia-600/25 dark:to-pink-600/20"></div>
        <div class="absolute -bottom-28 left-[8%] size-[20rem] rounded-full bg-gradient-to-tr from-orange-300/45 to-amber-300/35 blur-3xl motion-safe:animate-[ip-float_9s_ease-in-out_infinite] dark:from-orange-600/20 dark:to-amber-600/15"></div>
        <div class="absolute left-1/2 top-1/3 size-[16rem] -translate-x-1/2 rounded-full bg-primary-300/25 blur-3xl dark:bg-primary-700/15"></div>
    </div>

    <div class="relative mx-auto flex min-h-screen w-full max-w-[430px] flex-col bg-slate-50 shadow-2xl dark:bg-slate-950 lg:my-6 lg:min-h-[calc(100vh-3rem)] lg:rounded-[2.5rem] lg:overflow-hidden lg:border lg:border-slate-300 dark:lg:border-slate-800">

        {{-- ───────── Top bar ───────── --}}
        <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 pt-[env(safe-area-inset-top)] backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
            <div class="flex items-center justify-between px-4 py-3">
                <a href="{{ route('landing') }}" class="group flex items-center gap-2.5" aria-label="بازگشت به صفحه اصلی">
                    <span class="grid size-10 place-items-center rounded-2xl bg-gradient-to-br from-fuchsia-500 via-primary-500 to-orange-400 text-white shadow-lg shadow-primary-600/30 transition group-hover:scale-105 group-active:scale-95">
                        <x-icon name="{{ $panelKey === 'seller' ? 'store' : 'bag' }}" class="size-5" />
                    </span>
                    <div>
                        <div class="text-sm font-extrabold leading-5">InstaShop</div>
                        <div class="text-[11px] text-slate-400">{{ $panel->label() }}</div>
                    </div>
                </a>
                <x-theme-toggle class="size-10 text-slate-500" />
            </div>
        </header>

        {{-- ───────── Content ───────── --}}
        <main class="scroll-slim flex flex-1 flex-col px-5 pb-[env(safe-area-inset-bottom)] pt-6">
            {{ $slot }}
        </main>

        {{-- ───────── Footer ───────── --}}
        <footer class="mt-auto px-5 pb-4 pt-2 text-center text-[11px] leading-5 text-slate-400">
            ورود و ثبت‌نام شما به معنای پذیرش
            <a href="#" class="font-semibold text-primary-600 dark:text-primary-400">قوانین InstaShop</a>
            است
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
