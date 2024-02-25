<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Ignore;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IgnoreController extends Controller
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->middleware('check.user');
    }

    /**
     * Главная страница
     *
     *
     * @return View|RedirectResponse
     */
    public function index(Request $request, Validator $validator)
    {
        $login = $request->input('user');

        if ($request->isMethod('post')) {
            $page = int($request->input('page', 1));

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'));

            $user = getUserByLogin($login);
            $validator->notEmpty($user, ['user' => __('validator.user')]);

            if ($user) {
                $validator->notEqual($user->login, getUser('login'), ['user' => __('ignores.forbidden_yourself')]);

                $totalIgnore = Ignore::query()->where('user_id', getUser('id'))->count();
                $validator->lte($totalIgnore, setting('limitignore'), __('ignores.ignore_full', ['max' => setting('limitignore')]));

                $validator->false(getUser()->isIgnore($user), ['user' => __('ignores.already_ignore')]);
                $validator->notIn($user->level, User::ADMIN_GROUPS, ['user' => __('ignores.forbidden_admins')]);
            }

            if ($validator->isValid()) {
                Ignore::query()->create([
                    'user_id'    => getUser('id'),
                    'ignore_id'  => $user->id,
                    'created_at' => SITETIME,
                ]);

                if (! $user->isIgnore(getUser())) {
                    $text = textNotice('ignore', ['login' => getUser('login')]);
                    $user->sendMessage(null, $text);
                }

                setFlash('success', __('ignores.success_added'));

                return redirect('ignores?page=' . $page);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $ignores = Ignore::query()
            ->where('user_id', getUser('id'))
            ->orderByDesc('created_at')
            ->with('ignoring')
            ->paginate(setting('ignorlist'));

        return view('ignores/index', compact('ignores', 'login'));
    }

    /**
     * Заметка для пользователя
     *
     *
     * @return View|RedirectResponse
     */
    public function note(int $id, Request $request, Validator $validator)
    {
        $ignore = Ignore::query()
            ->where('user_id', getUser('id'))
            ->where('id', $id)
            ->first();

        if (! $ignore) {
            abort(404, __('main.record_not_found'));
        }

        if ($request->isMethod('post')) {
            $msg = $request->input('msg');

            $validator->equal($request->input('_token'), csrf_token(), ['msg' => __('validator.token')])
                ->length($msg, 0, 1000, ['msg' => __('users.note_to_big')]);

            if ($validator->isValid()) {
                $ignore->update([
                    'text' => $msg,
                ]);

                setFlash('success', __('users.note_saved_success'));

                return redirect('ignores');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('ignores/note', compact('ignore'));
    }

    /**
     * Удаление контактов
     */
    public function delete(Request $request, Validator $validator): RedirectResponse
    {
        $page = int($request->input('page', 1));
        $del = intar($request->input('del'));

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true($del, __('validator.deletion'));

        if ($validator->isValid()) {
            Ignore::query()
                ->where('user_id', getUser('id'))
                ->whereIn('id', $del)
                ->delete();

            setFlash('success', __('main.records_deleted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('ignores?page=' . $page);
    }
}
