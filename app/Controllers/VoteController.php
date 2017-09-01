<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validation;
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
        $votes = Vote::where('closed', 0)
            ->orderBy('created_at')
            ->get();

        return view('vote/index', compact('votes'));
    }

    /**
     * Просмотр голосования
     */
    public function view($id)
    {
        $show = Request::get('show');

        $vote = Vote::find($id);

        if (! $vote) {
            abort(404, 'Данного голосования не существует!');
        }

        if (! empty($votes['closed'])) {
            abort('default', 'Данный опрос закрыт для голосования!');
        }

        $vote['answers'] = VoteAnswer::where('vote_id', $vote['id'])
            ->orderBy('id')
            ->get();

        if ($vote['answers']->isEmpty()) {
            abort('default', 'Для данного голосования не созданы варианты ответов');
        }

        $vote['poll'] = VotePoll::where('vote_id', $vote['id'])
            ->where('user_id', getUserId())
            ->first();

        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $poll  = abs(intval(Request::input('poll')));

            $validation = new Validation();
            $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('empty', $vote['poll'], 'Вы уже проголосовали в этом опросе!')
                ->addRule('not_empty', $poll, 'Вы не выбрали вариант ответа!');

            $answer = VoteAnswer::where('id', $poll)->where('vote_id', $vote->id)->first();

            if ($poll) {
                $validation->addRule('not_empty', $answer, 'Ответ для данного голосования не найден!');
            }

            if ($validation->run()) {
                $vote->increment('count');
                $answer->increment('result');

                VotePoll::create([
                    'vote_id'    => $vote->id,
                    'user_id'    => getUserId(),
                    'created_at' => SITETIME,
                ]);

                setFlash('success', 'Ваш голос успешно принят!');
                redirect('/votes/'.$vote->id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validation->getErrors());
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
        $vote = Vote::find($id);

        if (! $vote) {
            abort(404, 'Данного голосования не существует!');
        }

        $voters = VotePoll::where('vote_id', $vote['id'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('vote/voters', compact('vote', 'voters'));
    }
}
