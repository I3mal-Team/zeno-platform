<?php

declare(strict_types=1);

namespace App\Enums;

enum OtpPurpose: string
{
    case Login = 'login';
    case PhoneChange = 'phone_change';
    case AccountDeletion = 'account_deletion';
}
