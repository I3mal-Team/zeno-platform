<?php

declare(strict_types=1);

namespace App\Notifications\Concerns;

use App\Notifications\Channels\FcmChannel;
use App\Support\Fcm\FcmSender;

/**
 * Adds the FCM push channel alongside the database record — but only once the
 * service-account credentials exist, so notifications behave exactly as before
 * until push is configured.
 */
trait Pushable
{
    /** @return list<string> */
    protected function channelsWithPush(): array
    {
        $channels = ['database'];

        if (app(FcmSender::class)->enabled()) {
            $channels[] = FcmChannel::class;
        }

        return $channels;
    }
}
