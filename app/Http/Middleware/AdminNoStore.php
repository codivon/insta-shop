<?php

namespace App\Http\Middleware;

use App\Support\Version;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The admin panel is never stored by any cache layer.
 *
 * On cPanel/LiteSpeed hosts LSCache can store whole HTML pages — and a
 * cached page is served WITHOUT running PHP, so not even the self-healing
 * RefreshDeploymentCache middleware can see the request. That is exactly
 * how the panel kept showing pre-deploy views «even after the caches were
 * cleared». Admin pages are private and per-user: store nothing, ever.
 * (Public storefront pages keep their normal caching; the .htaccess mirror
 * of this policy covers the requests before they even reach PHP, and the
 * «X-InstaShop-Deploy» response header doubles as a curl-able deploy
 * diagnostic: fresh responses always carry the current release tag.)
 */
class AdminNoStore
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('X-InstaShop-Deploy', 'v'.Version::code());

        return $response;
    }
}
