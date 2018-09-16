<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Flood;
use App\Models\Ignore;
use App\Models\User;
use App\Models\Wall;
use Illuminate\Database\Capsule\Manager as DB;

class WallController extends BaseController
{
    /**
     * Главная страница
     *
     * @param string $login
     * @return string
     */
    public function index($login): string
    {
        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort(404, 'Пользователь не найден!');
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
     * @param string $login
     * @return void
     */
    public function create($login): void
    {
        if (! getUser()) {
            abort(403, 'Для отправки сообщений необходимо авторизоваться!');
        }

        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort(404, 'Пользователь не найден!');
        }

        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $msg   = check(Request::input('msg'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($msg, 5, 1000, ['msg' => 'Слишком длинное или короткое сообщение!'])
                ->equal(Flood::isFlood(), true, ['msg' => 'Антифлуд! Разрешается отправлять сообщения раз в ' . Flood::getPeriod() . ' сек!']);

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

                DB::delete('
                        DELETE FROM walls WHERE user_id = ? AND created_at < (
                            SELECT min(created_at) FROM (
                                SELECT created_at FROM walls WHERE user_id = ? ORDER BY created_at DESC LIMIT ?
                            ) AS del
                        );',
                    [$user->id, $user->id, setting('wallmaxpost')]
                );

                setFlash('success', 'Запись успешно добавлена!');
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }

            redirect('/walls/' . $user->login);
        }
    }

    /**
     * Удаление сообщений
     *
     * @param string $login
     * @return void
     */
    public function delete($login): void
    {
        $id    = int(Request::input('id'));
        $token = check(Request::input('token'));

        $user = User::query()->where('login', $login)->first();

        $validator = new Validator();
        $validator
            ->true(Request::ajax(), 'Это не ajax запрос!')
            ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->notEmpty($id, 'Не выбрана запись для удаление!')
            ->notEmpty($user, 'Пользователь не найден!')
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
