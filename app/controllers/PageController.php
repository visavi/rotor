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

    /**
     * Теги
     */
    public function tags()
    {
        App::view('pages/tags');
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
                [Setting::get('title'), round(Setting::get('maxbantime') / 1440)],
                $rules['text']
            );
        }

        App::view('pages/rules', compact('rules'));
    }

    /**
     * Смайлы
     */
    public function smiles()
    {
        $total = Smile::count();
        $page = App::paginate(Setting::get('smilelist'), $total);

        $smiles = Smile::orderBy(Capsule::raw('CHAR_LENGTH(`code`)'))
            ->orderBy('name')
            ->limit(Setting::get('smilelist'))
            ->offset($page['offset'])
            ->get();

        App::view('pages/smiles', compact('smiles', 'page'));
    }
}
