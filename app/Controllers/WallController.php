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
            abort(404, trans('validator.user'));
        }

        $total   = Wall::query()->where('user_id', $user->id)->count();
        $page    = paginate(setting('wallpost'), $total);
        $newWall = getUser('newwall');

        $messages = Wall::query()
            ->where('user_id', $user->id)
            ->offset($page->offset)
            ->limit($page->limit)
            ->orderBy('created_at', 'desc')
            ->with('user', 'author')
            ->get();

        if ($newWall && $user->id === getUser('id')) {
            $user->update([
                'newwall' => 0,
            ]);
        }

        return view('walls/index', compact('messages', 'user', 'page', 'newWall'));
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
            abort(403, trans('main.not_authorized'));
        }

        $user = getUserByLogin($login);

        if (! $user) {
            abort(404, trans('validator.user'));
        }

        if ($request->isMethod('post')) {
            $token = check($request->input('token'));
            $msg   = check($request->input('msg'));

            $validator->equal($token, $_SESSION['token'], trans('validator.token'))
                ->length($msg, 5, setting('comment_length'), ['msg' => trans('validator.text')])
                ->false($flood->isFlood(), ['msg' => trans('validator.flood', ['sec' => $flood->getPeriod()])]);

            $ignoring = Ignore::query()
                ->where('user_id', $user->id)
                ->where('ignore_id', getUser('id'))
                ->first();

            $validator->empty($ignoring, 'Вы внесены в игнор-лист получателя!');

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

                setFlash('success', 'Запись успешно добавлена!');
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

        $validator
            ->true($request->ajax(), 'Это не ajax запрос!')
            ->equal($token, $_SESSION['token'], trans('validator.token'))
            ->notEmpty($id, 'Не выбрана запись для удаление!')
            ->notEmpty($user, trans('validator.user'))
            ->true(isAdmin() || $user->id === getUser('id'), 'Записи может удалять только владелец и администрация!');

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
