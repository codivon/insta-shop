@extends('errors.partials.error-shell')

@section('title', 'خطای سرور')

@section('accent', 'from-slate-800 via-slate-700 to-slate-600')

@section('code', '۵۰۰')

@section('icon')
    <x-icon name="alert" class="size-8" />
@endsection

@section('heading', 'مشکلی در سرور پیش آمد')

@section('message')
    <p>خطای غیرمنتظره‌ای رخ داد. نگران نباشید — تیم فنی مطلع شده است.</p>
    <p class="mt-1">چند لحظه بعد دوباره تلاش کنید.</p>
@endsection
