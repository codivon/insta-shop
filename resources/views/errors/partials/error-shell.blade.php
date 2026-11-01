{{-- ═══════════════════════════════════════════════════════════
  Shared modern error-page shell.
  Usage: @extends with sections: code (digits), icon (x-icon name),
  title, message; optional vars: $accent (tailwind gradient classes),
  $actions (bool, default true).
═════════════════════════════════════════════════════════════ --}}
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'خطا') | {{ \App\Support\Theme::resolve()['app_name'] }}</title>
    <link rel="stylesheet" href="/fonts/vazirmatn.css">
    @include('partials.brand-favicon')
    @include('partials.theme-vars')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative grid min-h-dvh place-items-center overflow-hidden bg-slate-100 p-6 text-slate-900 transition-colors dark:bg-slate-950 dark:text-slate-100">

    {{-- ── decorative gradient blobs ── --}}
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="absolute -top-24 right-[10%] size-72 rounded-full bg-primary-400/20 blur-3xl dark:bg-primary-700/20"></div>
        <div class="absolute bottom-[-6rem] left-[8%] size-80 rounded-full bg-rose-400/15 blur-3xl dark:bg-rose-700/15"></div>
        <div class="absolute left-1/2 top-1/3 size-64 -translate-x-1/2 rounded-full bg-amber-300/10 blur-3xl dark:bg-amber-600/10"></div>
        <div class="absolute inset-0 opacity-[0.35] [background-image:radial-gradient(circle_at_1px_1px,rgb(148,163,184,0.35)_1px,transparent_0)] [background-size:26px_26px] dark:opacity-20"></div>
    </div>

    <main class="ip-fade-up relative w-full max-w-lg">
        <div class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/85 shadow-2xl shadow-slate-900/10 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/85">

            {{-- ── code + icon banner ── --}}
            <div class="relative overflow-hidden bg-gradient-to-bl {{ $accent ?? 'from-primary-600 via-fuchsia-600 to-rose-500' }} px-8 pb-12 pt-10 text-center">
                <div class="pointer-events-none absolute inset-0 opacity-25" aria-hidden="true">
                    <div class="absolute -top-10 left-8 size-32 rounded-full bg-white/30 blur-2xl"></div>
                    <div class="absolute -bottom-12 right-10 size-40 rounded-full bg-white/20 blur-3xl"></div>
                </div>

                <p class="relative select-none text-[92px] font-black leading-none tracking-tight text-white drop-shadow-lg sm:text-[120px]"
                   dir="ltr">@yield('code')</p>

                <span class="relative mx-auto mt-2 grid size-16 place-items-center rounded-2xl bg-white/15 text-white shadow-lg backdrop-blur">
                    @yield('icon')
                </span>
            </div>

            {{-- ── message card (overlapping the banner) ── --}}
            <div class="relative -mt-7 px-6 pb-7 sm:px-8">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 text-center shadow-xl dark:border-slate-800 dark:bg-slate-900">
                    <h1 class="text-lg font-extrabold leading-8">@yield('heading')</h1>
                    <div class="mt-2.5 text-xs leading-7 text-slate-500 dark:text-slate-400">
                        @yield('message')
                    </div>

                    @if (($actions ?? true))
                        <div class="mt-6 flex flex-wrap items-center justify-center gap-2.5">
                            <a href="/"
                               class="inline-flex items-center gap-2 rounded-2xl bg-primary-600 px-5 py-3 text-xs font-extrabold text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-700 active:scale-95">
                                <x-icon name="home" class="size-4" />
                                صفحهٔ اصلی
                            </a>
                            <button type="button" onclick="history.length > 1 ? history.back() : (window.location = '/')"
                                    class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-xs font-extrabold text-slate-600 transition hover:bg-slate-50 active:scale-95 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                                <x-icon name="arrow-right" class="size-4" />
                                بازگشت
                            </button>
                        </div>
                    @endif

                    @yield('actions')
                </div>
            </div>
        </div>

        <p class="mt-5 text-center text-[10px] font-bold text-slate-400">
            {{ \App\Support\Theme::resolve()['app_name'] }}
        </p>
    </main>
</body>
</html>
