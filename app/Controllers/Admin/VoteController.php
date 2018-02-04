<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\User;
use App\Models\Vote;

class VoteController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::MODER)) {
            abort('403', 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $total = Vote::query()->where('closed', 0)->count();
        $page = paginate(setting('allvotes'), $total);

        $votes = Vote::query()
            ->where('closed', 0)
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->get();

        return view('admin/vote/index', compact('votes', 'page'));
    }

    /**
     * Редактирование голосования
     */
    public function edit($id)
    {
        $vote = Vote::query()->where('id', $id)->first();

        if (! $vote) {
            abort(404, 'Данного голосования не существует!');
        }


        if (Request::isMethod('post')) {

            $token   = check(Request::input('token'));
            $title   = check(Request::input('title'));
            $answers = check(Request::input('answers'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

            $validator->length($title, 5, 100, ['title' => 'Слишком длинный или короткий текст вопроса!']);
            $answers = array_unique(array_diff($answers, ['']));

            foreach ($answers as $answer) {
                if (utfStrlen($answer) > 50) {
                    $validator->addError(['answers' => 'Длина вариантов ответа не должна быть более 50 символов!']);
                    break;
                }
            }

            $validator->between(count($answers), 2, 10, ['answer' => 'Недостаточное количество вариантов ответов!']);

            if ($validator->isValid()) {

/*                $vote->update([
                    'title'      => $title,
                ]);*/

                //var_dump(Request::all()); exit;

/*                $prepareAnswers = [];
                foreach ($answers as $answer) {
                    $prepareAnswers[] = [
                        'vote_id' => $vote->id,
                        'answer'  => $answer
                    ];
                }*/

                //VoteAnswer::query()->insert($prepareAnswers);

                setFlash('success', 'Голосование успешно изменено!');
                redirect('/admin/votes/edit/'.$vote->id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/vote/edit', compact('vote'));
    }
}
