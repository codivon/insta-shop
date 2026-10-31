<?php

namespace App\Http\Middleware;

use App\Support\PanelContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Detects the current panel from the URI prefix and swaps session config
 * (cookie + table + lifetime) BEFORE StartSession runs — this is what makes
 * the three panels' logins fully isolated from each other.
 *
 * Must be PREPENDED to the global middleware stack (see bootstrap/app.php).
 */
class ResolvePanel
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->detect($request->path());
        $ctx = PanelContext::make($key);

        app()->instance(PanelContext::class, $ctx);

        // Swap session storage for this panel before StartSession runs.
        config([
            'session.driver'    => 'database',
            'session.cookie'    => $ctx->sessionCookie(),
            'session.table'     => $ctx->sessionTable(),
            'session.lifetime'  => $ctx->sessionLifetime(),
            'session.same_site' => 'lax',
        ]);

        return $next($request);
    }

    protected function detect(string $path): string
    {
        $first = explode('/', trim($path, '/'))[0] ?? '';

        foreach (config('panels.panels', []) as $key => $panel) {
            $prefix = $panel['prefix'] ?? null;

            if ($prefix !== null && $prefix !== '' && $first === $prefix) {
                return $key;
            }
        }

        return 'landing';
    }
}
