<?php

declare(strict_types=1);

putenv('APP_ENV=local');

$app = \Illuminate\Foundation\Application::configure(basePath: dirname(__DIR__))
    ->withExceptions()
    ->withRouting(using: static function (): void {
        \Illuminate\Support\Facades\Route::get('/shortcuts-test', static fn () => 'OK');
    })
    ->withProviders([
        \Laravel\Tinker\TinkerServiceProvider::class,
        \Laravel\Pail\PailServiceProvider::class,
        \Laravel\Sail\SailServiceProvider::class,
        \NunoMaduro\Collision\Adapters\Laravel\CollisionServiceProvider::class,
    ])
    ->withCommands([\App\Console\Commands\ProbeCommand::class])
    ->create();

$app->afterBootstrapping(
    \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
    static function (\Illuminate\Foundation\Application $app): void {
        $app['config']->set('app.key', 'base64:' . base64_encode(str_repeat('x', 32)));
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]);
    }
);

return $app;
