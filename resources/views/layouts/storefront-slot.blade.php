{{-- ─────────────────────────────────────────────────────────
  Thin wrapper: mounts the public storefront Livewire page
  inside the storefront shell. Shop data (name/logo/handle)
  flows as layout variables from render()->layoutData([...]).
──────────────────────────────────────────────────────────── --}}
@extends('layouts.storefront')

@section('title', $title ?? 'فروشگاه')
