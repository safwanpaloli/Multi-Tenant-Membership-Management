<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                $statusCode = 500;
                $message = $e->getMessage() ?: 'Server Error';
                $errors = [];

                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $statusCode = 422;
                    $message = 'Validation failed.';
                    $errors = collect($e->errors())->flatten()->toArray();
                } elseif ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    $statusCode = 404;
                    $message = 'Resource not found.';
                } elseif ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    $statusCode = 401;
                    $message = 'Unauthenticated.';
                } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                    $statusCode = $e->getStatusCode();
                } elseif ($e instanceof \InvalidArgumentException) {
                    $statusCode = 400;
                }

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => $errors,
                ], $statusCode);
            }
        });
    })->create();
