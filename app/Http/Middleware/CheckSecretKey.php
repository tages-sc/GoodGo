<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSecretKey
{
    /**
     * Verifica che la richiesta contenga la secret-key corretta nell'header.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secretKey = config('services.api.secret_key');

        if (empty($secretKey)) {
            return response()->json([
                'error' => 500,
                'message' => 'API secret key not configured',
            ], 500);
        }

        if ($request->header('secret-key') !== $secretKey) {
            return response()->json([
                'error' => 401,
                'message' => 'Invalid or missing secret key',
            ], 401);
        }

        return $next($request);
    }
}
