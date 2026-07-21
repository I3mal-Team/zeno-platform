<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Auth\RequestOtpData;
use App\Data\Auth\VerifyOtpData;
use App\Enums\UserStatus;
use App\Exceptions\Domain\AccountSuspendedException;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;

final class AuthService
{
    public function __construct(
        private readonly OtpService $otp,
        private readonly UserRepository $users,
    ) {}

    public function requestOtp(RequestOtpData $data, ?string $ip, ?string $userAgent): int
    {
        $existing = $this->users->findByPhone($data->phoneE164);

        if ($existing !== null && ! UserStatus::from($existing->status)->canSignIn()) {
            throw new AccountSuspendedException;
        }

        return $this->otp->issue($data->phoneE164, $data->purpose, $ip, $userAgent);
    }

    /**
     * @return array{user: User, token: string, isNewUser: bool}
     */
    public function verifyOtp(VerifyOtpData $data, string $deviceName): array
    {
        $this->otp->verify($data->phoneE164, $data->code, $data->purpose);

        return DB::transaction(function () use ($data, $deviceName) {
            $user = $this->users->findByPhone($data->phoneE164);
            $isNewUser = $user === null;

            if ($isNewUser) {
                $user = $this->users->create($data->phoneE164, $data->role);
            } elseif (! UserStatus::from($user->status)->canSignIn()) {
                throw new AccountSuspendedException;
            }

            $this->users->markPhoneVerified($user);
            $this->users->touchLastActive($user);

            return [
                'user' => $user,
                'token' => $user->createToken($deviceName)->plainTextToken,
                'isNewUser' => $isNewUser,
            ];
        });
    }

    public function logout(User $user, int $tokenId): void
    {
        $this->users->deleteToken($user, $tokenId);
    }

    public function logoutEverywhere(User $user): void
    {
        $this->users->deleteAllTokens($user);
    }
}
