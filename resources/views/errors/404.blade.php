@extends('errors.partials.error-shell')

@section('title', 'صفحه پیدا نشد')

@section('code', '۴۰۴')

@section('icon')
    <x-icon name="search" class="size-8" />
@endsection

@section('heading', 'این صفحه پیدا نشد')

@section('message')
    <p>آدرسی که دنبال آن بودید وجود ندارد، حذف شده یا آدرس را اشتباه وارد کرده‌اید.</p>
    <p class="mt-1">از صفحهٔ اصلی شروع کنید یا از جستجوی فروشگاه استفاده کنید.</p>
@endsection
