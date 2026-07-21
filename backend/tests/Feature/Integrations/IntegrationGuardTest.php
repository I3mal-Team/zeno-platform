<?php

declare(strict_types=1);

use App\Support\Integrations\IntegrationGuard;
use App\Support\Integrations\StubDriver;
use App\Support\Otp\FixedOtpCodeGenerator;
use App\Support\Otp\OtpCodeGenerator;
use App\Support\Otp\RandomOtpCodeGenerator;
use App\Support\Sms\LogSmsGateway;
use App\Support\Sms\SmsGateway;

function resolveOtpGenerator(string $driver, ?string $fixedCode = null): OtpCodeGenerator
{
    config()->set('integrations.otp.generator', $driver);

    if ($fixedCode !== null) {
        config()->set('integrations.otp.fixed_code', $fixedCode);
    }

    app()->forgetInstance(OtpCodeGenerator::class);

    return app(OtpCodeGenerator::class);
}

it('blocks a stub driver in production', function () {
    IntegrationGuard::assertSafeForEnvironment('production', [
        'otp' => new FixedOtpCodeGenerator('4829'),
    ]);
})->throws(RuntimeException::class, 'cannot run in production');

it('names the offending integration so the failure is actionable', function () {
    expect(fn () => IntegrationGuard::assertSafeForEnvironment('production', [
        'sms' => new LogSmsGateway(logger()),
    ]))->toThrow(RuntimeException::class, 'sms');
});

it('allows stub drivers outside production', function () {
    foreach (['local', 'testing', 'staging'] as $environment) {
        IntegrationGuard::assertSafeForEnvironment($environment, [
            'otp' => new FixedOtpCodeGenerator('4829'),
        ]);
    }
})->throwsNoExceptions();

it('allows live drivers in production', function () {
    IntegrationGuard::assertSafeForEnvironment('production', [
        'otp' => new RandomOtpCodeGenerator,
    ]);
})->throwsNoExceptions();

it('resolves the configured otp generator', function () {
    expect(resolveOtpGenerator('fixed', '1234')->generate(4))->toBe('1234');
});

it('pads a shorter fixed code to the configured length', function () {
    expect(resolveOtpGenerator('fixed', '7')->generate(4))->toBe('0007');
});

it('generates a random code of the configured length', function () {
    expect(resolveOtpGenerator('random')->generate(4))
        ->toHaveLength(4)
        ->toMatch('/^\d{4}$/');
});

it('rejects an unknown otp generator', function () {
    resolveOtpGenerator('nope');
})->throws(InvalidArgumentException::class, 'Unknown OTP generator');

it('marks every stub with a description', function () {
    foreach ([new FixedOtpCodeGenerator('4829'), new LogSmsGateway(logger())] as $stub) {
        expect($stub)->toBeInstanceOf(StubDriver::class)
            ->and($stub->stubDescription())->not->toBeEmpty();
    }
});

it('binds the configured sms driver', function () {
    expect(app(SmsGateway::class))->toBeInstanceOf(LogSmsGateway::class);
});
