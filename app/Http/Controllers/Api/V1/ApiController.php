<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
    /**
     * Risposta di successo con dati.
     */
    protected function success(mixed $data = null, int $status = 200): JsonResponse
    {
        if ($data === null) {
            return response()->json(null, $status);
        }

        return response()->json($data, $status);
    }

    /**
     * Risposta di successo senza payload.
     */
    protected function ok(): JsonResponse
    {
        return response()->json(null, 200);
    }

    /**
     * Risposta di errore nel formato standard API.
     */
    protected function error(int $errorCode, string $message, int $httpStatus = 400): JsonResponse
    {
        return response()->json([
            'error' => $errorCode,
            'message' => $message,
        ], $httpStatus);
    }
}
