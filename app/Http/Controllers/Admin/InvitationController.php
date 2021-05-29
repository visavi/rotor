<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

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
     *
     * @return View
     */
    public function index(Request $request): View
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
     * @return View
     */
    public function keys(): View
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
     *
     * @return View|RedirectResponse
     */
    public function create(Request $request, Validator $validator)
    {
        if ($request->isMethod('post')) {
            $keys = int($request->input('keys'));

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
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

                return redirect('admin/invitations');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $listKeys = [1, 2, 3, 4, 5, 10, 15, 20, 30, 40, 50];

        return view('admin/invitations/create', compact('listKeys'));
    }

    /**
     * Отправка ключей пользователю
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     */
    public function send(Request $request, Validator $validator): RedirectResponse
    {
        $userkeys = int($request->input('userkeys'));

        /* @var User $user */
        $user = getUserByLogin($request->input('user'));

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
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

            return redirect('admin/invitations');
        }

        setInput($request->all());
        setFlash('danger', $validator->getErrors());

        return redirect('admin/invitations/create');
    }

    /**
     * Отправка ключей активным пользователям
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     */
    public function mail(Request $request, Validator $validator): RedirectResponse
    {
        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
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

            return redirect('admin/invitations');
        }

        setInput($request->all());
        setFlash('danger', $validator->getErrors());

        return redirect('admin/invitations/create');
    }

    /**
     * Удаление ключей
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     */
    public function delete(Request $request, Validator $validator): RedirectResponse
    {
        $page  = int($request->input('page', 1));
        $del   = intar($request->input('del'));
        $used  = $request->input('used') ? 1 : 0;

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true($del, __('validator.deletion'));

        if ($validator->isValid()) {
            Invite::query()->whereIn('id', $del)->delete();

            setFlash('success', __('admin.invitations.keys_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('admin/invitations?used=' . $used . '&page=' . $page);
    }
}
