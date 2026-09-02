<?php

use App\Http\Requests\StoreClienteRequest;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

$isDuplicateKey = function (QueryException $e): bool {
    $sqlState = $e->getPrevious()?->errorInfo[0] ?? null;

    return $sqlState === '23000'
        || str_contains($e->getMessage(), 'Duplicate entry');
};

$duplicatedField = function (QueryException $e): ?string {
    if (preg_match("/for key '([^']+)'/", $e->getMessage(), $matches)) {
        $key = $matches[1];

        // el nombre del índice único coincide con la columna: tabla.columna
        return str_contains($key, '.') ? explode('.', $key)[1] : $key;
    }

    return null;
};

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) use ($isDuplicateKey, $duplicatedField): void {
        // responde en JSON solo para las rutas de la API
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*'));

        // errores de validación: indican el primer mensaje según las reglas
        $exceptions->render(function (ValidationException $e, Request $request) {
            return ApiResponse::error(
                message: $e->validator->errors()->first(),
                statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        });

        // duplicados por atributos únicos de la tabla
        $exceptions->render(function (QueryException $e, Request $request) use ($isDuplicateKey, $duplicatedField) {
            if (! $isDuplicateKey($e) || ! $field = $duplicatedField($e)) {
                return null;
            }

            Log::error('Violación de unicidad', ['error' => $e->getMessage()]);

            $messages = (new StoreClienteRequest)->messages();
            $message = $messages[$field.'.unique'] ?? 'El valor ya se encuentra registrado';

            return ApiResponse::error(
                message: $message,
                statusCode: Response::HTTP_CONFLICT,
            );
        });

        // recurso no encontrado
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            return ApiResponse::error(
                message: 'Recurso no encontrado',
                statusCode: Response::HTTP_NOT_FOUND,
            );
        });

        // excepciones HTTP (404, 405, 401, etc.)
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            return ApiResponse::error(
                message: Response::$statusTexts[$e->getStatusCode()] ?? 'Ocurrió un error',
                statusCode: $e->getStatusCode(),
            );
        });

        // resto de excepciones: respuesta 500 en el mismo formato
        $exceptions->render(function (Throwable $e, Request $request) {
            Log::error('Excepción no controlada', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(
                message: 'Ocurrió un error en el servidor',
                statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        });
    })->create();
