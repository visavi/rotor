<?php

use App\Http\Middleware\AdminLogger;
use App\Http\Middleware\ApplySettings;
use App\Http\Middleware\CheckAccessSite;
use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckInstallSite;
use App\Http\Middleware\CheckThrottle;
use App\Http\Middleware\CheckToken;
use App\Http\Middleware\CheckUser;
use App\Http\Middleware\CheckUserState;
use App\Http\Middleware\SaveStatistic;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Session\TokenMismatchException;
use Illuminate\View\Middleware\ShareErrorsFromSession;
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
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
        ]);

        $middleware->group('web', [
            ApplySettings::class,
            CheckInstallSite::class,
            CheckThrottle::class,
            CheckAccessSite::class,
            CheckUserState::class,
            SaveStatistic::class,

            ShareErrorsFromSession::class,
            PreventRequestForgery::class,
            SubstituteBindings::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
        ]);

        $middleware->alias([
            'check.admin'  => CheckAdmin::class,
            'check.user'   => CheckUser::class,
            'check.token'  => CheckToken::class,
            'admin.logger' => AdminLogger::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('delete:files')->daily();
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
                    'message' => __('validator.token'),
                ]);
            }

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
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
