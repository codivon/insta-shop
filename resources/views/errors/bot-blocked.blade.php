{{-- ═══════════════════════════════════════════════════════════
  Blocked bot/crawler page — served (403) by ApplySecuritySettings
  when `security.bot_blocking` is on and the visitor's User-Agent
  matches the deny list (bad SEO crawlers & scrapers).
═════════════════════════════════════════════════════════════ --}}
@extends('errors.partials.error-shell')

@section('title', 'ربات مسدود شد')

@section('accent', 'from-slate-800 via-slate-700 to-primary-700')

@section('code', '۴۰۳')

@section('icon')
    <x-icon name="shield" class="size-8" />
@endsection

@section('heading', 'دسترسی ربات‌ها مسدود است')

@section('message')
    <p>درخواست شما به‌عنوان خزنده یا ربات خودکار شناسایی شد و مسدود گردید.</p>
    <p class="mt-1">اگر انسان هستید و به‌اشتباه این صفحه را می‌بینید، جاوااسکریپت مرورگر را فعال کنید و دوباره تلاش کنید.</p>
@endsection

@push('scripts')
    <meta name="robots" content="noindex, nofollow">
@endpush
