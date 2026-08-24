<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | First super-admin
    |--------------------------------------------------------------------------
    |
    | Credentials the admin seeder uses to create (or update) the initial
    | super-admin so /admin is reachable out of the box. Override in the
    | environment for anything but local development.
    |
    */

    'email' => env('ADMIN_EMAIL', 'admin@zeno.sa'),

    'password' => env('ADMIN_PASSWORD', 'password'),

];
