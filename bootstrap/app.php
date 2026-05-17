<?php

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $message = 'The uploaded file is too large. Product images must be 2 MB or smaller.';

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => $message,
                    'errors' => [
                        'image' => [$message],
                    ],
                ], 413);
            }

            $target = $request->headers->get('referer') ?: url('/products/create');
            $separator = str_contains($target, '?') ? '&' : '?';

            return redirect()->to($target.$separator.'upload_error=too_large');
        });
    })->create();
