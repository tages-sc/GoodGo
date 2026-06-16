<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'user.type' => \App\Http\Middleware\CheckUserType::class,
            'policy.accepted' => \App\Http\Middleware\EnsurePolicyAccepted::class,
            'partner.profile.complete' => \App\Http\Middleware\EnsurePartnerProfileComplete::class,
            'api.secret' => \App\Http\Middleware\CheckSecretKey::class,
            'api.locale' => \App\Http\Middleware\SetApiLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
