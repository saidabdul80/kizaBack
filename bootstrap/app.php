<?php

use App\Http\Middleware\CheckScope;
use App\Http\Middleware\CheckScopes;
use App\Http\Middleware\MutateVariable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: [
            __DIR__.'/../routes/api.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens([
                'api/*'
        ]);
        $middleware->api([
            MutateVariable::class,
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
        $middleware->alias([
            //"configuration" =>  \App\Http\Middleware\ConfigurationPermission::class,
            'idempotency' => \App\Http\Middleware\Idempotency::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'verify_existing_user' => \App\Http\Middleware\VerifyExistingUser::class,
            'scope' => CheckScopes::class,
        
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }
     
            return $request->expectsJson();
        });

        /* $exceptions->catch(function (Request $request, Throwable $e) {
            if ($e instanceof \Exception && $e->getCode() === 401) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return null;
        }); */
        
        if ($exceptions instanceof ModelNotFoundException) {
            // Customize the response for ModelNotFoundException
            return response()->json(['error' => 'Model not found'], 404);
        }

    })->create();
