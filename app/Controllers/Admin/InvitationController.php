<?php

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Http\Request;

class InvitationController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::MODER)) {
            abort(403, 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     *
     * @param Request $request
     * @return string
     */
    public function index(Request $request): string
    {
        $used = $request->input('used') ? 1 : 0;

        $total = Invite::query()->where('used', $used)->count();
        $page = paginate(setting('listinvite'), $total);

        $invites = Invite::query()
            ->where('used', $used)
            ->orderBy('created_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('user', 'inviteUser')
            ->get();

        return view('admin/invitations/index', compact('invites', 'page', 'used'));
    }

    /**
     * Список ключей
     *
     * @return string
     */
    public function keys(): string
    {
        $keys = Invite::query()
            ->where('user_id', getUser('id'))
            ->where('used', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin/invitations/keys', compact('keys'));
    }

    /**
     * Создание ключей
     *
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function create(Request $request): string
    {
        if ($request->isMethod('post')) {
            $token  = check($request->input('token'));
            $keys   = int($request->input('keys'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->notEmpty($keys, ['keys' => 'Не указано число ключей!']);

            if ($validator->isValid()) {

                $newKeys = [];

                for ($i = 0; $i < $keys; $i++) {
                    $newKeys[] = [
                        'hash'       => str_random(random_int(12, 15)),
                        'user_id'    => getUser('id'),
                        'created_at' => SITETIME,
                    ];
                }

                Invite::query()->insert($newKeys);

                setFlash('success', 'Ключи успешно созданы!');
                redirect('/admin/invitations');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $listKeys = [1, 2, 3, 4, 5, 10, 15, 20, 30, 40, 50];

        return view('admin/invitations/create', compact('listKeys'));
    }

    /**
     * Отправка ключей пользователю
     *
     * @param Request $request
     * @return void
     * @throws \Exception
     */
    public function send(Request $request): void
    {
        $token    = check($request->input('token'));
        $login    = check($request->input('user'));
        $userkeys = int($request->input('userkeys'));

        /* @var User $user */
        $user = getUserByLogin($login);

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->notEmpty($user, ['user' => 'Пользователя с данным логином не существует!'])
            ->notEmpty($userkeys, ['userkeys' => 'Не указано число ключей!']);

        if ($validator->isValid()) {

            $newKeys  = [];
            $listKeys = [];

            for ($i = 0; $i < $userkeys; $i++) {

                $key = str_random(random_int(12, 15));

                $listKeys[] = $key;

                $newKeys[] = [
                    'hash'       => $key,
                    'user_id'    => $user->id,
                    'created_at' => SITETIME,
                ];
            }

            Invite::query()->insert($newKeys);

            $text = 'Вы получили пригласительные ключи в количестве ' . \count($listKeys) . 'шт.'.PHP_EOL.'Список ключей: '.implode(', ', $listKeys).PHP_EOL.'С помощью этих ключей вы можете пригласить ваших друзей на наш сайт!';
            $user->sendMessage(null, $text);

            setFlash('success', 'Ключи успешно отправлены!');
            redirect('/admin/invitations');
        } else {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
            redirect('/admin/invitations/create');
        }
    }

    /**
     * Отправка ключей активным пользователям
     *
     * @param Request $request
     * @return void
     * @throws \Exception
     */
    public function mail(Request $request): void
    {
        $token = check($request->input('token'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true(isAdmin(User::BOSS), 'Рассылать ключи может только владелец');

        $users = User::query()->where('updated_at', '>', strtotime('-1 week', SITETIME))->get();

        $users = $users->filter(function ($value, $key) {
            return $value->id !== getUser('id');
        });

        $validator->false($users->isEmpty(), 'Отсутствуют получатели ключей!');

        if ($validator->isValid()) {

            /** @var User $user */
            foreach ($users as $user) {
                $key = str_random(random_int(12, 15));

                Invite::query()->create([
                    'hash'       => $key,
                    'user_id'    => $user->id,
                    'created_at' => SITETIME,
                ]);

                $text = 'Поздравляем! Вы получили пригласительный ключ' . PHP_EOL . 'Ваш ключ: ' . $key . PHP_EOL.'С помощью этого ключа вы можете пригласить вашего друга на наш сайт!';
                $user->sendMessage(null, $text);
            }

            setFlash('success', 'Ключи успешно отправлены! ('. $users->count() .')');
            redirect('/admin/invitations');
        } else {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
            redirect('/admin/invitations/create');
        }
    }

    /**
     * Удаление ключей
     *
     * @param Request $request
     * @return void
     */
    public function delete(Request $request): void
    {
        $page  = int($request->input('page', 1));
        $token = check($request->input('token'));
        $del   = intar($request->input('del'));
        $used  = $request->input('used') ? 1 : 0;

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true($del, 'Отсутствуют выбранные записи для удаления!');

        if ($validator->isValid()) {
            Invite::query()->whereIn('id', $del)->delete();

            setFlash('success', 'Выбранные ключи успешно удалены!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/invitations?used=' . $used . '&page=' . $page);
    }
}
