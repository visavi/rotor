<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Ignore;
use App\Models\User;
use Illuminate\Http\Request;

class IgnoreController extends BaseController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! getUser()) {
            abort(403, __('main.not_authorized'));
        }
    }

    /**
     * Главная страница
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function index(Request $request, Validator $validator): string
    {
        $login = check($request->input('user'));

        if ($request->isMethod('post')) {
            $page  = int($request->input('page', 1));
            $token = check($request->input('token'));

            $validator->equal($token, $_SESSION['token'], __('validator.token'));

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
                redirect('/ignores?page=' . $page);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
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
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function note(int $id, Request $request, Validator $validator): string
    {
        $ignore = Ignore::query()
            ->where('user_id', getUser('id'))
            ->where('id', $id)
            ->first();

        if (! $ignore) {
            abort(404, __('main.record_not_found'));
        }

        if ($request->isMethod('post')) {
            $token = check($request->input('token'));
            $msg   = check($request->input('msg'));

            $validator->equal($token, $_SESSION['token'], ['msg' => __('validator.token')])
                ->length($msg, 0, 1000, ['msg' => __('users.note_to_big')]);

            if ($validator->isValid()) {
                $ignore->update([
                    'text' => $msg,
                ]);

                setFlash('success', __('users.note_saved_success'));
                redirect('/ignores');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('ignores/note', compact('ignore'));
    }

    /**
     * Удаление контактов
     *
     * @param Request   $request
     * @param Validator $validator
     */
    public function delete(Request $request, Validator $validator): void
    {
        $page  = int($request->input('page', 1));
        $token = check($request->input('token'));
        $del   = intar($request->input('del'));

        $validator->equal($token, $_SESSION['token'], __('validator.token'))
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

        redirect('/ignores?page=' . $page);
    }
}
