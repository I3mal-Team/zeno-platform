<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Auth\RequestOtpData;
use App\Data\Auth\VerifyOtpData;
use App\Enums\UserStatus;
use App\Exceptions\Domain\AccountSuspendedException;
use App\Models\User;
use App\Repositories\ApplicationRepository;
use App\Repositories\CandidateProfileRepository;
use App\Repositories\DeviceTokenRepository;
use App\Repositories\JobAlertRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\SavedJobRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;

final class AuthService
{
    public function __construct(
        private readonly OtpService $otp,
        private readonly UserRepository $users,
        private readonly CandidateProfileRepository $profiles,
        private readonly DeviceTokenRepository $devices,
        private readonly ApplicationRepository $applications,
        private readonly NotificationRepository $notifications,
        private readonly SavedJobRepository $savedJobs,
        private readonly JobAlertRepository $alerts,
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
     * Verifies the code and resolves the user, creating one on first sign-in.
     * The token vs. session decision belongs to the surface, so this returns
     * neither — the API layers a Sanctum token on top, the web a session.
     *
     * @return array{user: User, isNewUser: bool}
     */
    public function authenticate(VerifyOtpData $data): array
    {
        $this->otp->verify($data->phoneE164, $data->code, $data->purpose);

        return DB::transaction(function () use ($data) {
            $user = $this->users->findByPhone($data->phoneE164);
            $isNewUser = $user === null;

            if ($isNewUser) {
                $user = $this->users->create($data->phoneE164, $data->role);
            } elseif (! UserStatus::from($user->status)->canSignIn()) {
                throw new AccountSuspendedException;
            }

            $this->users->markPhoneVerified($user);
            $this->users->touchLastActive($user);

            return ['user' => $user, 'isNewUser' => $isNewUser];
        });
    }

    /**
     * @return array{user: User, token: string, isNewUser: bool}
     */
    public function verifyOtp(VerifyOtpData $data, string $deviceName): array
    {
        ['user' => $user, 'isNewUser' => $isNewUser] = $this->authenticate($data);

        return [
            'user' => $user,
            'token' => $user->createToken($deviceName)->plainTextToken,
            'isNewUser' => $isNewUser,
        ];
    }

    public function logout(User $user, int $tokenId): void
    {
        $this->users->deleteToken($user, $tokenId);
    }

    public function logoutEverywhere(User $user): void
    {
        $this->users->deleteAllTokens($user);
    }

    /**
     * Account closure, required in-app by both stores (App Store guideline
     * 5.1.1(v) and Play's data-deletion policy).
     *
     * Everything the person authored goes for good. Deleting the applications
     * is what does most of the work: nothing in that chain soft deletes, so the
     * database cascades on to each conversation, its messages and read
     * receipts, and any WhatsApp handoff. Note the consequence — those
     * applications also disappear from the employer's applicant list, which is
     * the correct reading of a deletion request but is a visible change on the
     * other side of it.
     *
     * The user row itself is soft deleted rather than erased: listings and
     * verification requests reference it under restrict-on-delete constraints,
     * so an employer who ever posted cannot be removed at all. The partial
     * unique index on phone_e164 covers only live rows, so soft deleting
     * already frees the number and the same person can sign up again at once.
     */
    public function deleteAccount(User $user): void
    {
        DB::transaction(function () use ($user) {
            $this->applications->deleteForCandidate($user->getKey());
            $this->profiles->deleteForUser($user->getKey());
            $this->alerts->deleteAllForCandidate($user->getKey());
            $this->savedJobs->removeAll($user);
            $this->notifications->deleteAllForUser($user);
            $this->devices->forgetAllForUser($user->getKey());
            $this->users->deleteAllTokens($user);
            $this->users->softDeleteAccount($user);
        });
    }
}
