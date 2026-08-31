<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Banhist;
use App\Models\BlackList;
use App\Models\Comment;
use App\Models\User;
use App\Services\UserService;
use App\Support\Registry;
use App\Support\Restatement;
use App\Support\Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $users = User::query()
            ->orderByDesc('created_at')
            ->paginate(setting('userlist'));

        return view('admin/users/index', compact('users'));
    }

    /**
     * Поиск пользователей
     */
    public function search(Request $request): View
    {
        $q = check($request->input('q'));

        $users = User::query()
            ->when(
                $q === '1',
                fn ($query) => $query->whereRaw("login RLIKE '^[-0-9]'"),
                fn ($query) => $query->where('login', 'like', $q . '%')
            )
            ->orderByDesc('point')
            ->paginate(setting('usersearch'))
            ->appends(['q' => $q]);

        return view('admin/users/search', compact('users'));
    }

    /**
     * Редактирование пользователя
     */
    public function edit(Request $request, Validator $validator): View|RedirectResponse
    {
        $user = getUserByLogin($request->input('user'));

        if (! $user) {
            abort(404, __('validator.user'));
        }

        $allThemes = getAvailableThemes();
        $adminGroups = User::ADMIN_GROUPS;

        $allGroups = [];
        foreach (User::ALL_GROUPS as $level) {
            $allGroups[$level] = User::getLevelByKey($level);
        }

        if ($request->isMethod('post')) {
            $level = $request->input('level');
            $password = $request->input('password');
            $email = $request->input('email');
            $name = $request->input('name');
            $country = $request->input('country');
            $city = $request->input('city');
            $phone = preg_replace('/[^\d+]/', '', $request->input('phone') ?? '');
            $site = $request->input('site');
            $birthday = $request->input('birthday');
            $point = int($request->input('point'));
            $money = int($request->input('money'));
            $status = $request->input('status');
            $posrating = int($request->input('posrating'));
            $negrating = int($request->input('negrating'));
            $themes = $request->input('themes');
            $gender = $request->input('gender') === User::MALE ? User::MALE : User::FEMALE;
            $info = $request->input('info');

            $validator
                ->in($level, User::ALL_GROUPS, ['level' => __('users.user_level_invalid')])
                ->length($password, 6, 20, __('users.password_length_requirements'), false)
                ->email($email, ['email' => __('validator.email')], UserService::isEmailRequired())
                ->phone($phone, ['phone' => __('validator.phone')], false)
                ->url($site, ['site' => __('validator.url')], false)
                ->regex($birthday, '#^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$#', ['birthday' => __('validator.date')], false)
                ->length($status, 3, 25, ['status' => __('users.status_short_or_long')], false)
                ->true(in_array($themes, $allThemes, true) || empty($themes), ['themes' => __('users.theme_not_installed')])
                ->length($info, 0, 1000, ['info' => __('users.info_yourself_long')]);

            // Для админа обязательность полей не проверяется
            foreach (Registry::$onProfileValidate as $handler) {
                $handler($user, $request, $validator, false);
            }

            if ($validator->isValid()) {
                if ($password) {
                    $text = __('users.user_new_password', ['password' => $password]);
                    $password = Hash::make($password);
                } else {
                    $text = null;
                    $password = $user->password;
                }

                $name = Str::substr($name, 0, 20);
                $country = Str::substr($country, 0, 30);
                $city = Str::substr($city, 0, 50);
                $rating = $posrating - $negrating;

                $user->update([
                    'password'  => $password,
                    'level'     => $level,
                    'email'     => $email ?: null,
                    'name'      => $name,
                    'country'   => $country,
                    'city'      => $city,
                    'phone'     => $phone,
                    'site'      => $site,
                    'birthday'  => $birthday,
                    'point'     => $point,
                    'money'     => $money,
                    'status'    => $status,
                    'rating'    => $rating,
                    'posrating' => $posrating,
                    'negrating' => $negrating,
                    'themes'    => $themes,
                    'gender'    => $gender,
                    'info'      => $info,
                ]);

                foreach (Registry::$onProfileSave as $handler) {
                    $handler($user, $request);
                }

                clearCache('status');

                return redirect('admin/users/edit?user=' . $user->login)
                    ->with('success', array_filter([__('users.user_success_changed'), $text]));
            }

            return redirect('admin/users/edit?user=' . $user->login)
                ->withInput()
                ->withErrors($validator->getErrors());
        }

        $banhist = Banhist::query()
            ->where('user_id', $user->id)
            ->whereIn('type', ['ban', 'change'])
            ->orderByDesc('created_at')
            ->first();

        return view('admin/users/edit', compact('user', 'banhist', 'allThemes', 'allGroups', 'adminGroups'));
    }

    /**
     * Удаление пользователя
     */
    public function delete(Request $request, Validator $validator): View|RedirectResponse
    {
        $user = getUserByLogin($request->input('user'));

        if (! $user) {
            abort(404, __('validator.user'));
        }

        if ($request->isMethod('post')) {
            $loginblack = $request->boolean('loginblack');
            $mailblack = $request->boolean('mailblack');
            $delcomments = $request->boolean('delcomments');

            $validator->notIn($user->level, User::ADMIN_GROUPS, __('users.admins_remove_forbidden'));

            if ($validator->isValid()) {
                if ($loginblack) {
                    BlackList::query()->firstOrCreate(
                        ['type' => 'login', 'value' => $user->login],
                        ['user_id' => getUser('id')],
                    );
                }

                if ($mailblack && $user->email) {
                    BlackList::query()->firstOrCreate(
                        ['type' => 'email', 'value' => $user->email],
                        ['user_id' => getUser('id')],
                    );
                }

                foreach (Registry::$onAdminDeleteUser as $handler) {
                    $handler($user, $request);
                }

                // Удаление комментариев
                if ($delcomments) {
                    $comments = Comment::query()
                        ->where('user_id', $user->id)
                        ->get();

                    $comments->each(static function (Comment $comment) {
                        $comment->delete();
                    });

                    if ($comments->isNotEmpty()) {
                        Restatement::run(array_keys(Restatement::$handlers));
                    }
                }

                $user->delete();

                return redirect('admin/users')
                    ->with('success', __('users.user_success_deleted'));
            }

            return redirect('admin/users/delete?user=' . $user->login)
                ->withInput()
                ->withErrors($validator->getErrors());
        }

        return view('admin/users/delete', compact('user'));
    }
}
