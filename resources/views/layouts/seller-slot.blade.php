{{-- ─────────────────────────────────────────────────────────
  Thin wrapper: mounts Livewire full-page components inside
  the seller mobile app shell. The page title flows through
  render()->title('...') as the $title layout variable.
──────────────────────────────────────────────────────────── --}}
@extends('layouts.seller')

@section('title', $title ?? 'اپ فروشنده')

@section('content')
    {{ $slot }}
@endsection
