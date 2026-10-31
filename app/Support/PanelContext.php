<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Runtime context of the current panel (admin|seller|customer|landing).
 * Resolved per-request by App\Http\Middleware\ResolvePanel.
 */
class PanelContext
{
    protected string $key;

    protected array $config;

    public static function make(string $key): static
    {
        $ctx = new static;
        $ctx->key = $key;
        $ctx->config = config("panels.panels.{$key}", config('panels.panels.landing'));

        return $ctx;
    }

    public static function current(): static
    {
        return app(static::class);
    }

    public function key(): string
    {
        return $this->key;
    }

    public function is(string $key): bool
    {
        return $this->key === $key;
    }

    public function label(): string
    {
        return $this->config['label'] ?? $this->key;
    }

    public function guard(): ?string
    {
        return $this->config['guard'] ?? null;
    }

    public function prefix(): ?string
    {
        return $this->config['prefix'] ?? null;
    }

    public function sessionCookie(): string
    {
        return $this->config['session']['cookie'] ?? 'instashop_landing_session';
    }

    public function sessionTable(): string
    {
        return $this->config['session']['table'] ?? 'sessions';
    }

    public function sessionLifetime(): int
    {
        return (int) ($this->config['session']['lifetime'] ?? 120);
    }

    /** e.g. "admin" → "App\Http\Controllers\Admin" */
    public function controllerNamespace(): string
    {
        return 'App\\Http\\Controllers\\'.Str::studly($this->key);
    }

    /** Named route of the panel's login page (shop panel lives under /shop). */
    public function loginRoute(): string
    {
        return match ($this->key) {
            'admin' => 'admin.login',
            'seller' => 'seller.login',
            'customer' => 'shop.login',
            default => 'landing',
        };
    }

    /** Named route the panel lands on after authentication. */
    public function homeRoute(): string
    {
        return match ($this->key) {
            'admin' => 'admin.dashboard',
            'seller' => 'seller.home',
            'customer' => 'shop.home',
            default => 'landing',
        };
    }
}
