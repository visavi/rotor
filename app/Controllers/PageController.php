<?php

namespace App\Controllers;

class PageController extends BaseController
{
    /**
     * Главная страница
     */
    public function __call($action, $params)
    {
        if (! preg_match('|^[a-z0-9_\-]+$|i', $action)) {
            abort(404);
        }

        if (! file_exists(APP.'/views/main/'.$action.'.blade.php')){
            abort(404);
        }

        if (! is_user() && $action == 'menu'){
            abort(404);
        }

        view('main/'.$action);
    }

    /**
     * Теги
     */
    public function tags()
    {
        view('pages/tags');
    }

    /**
     * Правила
     */
    public function rules()
    {
        $rules = Rule::first();

        if ($rules) {
            $rules['text'] = str_replace(
                ['%SITENAME%', '%MAXBAN%'],
                [setting('title'), round(setting('maxbantime') / 1440)],
                $rules['text']
            );
        }

        view('pages/rules', compact('rules'));
    }

    /**
     * Смайлы
     */
    public function smiles()
    {
        $total = Smile::count();
        $page = paginate(setting('smilelist'), $total);

        $smiles = Smile::orderBy(DB::raw('CHAR_LENGTH(`code`)'))
            ->orderBy('name')
            ->limit(setting('smilelist'))
            ->offset($page['offset'])
            ->get();

        view('pages/smiles', compact('smiles', 'page'));
    }
}
