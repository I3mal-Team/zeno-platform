<?php

declare(strict_types=1);

namespace App\Support\Integrations;

use RuntimeException;

final class IntegrationGuard
{
    /**
     * @param  array<string, object>  $resolved
     *
     * @throws RuntimeException
     */
    public static function assertSafeForEnvironment(string $environment, array $resolved): void
    {
        if ($environment !== 'production') {
            return;
        }

        $stubs = [];

        foreach ($resolved as $integration => $driver) {
            if ($driver instanceof StubDriver) {
                $stubs[] = "  · {$integration}: ".$driver::class." — {$driver->stubDescription()}";
            }
        }

        if ($stubs === []) {
            return;
        }

        throw new RuntimeException(
            "Stubbed integrations cannot run in production:\n".implode("\n", $stubs)
        );
    }
}
