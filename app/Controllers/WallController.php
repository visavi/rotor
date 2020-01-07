<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Flood;
use App\Models\Ignore;
use App\Models\Wall;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Http\Request;

class WallController extends BaseController
{
    /**
     * Главная страница
     *
     * @param string $login
     * @return string
     */
    public function index(string $login): string
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

        if ($newWall && $user->id === getUser('id')) {
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
     * @return void
     */
    public function create($login, Request $request, Validator $validator, Flood $flood): void
    {
        if (! getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $user = getUserByLogin($login);

        if (! $user) {
            abort(404, __('validator.user'));
        }

        if ($request->isMethod('post')) {
            $token = check($request->input('token'));
            $msg   = check($request->input('msg'));

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])]);

            $ignoring = Ignore::query()
                ->where('user_id', $user->id)
                ->where('ignore_id', getUser('id'))
                ->first();

            $validator->empty($ignoring, __('ignores.you_are_ignoring'));

            if ($validator->isValid()) {
                if ($user->id !== getUser('id')) {
                    $user->increment('newwall');
                }

                Wall::query()->create([
                    'user_id'    => $user->id,
                    'author_id'  => getUser('id'),
                    'text'       => antimat($msg),
                    'created_at' => SITETIME,
                ]);

                DB::connection()->delete('
                        DELETE FROM walls WHERE user_id = ? AND created_at < (
                            SELECT min(created_at) FROM (
                                SELECT created_at FROM walls WHERE user_id = ? ORDER BY created_at DESC LIMIT ?
                            ) AS del
                        );',
                    [$user->id, $user->id, setting('wallmaxpost')]
                );

                $flood->saveState();
                sendNotify($msg, '/walls/' . $user->login, __('index.wall_posts_login', ['login' => $user->login]));

                setFlash('success', __('main.record_added_success'));
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }

            redirect('/walls/' . $user->login);
        }
    }

    /**
     * Удаление сообщений
     *
     * @param string    $login
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function delete(string $login, Request $request, Validator $validator): void
    {
        $id    = int($request->input('id'));
        $token = check($request->input('token'));

        $user = getUserByLogin($login);

        if (! $user) {
            abort(404, __('validator.user'));
        }

        $validator
            ->true($request->ajax(), __('validator.not_ajax'))
            ->equal($token, $_SESSION['token'], __('validator.token'))
            ->notEmpty($id, __('validator.deletion'))
            ->notEmpty($user, __('validator.user'))
            ->true(isAdmin() || $user->id === getUser('id'), __('main.deleted_only_admins'));

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
