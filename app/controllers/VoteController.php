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

        $results = array_pluck($vote['answers'], 'result', 'answer');
        $max = max($results);

        arsort($results);

        $vote['voted'] = $results;

        $vote['sum'] = ($vote['count'] > 0) ? $vote['count'] : 1;
        $vote['max'] = ($max > 0) ? $max : 1;

        App::view('vote/view', compact('vote', 'show'));
    }
}
