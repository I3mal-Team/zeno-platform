<?php

declare(strict_types=1);

use App\Support\Sms\SmsGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

/**
 * App Store and Play reviewers sign in from a device that cannot receive an
 * SMS, so one allow-listed number gets a constant code and no message. Getting
 * this wrong is the single most common rejection for a phone-OTP app, so the
 * behaviour is pinned here rather than left to configuration alone.
 */
const REVIEW_PHONE = '+966500000000';

const REVIEW_CODE = '1357';

beforeEach(function () {
    config()->set('integrations.otp.review_account', [
        'phone_e164' => REVIEW_PHONE,
        'code' => REVIEW_CODE,
    ]);

    $this->sms = Mockery::spy(SmsGateway::class);
    app()->instance(SmsGateway::class, $this->sms);
});

function askForCode(string $phone): TestResponse
{
    return test()->postJson('/api/v1/auth/otp/request', ['phone' => $phone]);
}

function signIn(string $phone, string $code): TestResponse
{
    return test()->postJson('/api/v1/auth/otp/verify', [
        'phone' => $phone,
        'code' => $code,
        'role' => 'candidate',
        'device_name' => 'review',
    ]);
}

it('signs the reviewer in with the constant code', function () {
    askForCode(REVIEW_PHONE)->assertOk();

    signIn(REVIEW_PHONE, REVIEW_CODE)
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['token']]);
});

it('never sends the reviewer an SMS', function () {
    askForCode(REVIEW_PHONE)->assertOk();

    $this->sms->shouldNotHaveReceived('send');
});

it('still texts every other number', function () {
    askForCode('+966512345678')->assertOk();

    $this->sms->shouldHaveReceived('send')->once();
});

it('leaves the constant code useless on any other number', function () {
    askForCode('+966512345678')->assertOk();

    signIn('+966512345678', REVIEW_CODE)
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'OTP_INVALID');
});

it('lets the reviewer ask again without waiting out the cooldown', function () {
    // A reviewer who taps resend and hits a 429 writes the app up as broken.
    askForCode(REVIEW_PHONE)->assertOk();
    askForCode(REVIEW_PHONE)->assertOk();
});

it('holds every other number to the cooldown', function () {
    askForCode('+966512345678')->assertOk();

    askForCode('+966512345678')
        ->assertStatus(429)
        ->assertJsonPath('error.code', 'OTP_RESEND_COOLDOWN');
});

it('does nothing when left unconfigured', function () {
    config()->set('integrations.otp.review_account', ['phone_e164' => null, 'code' => null]);

    askForCode(REVIEW_PHONE)->assertOk();

    $this->sms->shouldHaveReceived('send')->once();

    signIn(REVIEW_PHONE, REVIEW_CODE)
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'OTP_INVALID');
});
