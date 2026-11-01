{{-- User-uploaded favicon (admin → تنظیمات عمومی). Included in every layout's <head>. --}}
@if (\App\Support\Branding::faviconUrl())
    <link rel="icon" type="image/x-icon" href="{{ \App\Support\Branding::faviconUrl() }}">
@endif
