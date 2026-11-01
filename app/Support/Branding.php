<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

/**
 * Branding service — user-uploaded logo & favicon (settings group
 * "branding") resolved to public URLs, with safe fallbacks.
 */
class Branding
{
    /** Public URL of the uploaded logo, or null when not set. */
    public static function logoUrl(): ?string
    {
        return self::url('branding.logo');
    }

    /** Public URL of the uploaded favicon, or null when not set. */
    public static function faviconUrl(): ?string
    {
        return self::url('branding.favicon');
    }

    /** Persist an uploaded file (Livewire TemporaryUploadedFile) under branding/. */
    public static function store(mixed $file, string $slot): string
    {
        // remove the previous file to avoid orphans
        self::delete($slot);

        return $file->store('branding', 'public');
    }

    /** Delete the stored file of a slot and clear its setting. */
    public static function delete(string $slot): void
    {
        $path = Setting::get("branding.{$slot}");

        if ($path) {
            Storage::disk('public')->delete($path);
            Setting::forget("branding.{$slot}");
        }
    }

    protected static function url(string $key): ?string
    {
        $path = Setting::get($key);

        if (! $path) {
            return null;
        }

        return Storage::disk('public')->exists($path)
            ? Storage::disk('public')->url($path)
            : null;
    }
}
