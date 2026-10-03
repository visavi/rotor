<?php

use App\Http\Middleware\AdminLogger;
use App\Http\Middleware\ApplyTheme;
use App\Http\Middleware\CheckAccessSite;
use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckInstallSite;
use App\Http\Middleware\CheckThrottle;
use App\Http\Middleware\CheckToken;
use App\Http\Middleware\CheckTokenOptional;
use App\Http\Middleware\CheckUser;
use App\Http\Middleware\CheckUserState;
use App\Http\Middleware\GrantDailyBonus;
use App\Http\Middleware\NormalizeUrl;
use App\Http\Middleware\SaveStatistic;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\StartWebSession;
use App\Services\ScheduleService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\ViewErrorBag;
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
        $middleware->throttleApi();

        $middleware->append([
            NormalizeUrl::class,
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartWebSession::class,
            SetLocale::class,
            ApplyTheme::class,
        ]);

        $middleware->group('web', [
            CheckInstallSite::class,
            CheckThrottle::class,
            CheckAccessSite::class,
            CheckUserState::class,
            GrantDailyBonus::class,
            SaveStatistic::class,

            ShareErrorsFromSession::class,
            PreventRequestForgery::class,
            SubstituteBindings::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
        ]);

        $middleware->alias([
            'check.admin'          => CheckAdmin::class,
            'check.user'           => CheckUser::class,
            'check.token'          => CheckToken::class,
            'check.token.optional' => CheckTokenOptional::class,
            'admin.logger'         => AdminLogger::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('delete:files')->daily();
        $schedule->command('delete:errors')->daily();
        $schedule->command('delete:pending')->daily();
        $schedule->command('delete:polls')->weekly();
        $schedule->command('delete:readers')->weekly();
        $schedule->command('delete:dialogues')->daily();
        $schedule->command('add:subscribers')->hourly();
        $schedule->command('add:birthdays')->dailyAt('07:00');

        // Воркер поднимается на минуту и выходит: очередь разгребается.
        // При sync задача бессмысленна — письма уходят сразу, очереди нет
        $schedule->command('queue:work --stop-when-empty --max-time=50')
            ->everyMinute()
            ->withoutOverlapping()
            ->skip(static fn () => config('queue.default') === 'sync');

        // Метка живого крона: по ней панель понимает, что планировщик запускается.
        // Ставится каждый запуск — «Последний запуск» в панели точен до минуты
        $schedule->call(static fn () => app(ScheduleService::class)->markRun())
            ->everyMinute()
            ->name('schedule-ping');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Одно условие json для всех ошибок. Api отвечает json даже без Accept:
        // html-страница ошибки там не отрисуется, тема для api не подключается.
        // Сайт — как в Laravel по умолчанию: ajax без Accept тоже получает json,
        // иначе подгрузка ленты вставила бы в неё страницу ошибки целиком
        $wantsJson = static fn (Request $request): bool => $request->is('api/*') || $request->expectsJson();

        $exceptions->shouldRenderJsonWhen($wantsJson);

        $exceptions->reportable(function (Throwable $exception) {
            $statusCode = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode()
                : 500;

            saveErrorLog($statusCode, $exception->getMessage());
        });

        $exceptions->renderable(function (HttpExceptionInterface $exception, Request $request) use ($wantsJson) {
            $statusCode = $exception->getStatusCode();

            saveErrorLog($statusCode, $exception->getMessage());

            // TokenMismatchException сюда не доходит: Laravel ещё до колбэков
            // превращает его в HttpException(419) с исходным в previous
            $tokenExpired = $exception->getPrevious() instanceof TokenMismatchException;

            if ($wantsJson($request)) {
                $message = $tokenExpired
                    ? __('validator.token')
                    : ($exception->getMessage() ?: __('errors.error'));

                return response()->json(['message' => $message], $statusCode, $exception->getHeaders());
            }

            if ($tokenExpired) {
                return redirect()->back()
                    ->withInput($request->except('_token'))
                    ->withErrors(['token' => __('validator.token')]);
            }

            // Рисуем сами, а не стандартным обработчиком: его неймспейс errors:: видит
            // только config('view.paths') и пропустил бы шаблоны ошибок из темы
            $view = view()->exists('errors.' . $statusCode) ? 'errors.' . $statusCode : 'errors.default';

            return response()->view($view, [
                'errors'    => new ViewErrorBag(),
                'exception' => $exception,
            ], $statusCode, $exception->getHeaders());
        });
    })
    ->create();
