<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvitationController extends AdminController
{
    /**
     * Конструктор
     */
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
     * @param Request $request
     * @return string
     */
    public function index(Request $request): string
    {
        $used = $request->input('used') ? 1 : 0;

        $invites = Invite::query()
            ->where('used', $used)
            ->orderByDesc('created_at')
            ->with('user', 'inviteUser')
            ->paginate(setting('listinvite'))
            ->appends(['used' => $used]);

        return view('admin/invitations/index', compact('invites', 'used'));
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
            ->orderByDesc('created_at')
            ->get();

        return view('admin/invitations/keys', compact('keys'));
    }

    /**
     * Создание ключей
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function create(Request $request, Validator $validator): string
    {
        if ($request->isMethod('post')) {
            $token  = check($request->input('token'));
            $keys   = int($request->input('keys'));

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
                ->notEmpty($keys, ['keys' => __('admin.invitations.keys_not_amount')]);

            if ($validator->isValid()) {
                $newKeys = [];

                for ($i = 0; $i < $keys; $i++) {
                    $newKeys[] = [
                        'hash'       => Str::random(),
                        'user_id'    => getUser('id'),
                        'created_at' => SITETIME,
                    ];
                }

                Invite::query()->insert($newKeys);

                setFlash('success', __('admin.invitations.keys_success_created'));
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
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function send(Request $request, Validator $validator): void
    {
        $token    = check($request->input('token'));
        $login    = check($request->input('user'));
        $userkeys = int($request->input('userkeys'));

        /* @var User $user */
        $user = getUserByLogin($login);

        $validator->equal($token, $_SESSION['token'], __('validator.token'))
            ->notEmpty($user, ['user' => __('validator.user')])
            ->notEmpty($userkeys, ['userkeys' => __('admin.invitations.keys_not_amount')]);

        if ($validator->isValid()) {
            $newKeys  = [];
            $listKeys = [];

            for ($i = 0; $i < $userkeys; $i++) {
                $key = Str::random();

                $listKeys[] = $key;

                $newKeys[] = [
                    'hash'       => $key,
                    'user_id'    => $user->id,
                    'created_at' => SITETIME,
                ];
            }

            Invite::query()->insert($newKeys);

            $text = textNotice('invite', ['key' => implode(', ', $listKeys)]);
            $user->sendMessage(null, $text);

            setFlash('success', __('admin.invitations.keys_success_sent'));
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
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function mail(Request $request, Validator $validator): void
    {
        $token = check($request->input('token'));

        $validator->equal($token, $_SESSION['token'], __('validator.token'))
            ->true(isAdmin(User::BOSS), __('main.page_only_owner'));

        $users = User::query()->where('updated_at', '>', strtotime('-1 week', SITETIME))->get();

        $users = $users->filter(static function ($value, $key) {
            return $value->id !== getUser('id');
        });

        $validator->false($users->isEmpty(), __('admin.invitations.keys_empty_recipients'));

        if ($validator->isValid()) {
            /** @var User $user */
            foreach ($users as $user) {
                $key = Str::random(mt_rand(12, 15));

                Invite::query()->create([
                    'hash'       => $key,
                    'user_id'    => $user->id,
                    'created_at' => SITETIME,
                ]);

                $text = textNotice('invite', compact('key'));
                $user->sendMessage(null, $text);
            }

            setFlash('success', __('admin.invitations.keys_success_sent') . ' (' . $users->count() . ')');
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
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function delete(Request $request, Validator $validator): void
    {
        $page  = int($request->input('page', 1));
        $token = check($request->input('token'));
        $del   = intar($request->input('del'));
        $used  = $request->input('used') ? 1 : 0;

        $validator->equal($token, $_SESSION['token'], __('validator.token'))
            ->true($del, __('validator.deletion'));

        if ($validator->isValid()) {
            Invite::query()->whereIn('id', $del)->delete();

            setFlash('success', __('admin.invitations.keys_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/invitations?used=' . $used . '&page=' . $page);
    }
}
