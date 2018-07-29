<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Polling;
use App\Models\Vote;
use App\Models\VoteAnswer;

class VoteController extends BaseController
{
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

        return view('votes/index', compact('votes', 'page'));
    }

    /**
     * Просмотр голосования
     *
     * @param int $id
     * @return string
     */
    public function view($id): string
    {
        $show = Request::input('show');

        $vote = Vote::query()->find($id);

        if (! $vote) {
            abort(404, 'Данного голосования не существует!');
        }

        if ($vote->closed) {
            abort('default', 'Данный опрос закрыт для голосования!');
        }

        $vote->answers = VoteAnswer::query()
            ->where('vote_id', $vote->id)
            ->orderBy('id')
            ->get();

        if ($vote->answers->isEmpty()) {
            abort('default', 'Для данного голосования не созданы варианты ответов');
        }

        $vote->poll = $vote->pollings()
            ->where('user_id', getUser('id'))
            ->first();

        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $poll  = int(Request::input('poll'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->empty($vote->poll, 'Вы уже проголосовали в этом опросе!')
                ->notEmpty($poll, 'Вы не выбрали вариант ответа!');

            $answer = VoteAnswer::query()->where('id', $poll)->where('vote_id', $vote->id)->first();

            if ($poll) {
                $validator->notEmpty($answer, 'Ответ для данного голосования не найден!');
            }

            if ($validator->isValid()) {
                $vote->increment('count');
                $answer->increment('result');

                Polling::query()->create([
                    'relate_type' => Vote::class,
                    'relate_id'   => $vote->id,
                    'user_id'     => getUser('id'),
                    'vote'        => '+',
                    'created_at'  => SITETIME,
                ]);

                setFlash('success', 'Ваш голос успешно принят!');
                redirect('/votes/'.$vote->id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $voted = array_pluck($vote->answers, 'result', 'answer');
        $max   = max($voted);

        arsort($voted);

        $info['voted'] = $voted;
        $info['sum']   = $vote->count > 0 ? $vote->count : 1;
        $info['max']   = $max > 0 ? $max : 1;

        return view('votes/view', compact('vote', 'show', 'info'));
    }

    /**
     * Проголосовавшие
     *
     * @param int $id
     * @return string
     */
    public function voters($id): string
    {
        $vote = Vote::query()->find($id);

        if (! $vote) {
            abort(404, 'Данного голосования не существует!');
        }

        $voters = Polling::query()
            ->where('relate_type', Vote::class)
            ->where('relate_id', $vote->id)
            ->limit(50)
            ->with('user')
            ->get();

        return view('votes/voters', compact('vote', 'voters'));
    }

    /**
     * История голосований
     *
     * @return string
     */
    public function history(): string
    {
        $total = Vote::query()->where('closed', 1)->count();
        $page = paginate(setting('allvotes'), $total);

        $votes = Vote::query()
            ->where('closed', 1)
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('topic')
            ->get();

        return view('votes/history', compact('votes', 'page'));
    }

    /**
     * Результаты истории голосований
     *
     * @param int $id
     * @return string
     */
    public function viewHistory($id): string
    {
        $vote = Vote::query()->find($id);

        if (! $vote) {
            abort(404, 'Данного голосования не существует!');
        }

        if (! $vote->closed) {
            abort('default', 'Данный опрос еще не в архиве!');
        }

        $vote->answers = VoteAnswer::query()
            ->where('vote_id', $vote->id)
            ->orderBy('id')
            ->get();

        if ($vote->answers->isEmpty()) {
            abort('default', 'Для данного голосования не созданы варианты ответов');
        }

        $voted = array_pluck($vote->answers, 'result', 'answer');
        $max   = max($voted);

        arsort($voted);

        $info['voted'] = $voted;
        $info['sum']   = $vote->count > 0 ? $vote->count : 1;
        $info['max']   = $max > 0 ? $max : 1;

        return view('votes/view_history', compact('vote', 'info'));
    }

    /**
     * Создание голосования
     *
     * @return string
     */
    public function create(): string
    {
        if (Request::isMethod('post')) {

            $token    = check(Request::input('token'));
            $question = check(Request::input('question'));
            $answers  = check(Request::input('answer'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($question, 5, 100, ['question' => 'Слишком длинный или короткий текст вопроса!']);

            $answers = array_unique(array_diff($answers, ['']));

            foreach ($answers as $answer) {
                if (utfStrlen($answer) > 50) {
                    $validator->addError(['answer' => 'Длина вариантов ответа не должна быть более 50 символов!']);
                    break;
                }
            }

            $validator->between(count($answers), 2, 10, ['answer' => 'Недостаточное количество вариантов ответов!']);

            if ($validator->isValid()) {

                $vote = Vote::query()->create([
                    'title'      => $question,
                    'created_at' => SITETIME,
                ]);

                $prepareAnswers = [];
                foreach ($answers as $answer) {
                    $prepareAnswers[] = [
                        'vote_id' => $vote->id,
                        'answer'  => $answer
                    ];
                }

                VoteAnswer::query()->insert($prepareAnswers);

                setFlash('success', 'Голосование успешно создано!');
                redirect('/votes/' . $vote->id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('votes/create');
    }
}
