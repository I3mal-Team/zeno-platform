<?php

declare(strict_types=1);

// The DB facade is permitted for transactions, which belong to the use case and
// therefore to the service. Query builders are not.
arch('services do not query the database')
    ->expect('App\Services')
    ->not->toUse([
        'Illuminate\Database\Eloquent\Builder',
        'Illuminate\Database\Query\Builder',
        'Illuminate\Database\Connection',
    ]);

test('services use the DB facade only for transactions', function () {
    $offenders = [];

    foreach (glob(dirname(__DIR__, 2).'/app/Services/*.php') ?: [] as $file) {
        $source = (string) file_get_contents($file);

        preg_match_all('/DB::(\w+)/', $source, $matches);

        foreach ($matches[1] as $method) {
            if ($method !== 'transaction') {
                $offenders[] = basename($file).' calls DB::'.$method;
            }
        }
    }

    expect($offenders)->toBeEmpty(
        'Data access belongs in a repository: '.implode(', ', $offenders)
    );
});

// The load-bearing rule: with this in place, business logic cannot fork per
// surface, because a service can never know which surface called it.
arch('services are transport agnostic')
    ->expect('App\Services')
    ->not->toUse([
        'App\Http',
        'Illuminate\Http\Request',
        'Illuminate\Support\Facades\Request',
        'Illuminate\Http\JsonResponse',
    ]);

arch('controllers do not access the database')
    ->expect('App\Http\Controllers')
    ->not->toUse([
        'Illuminate\Support\Facades\DB',
        'Illuminate\Database\Eloquent\Builder',
    ]);

arch('controllers do not call repositories directly')
    ->expect('App\Http\Controllers')
    ->not->toUse('App\Repositories');

arch('repositories are transport agnostic')
    ->expect('App\Repositories')
    ->not->toUse(['App\Http', 'App\Services']);

arch('models stay clean')
    ->expect('App\Models')
    ->not->toUse(['App\Services', 'App\Repositories', 'App\Http']);

arch('api controllers do not render views')
    ->expect('App\Http\Controllers\Api')
    ->not->toUse(['Illuminate\View', 'Illuminate\Contracts\View\View']);

arch('site controllers do not use api resources')
    ->expect('App\Http\Controllers\Site')
    ->not->toUse('App\Http\Resources');

test('the domain layer is not split by surface', function () {
    $appPath = dirname(__DIR__, 2).'/app';
    $forbidden = [];

    foreach (['Services', 'Repositories', 'Data', 'Enums'] as $layer) {
        foreach (['Api', 'Web', 'Site', 'Dashboard'] as $surface) {
            if (is_dir("{$appPath}/{$layer}/{$surface}")) {
                $forbidden[] = "app/{$layer}/{$surface}";
            }
        }
    }

    expect($forbidden)->toBeEmpty(
        'The domain is shared and must not fork per surface: '.implode(', ', $forbidden)
    );
});

arch('no debugging statements are committed')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r', 'die'])
    ->not->toBeUsed();

arch('strict types are declared')
    ->expect('App')
    ->toUseStrictTypes();

arch('domain exceptions extend the shared base')
    ->expect('App\Exceptions\Domain')
    ->toExtend('App\Exceptions\Domain\DomainException')
    ->ignoring('App\Exceptions\Domain\DomainException');
