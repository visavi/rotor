<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append([
            // \App\Http\Middleware\TrustHosts::class,
            \Illuminate\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            \App\Http\Middleware\CheckInstallSite::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\AuthenticateCookie::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        ]);

        $middleware->group('web', [
            \App\Http\Middleware\CheckAccessSite::class,
        ]);

        $middleware->alias([
            'check.admin' => \App\Http\Middleware\CheckAdmin::class,
            'check.user'  => \App\Http\Middleware\CheckUser::class,
            'check.token' => \App\Http\Middleware\CheckToken::class,
            'check.ajax'  => \App\Http\Middleware\CheckAjax::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(function (Throwable $exception) {
            $statusCode = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode()
                : 500;

            saveErrorLog($statusCode, $exception->getMessage());
        });

        $exceptions->renderable(function (HttpExceptionInterface $exception, Request $request) {
            saveErrorLog($exception->getStatusCode(), $exception->getMessage());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage() ?: __('errors.error'),
                ], $exception->getStatusCode());
            }

            if (! view()->exists('errors.' . $exception->getStatusCode())) {
                return response()->view('errors.default', ['exception' => $exception]);
            }

            return (new Handler(app()))->render($request, $exception);
        });
    })
    ->create();
