<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Identifies a viewer well enough to deduplicate a day's views without storing
 * anything that points back at a person. A signed-in viewer is keyed on their
 * id; a guest on their session where one exists, and otherwise on address and
 * client — always hashed, never stored raw.
 */
final class ViewerFingerprint
{
    public static function for(Request $request): string
    {
        $user = $request->user();

        if ($user !== null) {
            return hash('sha256', 'user:'.$user->getAuthIdentifier());
        }

        if ($request->hasSession()) {
            return hash('sha256', 'session:'.$request->session()->getId());
        }

        return hash('sha256', 'guest:'.$request->ip().'|'.$request->userAgent());
    }
}
