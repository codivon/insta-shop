<?php

namespace InstaShop\Installer;

use Illuminate\Support\ServiceProvider;
use InstaShop\Installer\Support\InstallerState;

class InstallerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Until the app is installed there is no database (session/cache
        // tables don't exist yet) and no .env (no APP_KEY). Provision file
        // drivers plus a STABLE provisional key so the wizard boots cleanly
        // on a bare host — the same key is later written into .env, so the
        // session never breaks mid-install.
        if (! InstallerState::isInstalled()) {
            config([
                'session.driver' => 'file',
                'cache.default' => 'file',
                'queue.default' => 'sync',
                'app.key' => InstallerState::provisionalKey(),
            ]);
        }
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/installer.php', 'installer');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'installer');

        if (! $this->app->routesAreCached()) {
            require __DIR__.'/../routes/web.php';
        }
    }
}
