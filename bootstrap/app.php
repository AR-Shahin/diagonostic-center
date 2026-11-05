<?php

use App\Http\Middleware\CacheClearMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Request;
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
        ])->append([
            CacheClearMiddleware::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Request $request, AuthenticationException $exception) {
            if (request()->expectsJson()) {
                return Response()->json(['error' => 'UnAuthorized'], 401);
            }

            $guard = data_get($exception->guards(), 0);
            switch ($guard) {
                case 'admin':
                    $login = 'admin.login';
                    break;
                default:
                    $login = 'login';
                    break;
            }

            return redirect()->guest(route($login));
        });
    })->create();
