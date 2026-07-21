<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Data\Contact\ContactRequestData;
use Illuminate\Support\Facades\DB;

final class ContactRequestRepository
{
    public function create(ContactRequestData $data): void
    {
        DB::table('contact_requests')->insert([
            'name' => $data->name,
            'email' => $data->email,
            'topic' => $data->topic,
            'message' => $data->message,
            'ip_address' => $data->ipAddress,
            'status' => 'new',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
