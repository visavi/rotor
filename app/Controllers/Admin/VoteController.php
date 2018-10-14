<?php

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Http\Request;

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
     *
     * @return string
     */
    public function index(): string
    {
        $total = Vote::query()->where('closed', 0)->count();
        $page = paginate(setting('allvotes'), $total);

        $votes = Vote::query()
            ->where('closed', 0)
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('topic')
            ->get();

        return view('admin/votes/index', compact('votes', 'page'));
    }

    /**
     * Архив голосований
     *
     * @return string
     */
    public function history(): string
    {
        $total = Vote::query()->where('closed', 0)->count();
        $page = paginate(setting('allvotes'), $total);

        $votes = Vote::query()
            ->where('closed', 1)
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('topic')
            ->get();

        return view('admin/votes/history', compact('votes', 'page'));
    }

    /**
     * Редактирование голосования
     *
     * @param int $id
     * @return string
     */
    public function edit(int $id): string
    {
        $vote = Vote::query()->where('id', $id)->first();

        if (! $vote) {
            abort(404, 'Данного голосования не существует!');
        }

        if ($request->isMethod('post')) {

            $token   = check($request->input('token'));
            $title   = check($request->input('title'));
            $answers = check($request->input('answers'));

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

            $validator->between(\count($answers), 2, 10, ['answer' => 'Недостаточное количество вариантов ответов!']);

            if ($validator->isValid()) {

                $vote->update([
                    'title' => $title,
                ]);

                foreach ($answers as $answerId => $answer) {
                    $vote->answers()
                        ->updateOrCreate(['id' => $answerId], [
                            'answer' => $answer
                        ]);
                }

                setFlash('success', 'Голосование успешно изменено!');
                redirect('/admin/votes/edit/'.$vote->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $getAnswers = $vote->answers->pluck('answer', 'id')->all();

        return view('admin/votes/edit', compact('vote', 'getAnswers'));
    }

    /**
     * Удаление голосования
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void
    {
        $token = check($request->input('token'));
        $vote  = Vote::query()->where('id', $id)->first();

        if (! $vote) {
            abort(404, 'Данного голосования не существует!');
        }

        if (! isAdmin(User::BOSS)) {
            abort(404, 'Доступ запрещен!');
        }

        if ($token === $_SESSION['token']) {

            DB::transaction(function () use ($vote) {
                $vote->delete();
                $vote->answers()->delete();
                $vote->pollings()->delete();
            });

            setFlash('success', 'Голосование успешно удалено!');
        } else {
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        redirect('/admin/votes');
    }

    /**
     * Открытие-закрытие голосования
     *
     * @param int $id
     * @return void
     */
    public function close(int $id): void
    {
        $token = check($request->input('token'));
        $vote  = Vote::query()->where('id', $id)->first();

        if (! $vote) {
            abort(404, 'Данного голосования не существует!');
        }

        if ($token === $_SESSION['token']) {

            $type   = 'открыто';
            $closed = $vote->closed ^ 1;

            $vote->update([
                'closed' => $closed,
            ]);

            if ($closed) {
                $vote->pollings()->delete();
                $type = 'закрыто' ;
            }

            setFlash('success', 'Голосование успешно ' . $type . '!');
        } else {
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        if (empty($closed)) {
            redirect('/admin/votes');
        }  else {
            redirect('/admin/votes/history');
        }
    }

    /**
     * Пересчет голосов
     *
     * @return void
     */
    public function restatement(): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $token = check($request->input('token'));

        if ($token === $_SESSION['token']) {

            DB::update('update vote set count = (select SUM(result) from voteanswer where vote.id = voteanswer.vote_id)');

            setFlash('success', 'Голосования успешно пересчитаны!');
        } else {
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        redirect('/admin/votes');
    }
}
