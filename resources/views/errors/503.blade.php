@extends('errors.partials.error-shell')

@section('title', 'در دسترس نیست')

@section('accent', 'from-amber-500 via-orange-500 to-rose-500')

@section('code', '۵۰۳')

@section('icon')
    <x-icon name="clock" class="size-8" />
@endsection

@section('heading', 'سرور موقتاً در دسترس نیست')

@section('message')
    <p>سرور در حال بروزرسانی یا آماده‌سازی است؛ این وضعیت معمولاً چند دقیقه بیشتر طول نمی‌کشد.</p>
    <p class="mt-1">لطفاً بعداً دوباره تلاش کنید.</p>
@endsection
