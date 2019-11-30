<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteAnswer;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Http\Request;
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
     * @return string
     */
    public function index(): string
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
     * @return string
     */
    public function history(): string
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
     * @return string
     */
    public function edit(int $id, Request $request, Validator $validator): string
    {
        $vote = Vote::query()->where('id', $id)->first();

        if (! $vote) {
            abort(404, __('votes.voting_not_exist'));
        }

        if ($request->isMethod('post')) {
            $token   = check($request->input('token'));
            $title   = check($request->input('title'));
            $answers = check((array) $request->input('answers'));

            $validator->equal($token, $_SESSION['token'], __('validator.token'));

            $validator->length($title, 5, 100, ['title' => __('validator.text')]);
            $answers = array_unique(array_diff($answers, ['']));

            foreach ($answers as $answer) {
                if (utfStrlen($answer) > 50) {
                    $validator->addError(['answers' => __('votes.answer_wrong_length')]);
                    break;
                }
            }

            $validator->between(count($answers), 2, 10, ['answer' => __('votes.answer_not_enough')]);

            if ($validator->isValid()) {
                $vote->update([
                    'title' => $title,
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
                redirect('/admin/votes/edit/'.$vote->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $vote->getAnswers = $vote->answers->pluck('answer', 'id')->all();

        return view('admin/votes/edit', compact('vote'));
    }

    /**
     * Удаление голосования
     *
     * @param int     $id
     * @param Request $request
     * @return void
     * @throws Throwable
     */
    public function delete(int $id, Request $request): void
    {
        $token = check($request->input('token'));
        $vote  = Vote::query()->where('id', $id)->first();

        if (! $vote) {
            abort(404, __('votes.voting_not_exist'));
        }

        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        if ($token === $_SESSION['token']) {

            DB::connection()->transaction(static function () use ($vote) {
                $vote->delete();
                $vote->answers()->delete();
                $vote->pollings()->delete();
            });

            setFlash('success', __('votes.voting_success_deleted'));
        } else {
            setFlash('danger', __('validator.token'));
        }

        redirect('/admin/votes');
    }

    /**
     * Открытие-закрытие голосования
     *
     * @param int     $id
     * @param Request $request
     * @return void
     */
    public function close(int $id, Request $request): void
    {
        $token = check($request->input('token'));
        $vote  = Vote::query()->where('id', $id)->first();

        if (! $vote) {
            abort(404, __('votes.voting_not_exist'));
        }

        if ($token === $_SESSION['token']) {
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
            redirect('/admin/votes');
        }  else {
            redirect('/admin/votes/history');
        }
    }

    /**
     * Пересчет голосов
     *
     * @param Request $request
     * @return void
     */
    public function restatement(Request $request): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        $token = check($request->input('token'));

        if ($token === $_SESSION['token']) {
            restatement('votes');

            setFlash('success', __('main.success_recounted'));
        } else {
            setFlash('danger', __('validator.token'));
        }

        redirect('/admin/votes');
    }
}
