@extends('errors.partials.error-shell')

@section('title', 'دسترسی غیرمجاز')

@section('accent', 'from-rose-600 via-rose-500 to-orange-500')

@section('code', '۴۰۳')

@section('icon')
    <x-icon name="lock" class="size-8" />
@endsection

@section('heading', 'دسترسی به این بخش مجاز نیست')

@section('message')
    <p>شما اجازهٔ دسترسی به این صفحه را ندارید؛ اگر فکر می‌کنید این یک اشتباه است، با پشتیبانی در تماس باشید.</p>
@endsection
