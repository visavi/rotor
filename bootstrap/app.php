<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
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
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\ApplySettings::class,
        ]);

        $middleware->group('web', [
            \App\Http\Middleware\CheckInstallSite::class,
            \App\Http\Middleware\CheckThrottle::class,
            \App\Http\Middleware\CheckAccessSite::class,
            \App\Http\Middleware\CheckUserState::class,

            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
        ]);

        $middleware->alias([
            'check.admin'  => \App\Http\Middleware\CheckAdmin::class,
            'check.user'   => \App\Http\Middleware\CheckUser::class,
            'check.token'  => \App\Http\Middleware\CheckToken::class,
            'check.ajax'   => \App\Http\Middleware\CheckAjax::class,
            'admin.logger' => \App\Http\Middleware\AdminLogger::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('blog:activation')->everyMinute();
        $schedule->command('board:deactivation')->hourly();
        $schedule->command('delete:files')->daily();
        $schedule->command('delete:logins')->daily();
        $schedule->command('delete:logs')->daily();
        $schedule->command('delete:pending')->daily();
        $schedule->command('delete:polls')->weekly();
        $schedule->command('delete:readers')->weekly();
        $schedule->command('delete:dialogues')->daily();
        $schedule->command('add:subscribers')->hourly();
        $schedule->command('add:birthdays')->dailyAt('07:00');
        $schedule->command('message:send')->everyMinute();
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

            if (
                $exception instanceof TokenMismatchException
                || ($exception->getPrevious() instanceof TokenMismatchException)
            ) {
                if (! $request->expectsJson()) {
                    return redirect()->back()
                        ->withInput($request->except('_token'))
                        ->withErrors(['token' => __('validator.token')]);
                }

                return response()->json([
                    'success' => false,
                    'message' => __('validator.token'),
                ]);
            }

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
