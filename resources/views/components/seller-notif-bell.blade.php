{{-- ═══════════════════════════════════════════════════════════
  Seller header NOTIFICATIONS BELL — links to the notifications
  page and carries the live unread badge. Include it in every
  seller page header:  <x-seller-notif-bell />
  (notifications moved OUT of the bottom tab bar).
════════════════════════════════════════════════════════════ --}}
@php
    $hasRoute = \Illuminate\Support\Facades\Route::has('seller.notifications');
@endphp
@if ($hasRoute)
    <a href="{{ route('seller.notifications') }}" wire:navigate
       class="relative grid size-10 shrink-0 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300"
       aria-label="اعلان‌ها">
        <x-icon name="bell" class="size-5" />
        @if (auth('seller')->check())
            <livewire:seller.notifications.unread-badge wire:key="seller-header-badge" />
        @endif
    </a>
@endif
