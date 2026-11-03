{{-- ═══════════════════════════════════════════════════════════
  PWA head — per-panel manifest + iOS home-screen icon.
  Include in every mobile panel layout's <head> (seller /
  customer / mobile-auth). The panel key decides WHICH of the
  two installable apps this page represents.
═══════════════════════════════════════════════════════════ --}}
@php
    $pwaRole = $role ?? (\App\Support\PanelContext::current()->key() === 'seller' ? 'seller' : 'customer');
    $pwaAppName = \App\Support\Theme::resolve()['app_name'];
@endphp
<link rel="manifest" href="{{ route("pwa.manifest.{$pwaRole}") }}">
<link rel="apple-touch-icon" sizes="180x180" href="/pwa/icons/{{ $pwaRole }}-180.png">
@if ($pwaRole === 'seller')
    <meta name="apple-mobile-web-app-title" content="اپ فروشنده">
@else
    <meta name="apple-mobile-web-app-title" content="{{ \Illuminate\Support\Str::limit($pwaAppName, 12, '') }}">
@endif
