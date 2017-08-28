<?php

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

        App::view('vote/index', compact('votes'));
    }

    /**
     * Просмотр голосования
     */
    public function view($id)
    {
        $show = Request::get('show');

        $vote = Vote::find($id);

        if (! $vote) {
            App::abort(404, 'Данного голосования не существует!');
        }

        if (! empty($votes['closed'])) {
            App::abort('default', 'Данный опрос закрыт для голосования!');
        }

        $vote['answers'] = VoteAnswer::where('vote_id', $vote['id'])
            ->orderBy('id')
            ->get();

        if ($vote['answers']->isEmpty()) {
            App::abort('default', 'Для данного голосования не созданы варианты ответов');
        }

        $vote['poll'] = VotePoll::where('vote_id', $vote['id'])
            ->where('user_id', App::getUserId())
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
                    'user_id'    => App::getUserId(),
                    'created_at' => SITETIME,
                ]);

                App::setFlash('success', 'Ваш голос успешно принят!');
                App::redirect('/votes/'.$vote->id);
            } else {
                App::setInput(Request::all());
                App::setFlash('danger', $validation->getErrors());
            }
        }

        $results = array_pluck($vote['answers'], 'result', 'answer');
        $max = max($results);

        arsort($results);

        $vote['voted'] = $results;

        $vote['sum'] = ($vote['count'] > 0) ? $vote['count'] : 1;
        $vote['max'] = ($max > 0) ? $max : 1;

        App::view('vote/view', compact('vote', 'show'));
    }

    /**
     * Проголосовавшие
     */
    public function voters($id)
    {
        $vote = Vote::find($id);

        if (! $vote) {
            App::abort(404, 'Данного голосования не существует!');
        }

        $voters = VotePoll::where('vote_id', $vote['id'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        App::view('vote/voters', compact('vote', 'voters'));
    }
}
