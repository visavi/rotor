<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Vote;
use App\Models\VoteAnswer;
use App\Models\VotePoll;

class VoteController extends BaseController
{
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
            ->limit(setting('allvotes'))
            ->get();

        return view('vote/index', compact('votes', 'page'));
    }

    /**
     * Просмотр голосования
     */
    public function view($id)
    {
        $show = Request::input('show');

        $vote = Vote::query()->find($id);

        if (! $vote) {
            abort(404, 'Данного голосования не существует!');
        }

        if ($vote['closed']) {
            abort('default', 'Данный опрос закрыт для голосования!');
        }

        $vote['answers'] = VoteAnswer::query()
            ->where('vote_id', $vote['id'])
            ->orderBy('id')
            ->get();

        if ($vote['answers']->isEmpty()) {
            abort('default', 'Для данного голосования не созданы варианты ответов');
        }

        $vote['poll'] = VotePoll::query()
            ->where('vote_id', $vote['id'])
            ->where('user_id', getUser('id'))
            ->first();

        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $poll  = abs(intval(Request::input('poll')));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->empty($vote['poll'], 'Вы уже проголосовали в этом опросе!')
                ->notEmpty($poll, 'Вы не выбрали вариант ответа!');

            $answer = VoteAnswer::query()->where('id', $poll)->where('vote_id', $vote->id)->first();

            if ($poll) {
                $validator->notEmpty($answer, 'Ответ для данного голосования не найден!');
            }

            if ($validator->isValid()) {
                $vote->increment('count');
                $answer->increment('result');

                VotePoll::query()->create([
                    'vote_id'    => $vote->id,
                    'user_id'    => getUser('id'),
                    'created_at' => SITETIME,
                ]);

                setFlash('success', 'Ваш голос успешно принят!');
                redirect('/votes/'.$vote->id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $results = array_pluck($vote['answers'], 'result', 'answer');
        $max = max($results);

        arsort($results);

        $vote['voted'] = $results;

        $vote['sum'] = ($vote['count'] > 0) ? $vote['count'] : 1;
        $vote['max'] = ($max > 0) ? $max : 1;

        return view('vote/view', compact('vote', 'show'));
    }

    /**
     * Проголосовавшие
     */
    public function voters($id)
    {
        $vote = Vote::query()->find($id);

        if (! $vote) {
            abort(404, 'Данного голосования не существует!');
        }

        $voters = VotePoll::query()
            ->where('vote_id', $vote['id'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('vote/voters', compact('vote', 'voters'));
    }

    /**
     * История голосований
     */
    public function history()
    {
        $total = Vote::query()->where('closed', 1)->count();
        $page = paginate(setting('allvotes'), $total);

        $votes = Vote::query()
            ->where('closed', 1)
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit(setting('allvotes'))
            ->get();

        return view('vote/history', compact('votes', 'page'));
    }

    /**
     * Результаты истории голосований
     */
    public function viewHistory($id)
    {
        $vote = Vote::query()->find($id);

        if (! $vote) {
            abort(404, 'Данного голосования не существует!');
        }

        if (! $vote['closed']) {
            abort('default', 'Данный опрос еще не в архиве!');
        }

        $vote['answers'] = VoteAnswer::query()
            ->where('vote_id', $vote['id'])
            ->orderBy('id')
            ->get();

        if ($vote['answers']->isEmpty()) {
            abort('default', 'Для данного голосования не созданы варианты ответов');
        }

        $results = array_pluck($vote['answers'], 'result', 'answer');
        $max = max($results);

        arsort($results);

        $vote['voted'] = $results;

        $vote['sum'] = ($vote['count'] > 0) ? $vote['count'] : 1;
        $vote['max'] = ($max > 0) ? $max : 1;

        return view('vote/view_history', compact('vote'));
    }
}
