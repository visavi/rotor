<?php

declare(strict_types=1);

namespace App\Controllers;

use Illuminate\Support\Str;

class FileController extends BaseController
{
    /**
     * Главная страница
     *
     * @param string $page
     *
     * @return string
     */
    public function index(string $page = 'index'): string
    {
        if ($page === 'index') {
            return view('files/index');
        }

        if (! preg_match('|^[a-z0-9_\-/]+$|i', $page)) {
            abort(404);
        }

        $page = Str::contains($page, '/') ? $page : $page . '/index';

        if (! file_exists(RESOURCES . '/views/files/' . $page . '.blade.php')) {
            abort(404);
        }

        return view('files/layout', compact('page'));
    }
}
