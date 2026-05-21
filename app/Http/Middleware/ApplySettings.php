<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Illuminate\View\FileViewFinder;

class ApplySettings
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        $language = $user->language ?? setting('language');
        $theme = $user->themes ?? setting('themes');

        if ($request->session()->has('language')) {
            $language = $request->session()->get('language');
        }

        if (! file_exists(resource_path('lang/' . $language))) {
            $language = setting('language');
        }

        if (! file_exists(resource_path('views/themes/' . $theme))) {
            $theme = setting('themes');
        }

        App::setLocale($language);
        View::addNamespace('theme', resource_path('views/themes/' . $theme));

        // Позволяет переопределять страницы для определенной темы
        $finder = app('view')->getFinder();
        if ($finder instanceof FileViewFinder) {
            foreach ($finder->getHints() as $namespace => $paths) {
                $override = resource_path('views/themes/' . $theme . '/views/' . $namespace);

                if (is_dir($override)) {
                    app('view')->prependNamespace($namespace, $override);
                }
            }
        }

        return $next($request);
    }
}
