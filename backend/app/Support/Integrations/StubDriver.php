<?php

declare(strict_types=1);

namespace App\Support\Integrations;

/**
 * Marks an implementation as a placeholder for an external service that is
 * not wired up yet. Stubs are refused at boot in production.
 */
interface StubDriver
{
    public function stubDescription(): string;
}
