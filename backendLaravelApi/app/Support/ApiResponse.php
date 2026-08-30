<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'Operación exitosa',
        int $statusCode = Response::HTTP_OK
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    public static function error(
        string $message = 'Ocurrió un error',
        int $statusCode = Response::HTTP_BAD_REQUEST
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => null,
        ], $statusCode);
    }
}
