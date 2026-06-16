<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetApiLocale
{
    /**
     * Imposta la lingua dell'app in base all'header x-lang.
     * Lingue supportate: it (default), en.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('x-lang', 'it');

        if (in_array($locale, ['it', 'en'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
