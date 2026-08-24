<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * A first super-admin so the /admin panel is reachable out of the box. The
 * credentials come from the environment with dev defaults; set ADMIN_EMAIL and
 * ADMIN_PASSWORD before seeding anywhere that matters.
 */
final class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        Role::findOrCreate('super_admin', 'admin');

        $admin = Admin::query()->updateOrCreate(
            ['email' => config('admin.email')],
            [
                'name' => 'مدير النظام',
                'password' => config('admin.password'),
                'status' => 'active',
            ],
        );

        if (! $admin->hasRole('super_admin')) {
            $admin->assignRole('super_admin');
        }
    }
}
