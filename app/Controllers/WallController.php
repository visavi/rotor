<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validation;
use App\Models\Flood;
use App\Models\Ignore;
use App\Models\User;
use App\Models\Wall;
use Illuminate\Database\Capsule\Manager as DB;

class WallController extends BaseController
{
    /**
     * Главная страница
     */
    public function index($login)
    {
        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort('default', 'Пользователь не найден!');
        }

        $total   = Wall::query()->where('user_id', $user->id)->count();
        $page    = paginate(setting('wallpost'), $total);
        $newWall = getUser('newwall');

        $messages = Wall::query()
            ->where('user_id', $user->id)
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('created_at', 'desc')
            ->with('user', 'author')
            ->get();

        if ($newWall && $user->id == getUser('id')) {
            $user->update([
                'newwall' => 0,
            ]);
        }

        return view('wall/index', compact('messages', 'user', 'page', 'newWall'));
    }

    /**
     * Добавление сообщения
     */
    public function create($login)
    {
        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort('default', 'Пользователь не найден!');
        }

        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $msg   = check(Request::input('msg'));

            if (! getUser()) {
                abort(403, 'Для отправки сообщений необходимо авторизоваться!');
            }

            $validation = new Validation();
            $validation->addRule('equal', [$token, $_SESSION['token']], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->addRule('bool', $user, 'Ошибка! Пользователь не найден!')
                ->addRule('string', $msg, 'Ошибка! Слишком длинное или короткое сообщение!', true, 5, 1000)
                ->addRule('equal', [Flood::isFlood(), true], 'Антифлуд! Разрешается отправлять сообщения раз в ' . Flood::getPeriod() . ' сек!');

            $ignoring = Ignore::query()
                ->where('user_id', $user->id)
                ->where('ignore_id', getUser('id'))
                ->first();

            $validation->addRule('empty', $ignoring, 'Вы внесены в игнор-лист получателя!');

            if ($validation->run()) {

                if ($user->id != getUser('id')) {
                    $user->increment('newwall');
                }

                Wall::query()->create([
                    'user_id'    => $user->id,
                    'author_id'  => getUser('id'),
                    'text'       => antimat($msg),
                    'created_at' => SITETIME,
                ]);

                DB::delete('
                        DELETE FROM wall WHERE user_id = ? AND created_at < (
                            SELECT min(created_at) FROM (
                                SELECT created_at FROM wall WHERE user_id = ? ORDER BY created_at DESC LIMIT ?
                            ) AS del
                        );',
                    [$user->id, $user->id, setting('wallmaxpost')]
                );

                setFlash('success', 'Запись успешно добавлена!');
            } else {
                setInput(Request::all());
                setFlash('danger', $validation->getErrors());
            }

            redirect('/wall/' . $user->login);
        }
    }

    /**
     * Удаление сообщений
     */
    public function delete($login)
    {
        $id    = abs(intval(Request::input('id')));
        $token = check(Request::input('token'));

        $user = User::query()->where('login', $login)->first();

        $validation = new Validation();
        $validation
            ->addRule('bool', Request::ajax(), 'Это не ajax запрос!')
            ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            ->addRule('not_empty', $id, 'Не выбрана запись для удаление!')
            ->addRule('not_empty', $user, 'Пользователь не найден!')
            ->addRule('bool', (isAdmin() || $user->id == getUser('id')), 'Записи может удалять только владелец и администрация!');

        if ($validation->run()) {

            Wall::query()
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->delete();

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => current($validation->getErrors())
            ]);
        }
    }
}
