<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FileController extends Controller
{
    /**
     * Главная страница
     */
    public function index(string $page = 'index'): View
    {
        if ($page === 'index') {
            return view('files/index');
        }

        if (! preg_match('|^[a-z0-9_\-/]+$|i', $page)) {
            abort(404);
        }

        $page = Str::contains($page, '/') ? $page : $page . '/index';

        // Поиск через вьюхи, а не по файлу: страницу можно положить
        // в resources/custom/views — её не затрёт обновление ядра
        if (! ViewFacade::exists('files/' . $page)) {
            abort(404);
        }

        return view('files/layout', compact('page'));
    }
}
