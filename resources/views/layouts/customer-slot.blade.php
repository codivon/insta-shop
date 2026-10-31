{{-- ─────────────────────────────────────────────────────────
  Thin wrapper: mounts Livewire full-page components inside
  the customer mobile app shell. The page title flows through
  render()->title('...') as the $title layout variable.
──────────────────────────────────────────────────────────── --}}
@extends('layouts.customer')

@section('title', $title ?? 'فروشگاه InstaShop')

@section('content')
    {{ $slot }}
@endsection
