<?php

declare(strict_types=1);

return [
    // Absolute path to the Firebase service-account JSON. Push notifications
    // stay disabled (a silent no-op) until this file exists, so the app runs
    // fine before the credentials are in place.
    'credentials' => env('FCM_CREDENTIALS', storage_path('app/firebase/service-account.json')),
];
