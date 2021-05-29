<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Flood;
use App\Models\Ignore;
use App\Models\Wall;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WallController extends Controller
{
    /**
     * Главная страница
     *
     * @param string $login
     *
     * @return View
     */
    public function index(string $login): View
    {
        $user = getUserByLogin($login);

        if (! $user) {
            abort(404, __('validator.user'));
        }

        $newWall = getUser('newwall');

        $messages = Wall::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->with('user', 'author')
            ->paginate(setting('wallpost'));

        if ($newWall && getUser('id') === $user->id) {
            $user->update([
                'newwall' => 0,
            ]);
        }

        return view('walls/index', compact('messages', 'user', 'newWall'));
    }

    /**
     * Добавление сообщения
     *
     * @param string    $login
     * @param Request   $request
     * @param Validator $validator
     * @param Flood     $flood
     *
     * @return RedirectResponse
     */
    public function create(string $login, Request $request, Validator $validator, Flood $flood): RedirectResponse
    {
        if (! getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $user = getUserByLogin($login);

        if (! $user) {
            abort(404, __('validator.user'));
        }

        if ($request->isMethod('post')) {
            $msg = $request->input('msg');

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])]);

            $ignoring = Ignore::query()
                ->where('user_id', $user->id)
                ->where('ignore_id', getUser('id'))
                ->first();

            $validator->empty($ignoring, __('ignores.you_are_ignoring'));

            if ($validator->isValid()) {
                if (getUser() && getUser('id') !== $user->id) {
                    $user->increment('newwall');
                }

                Wall::query()->create([
                    'user_id'    => $user->id,
                    'author_id'  => getUser('id'),
                    'text'       => antimat($msg),
                    'created_at' => SITETIME,
                ]);

                $flood->saveState();
                sendNotify($msg, '/walls/' . $user->login, __('index.wall_posts_login', ['login' => $user->getName()]));

                setFlash('success', __('main.record_added_success'));
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }

            return redirect('walls/' . $user->login);
        }
    }

    /**
     * Удаление сообщений
     *
     * @param string    $login
     * @param Request   $request
     * @param Validator $validator
     *
     * @return TODO |RedirectResponse
     */
    public function delete(string $login, Request $request, Validator $validator)
    {
        $id   = int($request->input('id'));
        $user = getUserByLogin($login);

        if (! $user) {
            abort(404, __('validator.user'));
        }

        $validator
            ->true($request->ajax(), __('validator.not_ajax'))
            ->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->notEmpty($id, __('validator.deletion'))
            ->notEmpty($user, __('validator.user'))
            ->true(isAdmin() || getUser('id') === $user->id, __('main.deleted_only_admins'));

        if ($validator->isValid()) {
            Wall::query()
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->delete();

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => current($validator->getErrors())
            ]);
        }
    }
}
