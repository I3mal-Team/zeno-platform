<?php

declare(strict_types=1);

use App\Support\Otp\ReviewAccounts;
use App\Support\Sms\SmsGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/** Records what would have been sent, so tests can assert no SMS was billed. */
function recordingSmsGateway(): object
{
    $gateway = new class implements SmsGateway
    {
        /** @var list<array{to: string, message: string}> */
        public array $sent = [];

        public function send(string $to, string $message): void
        {
            $this->sent[] = ['to' => $to, 'message' => $message];
        }
    };

    app()->instance(SmsGateway::class, $gateway);

    return $gateway;
}

function allowReviewNumber(string $phoneE164 = '+966512345678', string $code = '1234'): void
{
    app()->instance(ReviewAccounts::class, new ReviewAccounts([$phoneE164 => $code]));
}

it('signs a review number in with its fixed code and sends no SMS', function () {
    $sms = recordingSmsGateway();
    allowReviewNumber();

    test()->postJson('/api/v1/auth/otp/request', ['phone' => '0512345678'])
        ->assertOk();

    expect($sms->sent)->toBeEmpty();

    test()->postJson('/api/v1/auth/otp/verify', [
        'phone' => '0512345678',
        'code' => '1234',
        'role' => 'candidate',
        'device_name' => 'review',
    ])->assertOk()->assertJsonPath('success', true);
});

it('still rejects a wrong code on a review number', function () {
    recordingSmsGateway();
    allowReviewNumber();

    test()->postJson('/api/v1/auth/otp/request', ['phone' => '0512345678'])->assertOk();

    test()->postJson('/api/v1/auth/otp/verify', [
        'phone' => '0512345678',
        'code' => '9999',
        'role' => 'candidate',
        'device_name' => 'review',
    ])->assertStatus(422);
});

it('leaves every number outside the list untouched', function () {
    $sms = recordingSmsGateway();
    allowReviewNumber('+966500000000');

    test()->postJson('/api/v1/auth/otp/request', ['phone' => '0512345678'])->assertOk();

    // A normal number still gets a real message, and the review code must not
    // open it — otherwise the allowlist would be a global backdoor.
    expect($sms->sent)->toHaveCount(1);

    test()->postJson('/api/v1/auth/otp/verify', [
        'phone' => '0512345678',
        'code' => '1234',
        'role' => 'candidate',
        'device_name' => 'test',
    ])->assertStatus(422);
});

it('does not lock a reviewer out on the per-number window cap', function () {
    recordingSmsGateway();
    allowReviewNumber();

    // Far past the window cap a normal number would hit. The reviewer works
    // through the app over many sign-ins; being locked out mid-review reads as
    // a broken app and gets the submission rejected.
    for ($i = 0; $i < 12; $i++) {
        test()->travel(2)->minutes();

        test()->postJson('/api/v1/auth/otp/request', ['phone' => '0512345678'])
            ->assertOk();
    }
});

it('ships the store-review pairs so production needs no env edit', function () {
    // Hardcoded in config/integrations.php rather than left to the environment:
    // the production .env is not ours to edit, and sign-in is the only way into
    // the app, so an env-only switch means no reviewer bypass there at all.
    // Rotate these once the review is done — until then they are a credential
    // anyone with the repository can use.
    $accounts = app(ReviewAccounts::class);

    expect($accounts->codeFor('+966533333332'))->toBe('4829')
        ->and($accounts->codeFor('+966533333335'))->toBe('4829')
        // The allowlist is still an allowlist: nothing else is bypassed.
        ->and($accounts->has('+966512345678'))->toBeFalse();
});

it('parses the env pair format and drops malformed entries', function () {
    $accounts = ReviewAccounts::fromString('+966500000001:1234, +966500000002:5678 ,bogus,:99,+966500000003:');

    expect($accounts->codeFor('+966500000001'))->toBe('1234')
        ->and($accounts->codeFor('+966500000002'))->toBe('5678')
        ->and($accounts->has('bogus'))->toBeFalse()
        ->and($accounts->has('+966500000003'))->toBeFalse()
        ->and($accounts->codeFor('+966599999999'))->toBeNull();
});

it('treats an empty or missing setting as no review accounts', function (?string $raw) {
    expect(ReviewAccounts::fromString($raw)->has('+966512345678'))->toBeFalse();
})->with([null, '', '   ']);
