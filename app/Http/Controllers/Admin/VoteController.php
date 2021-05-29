<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteAnswer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class VoteController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::MODER)) {
            abort(403, __('errors.forbidden'));
        }
    }

    /**
     * Главная страница
     *
     * @return View
     */
    public function index(): View
    {
        $votes = Vote::query()
            ->where('closed', 0)
            ->orderByDesc('created_at')
            ->with('topic')
            ->paginate(setting('allvotes'));

        return view('admin/votes/index', compact('votes'));
    }

    /**
     * Архив голосований
     *
     * @return View
     */
    public function history(): View
    {
        $votes = Vote::query()
            ->where('closed', 1)
            ->orderByDesc('created_at')
            ->with('topic')
            ->paginate(setting('allvotes'));

        return view('admin/votes/history', compact('votes'));
    }

    /**
     * Редактирование голосования
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id, Request $request, Validator $validator)
    {
        $vote = Vote::query()->where('id', $id)->first();

        if (! $vote) {
            abort(404, __('votes.voting_not_exist'));
        }

        if ($request->isMethod('post')) {
            $question    = $request->input('question');
            $description = $request->input('description');
            $answers     = (array) $request->input('answers');

            $answers = array_unique(array_diff($answers, ['']));

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($question, 3, 100, ['question' => __('validator.text')])
                ->length($description, 5, 1000, ['description' => __('validator.text')], false)
                ->between(count($answers), 2, 10, ['answer' => __('votes.answer_not_enough')]);

            foreach ($answers as $answer) {
                if (utfStrlen($answer) > 50) {
                    $validator->addError(['answers' => __('votes.answer_wrong_length')]);
                    break;
                }
            }

            if ($validator->isValid()) {
                $vote->update([
                    'title'       => $question,
                    'description' => $description,
                ]);

                $countAnswers = $vote->answers()->count();

                foreach ($answers as $answerId => $answer) {
                    /** @var VoteAnswer $ans */
                    $ans = $vote->answers()->firstOrNew(['id' => $answerId]);

                    if ($ans->exists) {
                        $ans->update(['answer' => $answer]);
                    } elseif ($countAnswers < 10) {
                        $ans->fill(['answer' => $answer])->save();
                        $countAnswers++;
                    }
                }

                setFlash('success', __('votes.voting_success_changed'));

                return redirect('admin/votes/edit/'.$vote->id);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $vote->getAnswers = $vote->answers->pluck('answer', 'id')->all();

        return view('admin/votes/edit', compact('vote'));
    }

    /**
     * Удаление голосования
     *
     * @param int     $id
     * @param Request $request
     *
     * @return RedirectResponse
     * @throws Throwable
     */
    public function delete(int $id, Request $request): RedirectResponse
    {
        $vote = Vote::query()->where('id', $id)->first();

        if (! $vote) {
            abort(404, __('votes.voting_not_exist'));
        }

        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        if ($request->input('_token') === csrf_token()) {
            DB::connection()->transaction(static function () use ($vote) {
                $vote->delete();
                $vote->answers()->delete();
                $vote->pollings()->delete();
            });

            setFlash('success', __('votes.voting_success_deleted'));
        } else {
            setFlash('danger', __('validator.token'));
        }

        return redirect('admin/votes');
    }

    /**
     * Открытие-закрытие голосования
     *
     * @param int     $id
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function close(int $id, Request $request): RedirectResponse
    {
        $vote = Vote::query()->where('id', $id)->first();

        if (! $vote) {
            abort(404, __('votes.voting_not_exist'));
        }

        if ($request->input('_token') === csrf_token()) {
            $status   = __('votes.voting_success_open');
            $closed = $vote->closed ^ 1;

            $vote->update([
                'closed' => $closed,
            ]);

            if ($closed) {
                $vote->pollings()->delete();
                $status = __('votes.voting_success_closed');
            }

            setFlash('success', $status);
        } else {
            setFlash('danger', __('validator.token'));
        }

        if (empty($closed)) {
            return redirect('admin/votes');
        }

        return redirect('admin/votes/history');
    }

    /**
     * Пересчет голосов
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function restatement(Request $request): RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        if ($request->input('_token') === csrf_token()) {
            restatement('votes');

            setFlash('success', __('main.success_recounted'));
        } else {
            setFlash('danger', __('validator.token'));
        }

        return redirect('admin/votes');
    }
}
