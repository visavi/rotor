<?php

class PageController extends BaseController
{
    /**
     * Главная страница
     */
    public function __call($action, $params)
    {
        if (! preg_match('|^[a-z0-9_\-]+$|i', $action)) {
            App::abort(404);
        }

        if (! file_exists(APP.'/views/main/'.$action.'.blade.php')){
            App::abort(404);
        }

        if (! is_user() && $action == 'menu'){
            App::abort(404);
        }

        App::view('main/'.$action);
    }
}
