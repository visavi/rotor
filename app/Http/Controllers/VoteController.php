<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Polling;
use App\Models\Vote;
use App\Models\VoteAnswer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class VoteController extends Controller
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $votes = Vote::query()
            ->where('closed', 0)
            ->orderByDesc('created_at')
            ->with('topic')
            ->paginate(setting('allvotes'));

        return view('votes/index', compact('votes'));
    }

    /**
     * Просмотр голосования
     *
     *
     * @return View|RedirectResponse
     */
    public function view(int $id, Request $request, Validator $validator)
    {
        $show = $request->input('show');

        /** @var Vote $vote */
        $vote = Vote::query()->find($id);

        if (! $vote) {
            abort(404, __('votes.voting_not_exist'));
        }

        if ($vote->closed) {
            abort(200, __('votes.voting_closed'));
        }

        $vote->answers = VoteAnswer::query()
            ->where('vote_id', $vote->id)
            ->orderBy('id')
            ->get();

        if ($vote->answers->isEmpty()) {
            abort(200, __('votes.voting_not_answers'));
        }

        $vote->poll = $vote->pollings()
            ->where('user_id', getUser('id'))
            ->first();

        if ($request->isMethod('post')) {
            $poll = int($request->input('poll'));

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->empty($vote->poll, __('votes.voting_passed'))
                ->notEmpty($poll, __('votes.answer_not_chosen'));

            if ($validator->isValid()) {
                $answer = $vote->answers()
                    ->where('id', $poll)
                    ->where('vote_id', $vote->id)
                    ->first();
                $validator->notEmpty($answer, __('votes.answer_not_found'));
            }

            if ($validator->isValid()) {
                $vote->increment('count');
                $answer->increment('result');

                Polling::query()->create([
                    'relate_type' => Vote::$morphName,
                    'relate_id'   => $vote->id,
                    'user_id'     => getUser('id'),
                    'vote'        => $answer->answer,
                    'created_at'  => SITETIME,
                ]);

                setFlash('success', __('votes.voting_success'));

                return redirect('votes/'.$vote->id);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $voted = Arr::pluck($vote->answers, 'result', 'answer');
        $max = max($voted);

        arsort($voted);

        $info['voted'] = $voted;
        $info['sum'] = $vote->count > 0 ? $vote->count : 1;
        $info['max'] = $max > 0 ? $max : 1;

        return view('votes/view', compact('vote', 'show', 'info'));
    }

    /**
     * Проголосовавшие
     */
    public function voters(int $id): View
    {
        /** @var Vote $vote */
        $vote = Vote::query()->find($id);

        if (! $vote) {
            abort(404, __('votes.voting_not_exist'));
        }

        $voters = $vote->pollings()
            ->limit(50)
            ->with('user')
            ->get();

        return view('votes/voters', compact('vote', 'voters'));
    }

    /**
     * История голосований
     */
    public function history(): View
    {
        $votes = Vote::query()
            ->where('closed', 1)
            ->orderByDesc('created_at')
            ->with('topic')
            ->paginate(setting('allvotes'));

        return view('votes/history', compact('votes'));
    }

    /**
     * Результаты истории голосований
     */
    public function viewHistory(int $id): View
    {
        /** @var Vote $vote */
        $vote = Vote::query()->find($id);

        if (! $vote) {
            abort(404, __('votes.voting_not_exist'));
        }

        if (! $vote->closed) {
            abort(200, __('votes.voting_not_archive'));
        }

        $vote->answers = VoteAnswer::query()
            ->where('vote_id', $vote->id)
            ->orderBy('id')
            ->get();

        if ($vote->answers->isEmpty()) {
            abort(200, __('votes.voting_not_answers'));
        }

        $voted = Arr::pluck($vote->answers, 'result', 'answer');
        $max = max($voted);

        arsort($voted);

        $info['voted'] = $voted;
        $info['sum'] = $vote->count > 0 ? $vote->count : 1;
        $info['max'] = $max > 0 ? $max : 1;

        return view('votes/view_history', compact('vote', 'info'));
    }

    /**
     * Создание голосования
     *
     *
     * @return View|RedirectResponse
     */
    public function create(Request $request, Validator $validator)
    {
        if ($request->isMethod('post')) {
            $question = $request->input('question');
            $description = $request->input('description');
            $answers = (array) $request->input('answers');

            $answers = array_unique(array_diff($answers, ['']));

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($question, 5, 100, ['question' => __('validator.text')])
                ->length($description, 5, 1000, ['description' => __('validator.text')], false)
                ->between(count($answers), 2, 10, ['answer' => __('votes.answer_not_enough')]);

            foreach ($answers as $answer) {
                if (utfStrlen($answer) > 50) {
                    $validator->addError(['answer' => __('votes.answer_wrong_length')]);
                    break;
                }
            }

            if ($validator->isValid()) {
                /** @var Vote $vote */
                $vote = Vote::query()->create([
                    'title'       => $question,
                    'description' => $description,
                    'created_at'  => SITETIME,
                ]);

                $prepareAnswers = [];
                foreach ($answers as $answer) {
                    $prepareAnswers[] = [
                        'vote_id' => $vote->id,
                        'answer'  => $answer,
                    ];
                }

                VoteAnswer::query()->insert($prepareAnswers);

                setFlash('success', __('votes.voting_success_created'));

                return redirect('votes/' . $vote->id);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('votes/create');
    }
}
