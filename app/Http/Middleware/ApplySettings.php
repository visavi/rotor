<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Illuminate\View\FileViewFinder;
use Throwable;

class ApplySettings
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = auth()->user();
        } catch (Throwable) {
            $user = null;
        }

        $language = $user->language ?? setting('language', config('app.locale'));
        $theme = $user->themes ?? setting('themes', 'default');

        if ($request->session()->has('language')) {
            $language = $request->session()->get('language');
        }

        if (! file_exists(resource_path('lang/' . $language))) {
            $language = setting('language', config('app.locale'));
        }

        if (! file_exists(resource_path('views/themes/' . $theme))) {
            $theme = setting('themes', 'default');
        }

        App::setLocale($language);

        // replaceNamespace, а не addNamespace: последний накапливает пути, и внутри
        // одного процесса (тесты, octane) остаётся тема, отрендеренная первой.
        // flush() по той же причине сбрасывает кеш уже найденных шаблонов
        $finder = app('view')->getFinder();
        $finder->flush();

        View::replaceNamespace('theme', resource_path('views/themes/' . $theme));

        $themeViews = resource_path('views/themes/' . $theme . '/views');

        if ($finder instanceof FileViewFinder) {
            // Порядок поиска шаблона: custom -> тема -> модуль (ядро).
            // prependNamespace ставит путь в начало, поэтому custom подкладывается последним
            $this->prependOverrides($finder, $themeViews);
            $this->prependOverrides($finder, resource_path('custom/views'));

            // Шаблоны ядра идут без неймспейса и ищутся по view.paths, а не по хинтам,
            // поэтому путь темы вставляется туда же — перед resources/views.
            // Список пересобирается из конфига: иначе при смене темы пути копились бы
            $paths = config('view.paths');

            if (is_dir($themeViews)) {
                $position = array_search(resource_path('views'), $paths, true);
                array_splice($paths, $position === false ? count($paths) : $position, 0, [$themeViews]);
            }

            $finder->setPaths($paths);
        }

        // Части оформления идут через неймспейс theme, а не через имя темы в пути
        $customTheme = resource_path('custom/views/themes/' . $theme);

        if (is_dir($customTheme)) {
            View::prependNamespace('theme', $customTheme);
        }

        return $next($request);
    }

    /**
     * Подкладывает каталоги переопределений в начало соответствующих неймспейсов
     *
     * Один scandir вместо is_dir по каждому неймспейсу: модулей несколько десятков,
     * а каталога переопределений у большинства сайтов нет вовсе
     */
    private function prependOverrides(FileViewFinder $finder, string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $hints = $finder->getHints();

        foreach (scandir($directory) ?: [] as $name) {
            if (isset($hints[$name]) && is_dir($directory . '/' . $name)) {
                View::prependNamespace($name, $directory . '/' . $name);
            }
        }
    }
}
