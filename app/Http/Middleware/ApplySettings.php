<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class ApplySettings
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        $language = $user->language ?? setting('language');
        $theme = $user->themes ?? setting('themes');

        if ($request->session()->has('language')) {
            $language = $request->session()->get('language');
        }

        if (! file_exists(resource_path('lang/' . $language))) {
            $language = setting('language');
        }

        if (! file_exists(public_path('themes/' . $theme))) {
            $theme = setting('themes');
        }

        App::setLocale($language);
        View::addLocation(public_path('themes/' . $theme . '/views'));

        return $next($request);
    }
}
