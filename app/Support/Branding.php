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
        // SECURITY (v0.20.0): inline guard — the sole caller validates its
        // inputs, but Branding::store() itself used to write whatever it was
        // handed onto the PUBLIC disk. Content-guessed extension must be an
        // image format AND the raw bytes must carry no PHP-executable
        // markers (polyglot PNG+PHP defense), no matter who calls this.
        $ext = strtolower((string) (is_object($file) && method_exists($file, 'guessExtension') ? $file->guessExtension() : ''));

        abort_unless(in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'ico'], true), 422, 'فایل برندینگ باید تصویر معتبر (png/jpg/webp/ico) باشد.');
        abort_unless(ImagePayload::clean($file), 422, 'فایل تصویر برندینگ معتبر نیست.');

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
