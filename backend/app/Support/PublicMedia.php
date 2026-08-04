<?php

declare(strict_types=1);

namespace App\Support;

final class PublicMedia
{
    /**
     * Rebase an absolute media URL onto the incoming request's host.
     *
     * Stored media URLs are built from APP_URL (e.g. the Herd domain
     * `zeno.test`), which a client on the LAN — a phone hitting the API by IP —
     * cannot resolve. Serving the path from whatever host the request arrived
     * on keeps images reachable for web and mobile alike.
     */
    public static function url(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            return $url;
        }

        $query = parse_url($url, PHP_URL_QUERY);

        return request()->getSchemeAndHttpHost().$path.(is_string($query) ? '?'.$query : '');
    }
}
