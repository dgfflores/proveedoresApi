<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(mixed $data = [], string $message = 'Operación exitosa', int $statusCode = 200, mixed $metadata = null): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ];

        return response()->json($response, $statusCode);
    }

    public static function error(string $message = 'Error en la petición', int $statusCode = 400, mixed $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $statusCode);
    }
}
