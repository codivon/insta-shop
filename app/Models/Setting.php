<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type'];

    public $timestamps = true;

    /* ------------------------------------------------------------------
     |  Cached access API used across the whole app:
     |    Setting::get('theme.primary', '#2563eb')
     |    Setting::set('theme.primary', '#059669')
     *----------------------------------------------------------------- */

    protected const CACHE_KEY = 'instashop.settings';

    public static function get(string $groupKey, mixed $default = null): mixed
    {
        $all = self::allSettings();

        return array_key_exists($groupKey, $all) ? $all[$groupKey] : $default;
    }

    public static function set(string $groupKey, mixed $value, string $type = 'text'): void
    {
        [$group, $key] = self::split($groupKey);

        static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => self::serialize($value, $type), 'type' => $type],
        );

        Cache::forget(self::CACHE_KEY);
    }

    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $groupKey => $pair) {
            $value = is_array($pair) ? $pair['value'] : $pair;
            $type  = is_array($pair) ? ($pair['type'] ?? 'text') : 'text';
            self::set($groupKey, $value, $type);
        }
    }

    public static function forget(string $groupKey): void
    {
        [$group, $key] = self::split($groupKey);
        static::where('group', $group)->where('key', $key)->delete();
        Cache::forget(self::CACHE_KEY);
    }

    /** All settings as flat "group.key" => decoded value */
    public static function allSettings(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $out = [];

            foreach (static::query()->get() as $row) {
                $out["{$row->group}.{$row->key}"] = self::deserialize($row->value, $row->type);
            }

            return $out;
        });
    }

    public static function group(string $group): array
    {
        $prefix = "{$group}.";

        return array_filter(self::allSettings(), fn ($k) => str_starts_with($k, $prefix), ARRAY_FILTER_USE_KEY);
    }

    /* ------------------------- helpers ------------------------- */

    protected static function split(string $groupKey): array
    {
        $parts = explode('.', $groupKey, 2);

        if (count($parts) !== 2) {
            throw new \InvalidArgumentException("Setting key must be 'group.key', got [{$groupKey}]");
        }

        return $parts;
    }

    protected static function serialize(mixed $value, string $type): ?string
    {
        if (is_null($value)) {
            return null;
        }

        return $type === 'json' ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value;
    }

    protected static function deserialize(?string $raw, string $type): mixed
    {
        return match ($type) {
            'boolean' => $raw === null ? null : filter_var($raw, FILTER_VALIDATE_BOOLEAN),
            'number'  => $raw === null ? null : (is_numeric($raw) ? $raw + 0 : $raw),
            'json'    => $raw === null ? null : json_decode($raw, true),
            default   => $raw,
        };
    }
}
