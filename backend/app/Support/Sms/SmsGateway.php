<?php

declare(strict_types=1);

namespace App\Support\Sms;

interface SmsGateway
{
    /**
     * @param  string  $to  E.164 formatted number
     *
     * @throws SmsDeliveryException
     */
    public function send(string $to, string $message): void;
}
