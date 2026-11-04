{{-- ─────────────────────────────────────────────────────────
  Floating «بازگشت به پنل فروشندگی» pill — shown on EVERY public
  page (storefront + customer app) but ONLY while a seller is
  logged into the seller panel. Guests never see it.

  Hidden on the customer app's FOCUSED pages (ticket chat) where
  the page's own pinned reply composer occupies the bottom edge —
  the pill would cover the composer there.
──────────────────────────────────────────────────────────── --}}
@php
    $panelSeller = \App\Support\PanelAuth::sellerAcrossPanels();
    $fabCoversBar = request()->routeIs('shop.support.show');
@endphp
@if ($panelSeller && ! $fabCoversBar)
    <a href="{{ route('seller.home') }}" wire:navigate
       class="fixed bottom-[calc(86px_+_env(safe-area-inset-bottom))] left-4 z-40 flex items-center gap-1.5 rounded-full bg-primary-600 px-3.5 py-2.5 text-[11px] font-extrabold text-white shadow-xl shadow-primary-600/35 transition hover:bg-primary-700 active:scale-95 lg:bottom-6"
       aria-label="بازگشت به پنل فروشندگی" title="بازگشت به پنل فروشندگی">
        <x-icon name="store" class="size-4" />
        <span class="hidden sm:inline">بازگشت به پنل فروشندگی</span>
        <span class="sm:hidden">پنل فروشنده</span>
    </a>
@endif
