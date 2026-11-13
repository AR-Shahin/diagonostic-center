<?php

use App\Http\Middleware\CacheClearMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        // Dev convenience only: clearing every cache on every request would also wipe the
        // login lock-out counter (and slow every page), so it never runs outside local.
        if (env('APP_ENV') === 'local') {
            $middleware->append(CacheClearMiddleware::class);
        }

    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Not logged in: send admins to the admin login, patients to the website login.
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $login = data_get($exception->guards(), 0) === 'admin' ? 'admin.login' : 'login';

            return redirect()->guest(route($login));
        });
    })->create();
