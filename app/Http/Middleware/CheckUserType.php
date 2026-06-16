<?php

namespace App\Http\Middleware;

use App\Enums\UserType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    /**
     * Handle an incoming request.
     *
     * Uso: middleware('user.type:super_admin,ente,organizer')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$types  I tipi di utente consentiti
     */
    public function handle(Request $request, Closure $next, string ...$types): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Non autenticato.'], 401);
            }

            return redirect()->route('login');
        }

        // Converti i tipi stringa in enum
        $allowedTypes = array_map(
            fn($type) => UserType::tryFrom($type),
            $types
        );

        // Rimuovi eventuali null (tipi non validi)
        $allowedTypes = array_filter($allowedTypes);

        // Super Admin ha sempre accesso
        if ($user->type === UserType::SUPER_ADMIN) {
            return $next($request);
        }

        // Verifica se il tipo utente è tra quelli consentiti
        if (in_array($user->type, $allowedTypes, true)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Accesso non autorizzato.'], 403);
        }

        abort(403, 'Accesso non autorizzato per questo tipo di utente.');
    }
}
