<?php

namespace App\Http\Middleware;

use App\Support\PanelContext;
use Closure;
use Illuminate\Http\Request;
use InstaShop\Installer\Support\InstallerState;
use Symfony\Component\HttpFoundation\Response;

/**
 * Detects the current panel from the URI prefix and swaps session config
 * (cookie + table + lifetime) BEFORE StartSession runs — this is what makes
 * the three panels' logins fully isolated from each other.
 *
 * Livewire AJAX calls go to the prefix-less "/livewire/update" endpoint, so
 * for those the panel is resolved from an explicit header injected by a JS
 * hook (see resources/js/app.js), falling back to the Referer path. Without
 * this, Auth::login() inside Livewire would write to the wrong session.
 *
 * Must be PREPENDED to the global middleware stack (see bootstrap/app.php).
 */
class ResolvePanel
{
    public function handle(Request $request, Closure $next): Response
    {
        // قبل از تکمیل نصب، جدول‌های session وجود ندارند؛ درایور فایل (که
        // توسط InstallerServiceProvider ست شده) باید بماند و سواپ پنل انجام نشود.
        if (! InstallerState::isInstalled()) {
            return $next($request);
        }

        $key = $this->detect($request);
        $ctx = PanelContext::make($key);

        app()->instance(PanelContext::class, $ctx);

        // Swap session storage for this panel before StartSession runs.
        config([
            'session.driver' => 'database',
            'session.cookie' => $ctx->sessionCookie(),
            'session.table' => $ctx->sessionTable(),
            'session.lifetime' => $ctx->sessionLifetime(),
            'session.same_site' => 'lax',
        ]);

        return $next($request);
    }

    protected function detect(Request $request): string
    {
        $path = $request->path();

        // Livewire endpoints (livewire/update in v3, livewire-{hash}/update
        // in v4) are panel-less; inherit the panel context of the page the
        // component is mounted on (header → referer → landing).
        if (str_starts_with($path, 'livewire')) {
            return $this->panelFromHeader($request)
                ?? $this->panelFromReferer($request)
                ?? 'landing';
        }

        $first = explode('/', trim($path, '/'))[0] ?? '';

        foreach (config('panels.panels', []) as $key => $panel) {
            $prefix = $panel['prefix'] ?? null;

            if ($prefix !== null && $prefix !== '' && $first === $prefix) {
                return $key;
            }
        }

        return 'landing';
    }

    protected function panelFromHeader(Request $request): ?string
    {
        $panel = (string) $request->header('X-Instashop-Panel', '');

        return $this->validPanel($panel);
    }

    protected function panelFromReferer(Request $request): ?string
    {
        $referer = (string) $request->headers->get('referer', '');

        if ($referer === '') {
            return null;
        }

        $path = trim((string) parse_url($referer, PHP_URL_PATH), '/');

        return $this->validPanel(explode('/', $path)[0] ?? '');
    }

    protected function validPanel(string $key): ?string
    {
        return array_key_exists($key, config('panels.panels', [])) ? $key : null;
    }
}
