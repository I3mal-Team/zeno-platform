<?php

declare(strict_types=1);

namespace App\Support\Fcm;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends push messages through the FCM HTTP v1 API using a service-account JSON.
 *
 * Deliberately dependency-free: the OAuth2 assertion is signed with openssl, so
 * nothing new is pulled into the app. Every path is a no-op until the
 * credentials file is present, so push can be prepared long before it is wired.
 */
final class FcmSender
{
    public function enabled(): bool
    {
        $path = config('fcm.credentials');

        return is_string($path) && is_file($path);
    }

    /**
     * @param  list<string>  $tokens
     * @param  array<string, mixed>  $data
     */
    public function send(array $tokens, string $title, string $body, array $data = []): void
    {
        if (! $this->enabled() || $tokens === []) {
            return;
        }

        $credentials = $this->credentials();
        if ($credentials === null) {
            return;
        }

        $accessToken = $this->accessToken($credentials);
        if ($accessToken === null) {
            return;
        }

        $projectId = (string) ($credentials['project_id'] ?? '');
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
        $stringData = array_map(static fn ($v): string => (string) $v, $data);

        foreach ($tokens as $token) {
            try {
                Http::withToken($accessToken)
                    ->timeout(5)
                    ->post($url, [
                        'message' => [
                            'token' => $token,
                            'notification' => ['title' => $title, 'body' => $body],
                            'data' => $stringData,
                        ],
                    ]);
            } catch (Throwable $e) {
                // One bad token must not stop the rest, nor bubble into the
                // request that triggered the notification.
                Log::warning('FCM send failed', ['error' => $e->getMessage()]);
            }
        }
    }

    /** @return array<string, mixed>|null */
    private function credentials(): ?array
    {
        $path = config('fcm.credentials');
        if (! is_string($path) || ! is_file($path)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : null;
    }

    /** @param  array<string, mixed>  $credentials */
    private function accessToken(array $credentials): ?string
    {
        $cached = Cache::get('fcm_access_token');
        if (is_string($cached)) {
            return $cached;
        }

        try {
            $jwt = $this->assertion($credentials);
            $response = Http::asForm()->timeout(5)->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);
            $token = $response->json('access_token');
            if (is_string($token)) {
                Cache::put('fcm_access_token', $token, now()->addMinutes(55));

                return $token;
            }
        } catch (Throwable $e) {
            Log::warning('FCM auth failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /** @param  array<string, mixed>  $credentials */
    private function assertion(array $credentials): string
    {
        $now = time();
        $header = $this->base64Url((string) json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $claims = $this->base64Url((string) json_encode([
            'iss' => $credentials['client_email'] ?? '',
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        $signingInput = $header.'.'.$claims;
        $signature = '';
        openssl_sign($signingInput, $signature, (string) ($credentials['private_key'] ?? ''), OPENSSL_ALGO_SHA256);

        return $signingInput.'.'.$this->base64Url($signature);
    }

    private function base64Url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
