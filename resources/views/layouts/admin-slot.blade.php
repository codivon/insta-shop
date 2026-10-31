{{-- thin wrapper: mounts Livewire full-page component inside the admin layout --}}
@extends('layouts.admin')

@section('title', 'تنظیمات — ظاهر و رنگ‌بندی')
@section('breadcrumb', 'مدیریت کل — تنظیمات')

@section('content')
    {{ $slot }}
@endsection
