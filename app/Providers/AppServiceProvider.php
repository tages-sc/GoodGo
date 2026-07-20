<?php

namespace App\Providers;

use App\Listeners\UpdateLastLoginAt;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Rete di sicurezza: gli helper sono registrati in composer.json
        // (autoload.files), ma se un deploy salta `composer dump-autoload`
        // l'intera app va in errore (viste + validazione tracce). Il file e'
        // protetto da function_exists(), quindi il doppio caricamento e' innocuo.
        require_once __DIR__ . '/../Support/helpers.php';
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, UpdateLastLoginAt::class);
    }
}
