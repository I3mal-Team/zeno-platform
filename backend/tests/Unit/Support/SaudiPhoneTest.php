<?php

declare(strict_types=1);

use App\Support\Phone\SaudiPhone;

it('normalises every accepted shape to one value', function (string $input) {
    expect(SaudiPhone::normalise($input))->toBe('+966512345678');
})->with([
    '0512345678',
    '512345678',
    '+966512345678',
    '966512345678',
    '00966512345678',
    '05 1234 5678',
    '05-1234-5678',
    '+966 51 234 5678',
    '٠٥١٢٣٤٥٦٧٨',
    '+٩٦٦٥١٢٣٤٥٦٧٨',
]);

it('rejects anything that is not a Saudi mobile', function (?string $input) {
    expect(SaudiPhone::normalise((string) $input))->toBeNull();
})->with([
    '0412345678',
    '0112345678',
    '051234567',
    '05123456789',
    '',
    'abcd',
    '+201012345678',
]);

it('renders a stored number for display', function () {
    expect(SaudiPhone::toLocal('+966512345678'))->toBe('0512345678');
});

it('masks all but the last three digits', function () {
    expect(SaudiPhone::mask('+966512345678'))->toBe('+966******678');
});
