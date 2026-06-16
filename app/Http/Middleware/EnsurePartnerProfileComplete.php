<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePartnerProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isPartner() && !$user->isProfileComplete()) {
            // Non bloccare se è già sulla pagina profilo
            if (!$request->routeIs('profile.show')) {
                session()->flash('warning', 'Completa il tuo profilo aziendale per poter utilizzare tutte le funzionalita.');
                return redirect()->route('profile.show');
            }
        }

        return $next($request);
    }
}
