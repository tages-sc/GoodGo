<?php

namespace App\Http\Middleware;

use App\Models\PolicyVersion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePolicyAccepted
{
    /**
     * Route da escludere dal controllo (per permettere l'accettazione)
     */
    protected array $except = [
        'policy.accept',
        'policy.show',
        'logout',
    ];

    /**
     * Handle an incoming request.
     *
     * Verifica che l'utente abbia accettato le ultime versioni
     * di Privacy Policy e Terms of Service.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Se non autenticato, passa
        if (! $user) {
            return $next($request);
        }

        // Se la route è esclusa, passa
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }

        // Ottieni le ultime versioni pubblicate
        $latestPrivacy = PolicyVersion::getLatestPrivacyPolicy();
        $latestTerms = PolicyVersion::getLatestTerms();

        // Se non ci sono policy pubblicate, passa
        if (! $latestPrivacy && ! $latestTerms) {
            return $next($request);
        }

        // Verifica Privacy Policy
        $privacyAccepted = ! $latestPrivacy ||
            ($user->privacy_version_id === $latestPrivacy->id && $user->privacy_accepted_at !== null);

        // Verifica Terms
        $termsAccepted = ! $latestTerms ||
            ($user->terms_version_id === $latestTerms->id && $user->terms_accepted_at !== null);

        // Se entrambi accettati, passa
        if ($privacyAccepted && $termsAccepted) {
            return $next($request);
        }

        // Altrimenti, redirect alla pagina di accettazione
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Devi accettare le nuove policy prima di continuare.',
                'requires_policy_acceptance' => true,
                'privacy_accepted' => $privacyAccepted,
                'terms_accepted' => $termsAccepted,
            ], 403);
        }

        return redirect()->route('policy.accept');
    }

    /**
     * Verifica se la request deve passare senza controllo
     */
    protected function shouldPassThrough(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        if (! $routeName) {
            return false;
        }

        return in_array($routeName, $this->except);
    }
}
