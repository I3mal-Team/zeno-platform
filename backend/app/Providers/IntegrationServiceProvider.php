<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\Integrations\IntegrationGuard;
use App\Support\Otp\FixedOtpCodeGenerator;
use App\Support\Otp\OtpCodeGenerator;
use App\Support\Sms\SmsGateway;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

final class IntegrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SmsGateway::class, function () {
            $driver = config('integrations.sms.driver');
            $class = config("integrations.sms.drivers.{$driver}")
                ?? throw new InvalidArgumentException("Unknown SMS driver [{$driver}]");

            return $this->app->make($class);
        });

        $this->app->singleton(OtpCodeGenerator::class, function () {
            $driver = config('integrations.otp.generator');
            $class = config("integrations.otp.generators.{$driver}")
                ?? throw new InvalidArgumentException("Unknown OTP generator [{$driver}]");

            return $class === FixedOtpCodeGenerator::class
                ? new FixedOtpCodeGenerator(config('integrations.otp.fixed_code'))
                : $this->app->make($class);
        });
    }

    public function boot(): void
    {
        IntegrationGuard::assertSafeForEnvironment(
            $this->app->environment(),
            [
                'sms' => $this->app->make(SmsGateway::class),
                'otp' => $this->app->make(OtpCodeGenerator::class),
            ],
        );
    }
}
