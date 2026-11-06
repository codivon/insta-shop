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
                // The default session cookie name is derived from APP_NAME,
                // which CHANGES the moment the wizard writes .env (Laravel →
                // InstaShop). A renamed cookie means a brand-new empty session
                // and the CSRF token dies mid-install → 419 on the very next
                // batch. Pin the cookie name until the app is installed.
                'session.cookie' => 'instashop_install_session',
                // config/session.php defaults 'secure' to (APP_ENV !== 'local')
                // which — with no .env yet — evaluates to TRUE. A Secure-flagged
                // cookie is REFUSED by browsers over plain HTTP, so on a host
                // where the wizard runs before SSL exists, the session (and the
                // XSRF cookie) would never stick → 419 mid-install. Cookies are
                // still safe over HTTPS; pre-install they must simply be
                // storable. writeEnv() picks the post-install value from the
                // scheme the wizard itself was used over.
                'session.secure' => false,
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
