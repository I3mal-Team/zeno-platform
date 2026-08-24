<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * The verifier role is separated from the start because it is the only one that
 * sees identity and commercial-registration documents.
 */
final class AdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['super_admin', 'moderator', 'verifier'] as $role) {
            Role::findOrCreate($role, 'admin');
        }
    }
}
