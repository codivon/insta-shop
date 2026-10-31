{{-- ─────────────────────────────────────────────────────────
  Thin wrapper: mounts Livewire full-page components inside
  the admin desktop layout. The page title flows through
  render()->title('...') as the $title layout variable.
──────────────────────────────────────────────────────────── --}}
@extends('layouts.admin')

@section('title', $title ?? 'پنل مدیریت')
@section('breadcrumb', $breadcrumb ?? 'مدیریت کل')

@section('content')
    {{ $slot }}
@endsection
