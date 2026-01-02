<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;

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

        return $next($request);
    }
}
