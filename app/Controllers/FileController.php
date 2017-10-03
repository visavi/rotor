<?php

namespace App\Controllers;

class FileController extends BaseController
{
    /**
     * Главная страница
     */
    public function __call($action, $params)
    {
        if (! $action) {
            return view('files/index');
        }

        if (! preg_match('|^[a-z0-9_\-/]+$|i', $action)) {
            abort(404);
        }

        $action = str_contains($action, '/') ? $action : $action.'/index';

        if (! file_exists(RESOURCES.'/views/files/'.$action.'.blade.php')) {
            abort(404);
        }

        return view('files/layout', compact('action'));

    }
}
