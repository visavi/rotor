<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Banhist;
use App\Models\BlackList;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use App\Models\UserField;
use Illuminate\Database\Query\JoinClause;
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

        $search = $q === '1' ? "RLIKE '^[-0-9]'" : "LIKE '$q%'";

        $users = User::query()
            ->whereRaw('login ' . $search)
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

        $allThemes = array_map('basename', glob(public_path('themes/*'), GLOB_ONLYDIR));
        $adminGroups = User::ADMIN_GROUPS;

        $allGroups = [];
        foreach (User::ALL_GROUPS as $level) {
            $allGroups[$level] = User::getLevelByKey($level);
        }

        $fields = UserField::query()
            ->select('uf.*', 'ud.value')
            ->from('user_fields as uf')
            ->leftJoin('user_data as ud', static function (JoinClause $join) use ($user) {
                $join->on('uf.id', 'ud.field_id')
                    ->where('ud.user_id', $user->id);
            })
            ->orderBy('uf.sort')
            ->get();

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
            $created = $request->input('created');

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->in($level, User::ALL_GROUPS, ['level' => __('users.user_level_invalid')])
                ->length($password, 6, 20, __('users.password_length_requirements'), false)
                ->email($email, ['email' => __('validator.email')])
                ->phone($phone, ['phone' => __('validator.phone')], false)
                ->url($site, ['site' => __('validator.url')], false)
                ->regex($birthday, '#^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$#', ['birthday' => __('validator.date')], false)
                ->regex($created, '#^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$#', ['created' => __('validator.date')], false)
                ->length($status, 3, 25, ['status' => __('users.status_short_or_long')], false)
                ->true(in_array($themes, $allThemes, true) || empty($themes), ['themes' => __('users.theme_not_installed')])
                ->length($info, 0, 1000, ['info' => __('users.info_yourself_long')]);

            foreach ($fields as $field) {
                $validator->length(
                    $request->input('field' . $field->id),
                    $field->min,
                    $field->max,
                    ['field' . $field->id => __('validator.text')],
                    false // Для админа поля не обязательны
                );
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
                    'password'   => $password,
                    'level'      => $level,
                    'email'      => $email,
                    'name'       => $name,
                    'country'    => $country,
                    'city'       => $city,
                    'phone'      => $phone,
                    'site'       => $site,
                    'birthday'   => $birthday,
                    'point'      => $point,
                    'money'      => $money,
                    'status'     => $status,
                    'rating'     => $rating,
                    'posrating'  => $posrating,
                    'negrating'  => $negrating,
                    'themes'     => $themes,
                    'gender'     => $gender,
                    'info'       => $info,
                    'created_at' => strtotime($created),
                ]);

                foreach ($fields as $field) {
                    $user->data()
                        ->updateOrCreate([
                            'field_id' => $field->id,
                        ], [
                            'value' => $request->input('field' . $field->id),
                        ]);
                }

                clearCache('status');
                setFlash('success', [__('users.user_success_changed'), $text]);

                return redirect('admin/users/edit?user=' . $user->login);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $banhist = Banhist::query()
            ->where('user_id', $user->id)
            ->whereIn('type', ['ban', 'change'])
            ->orderByDesc('created_at')
            ->first();

        return view('admin/users/edit', compact('user', 'banhist', 'allThemes', 'allGroups', 'adminGroups', 'fields'));
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
            $loginblack = empty($request->input('loginblack')) ? 0 : 1;
            $mailblack = empty($request->input('mailblack')) ? 0 : 1;
            $deltopics = empty($request->input('deltopics')) ? 0 : 1;
            $delposts = empty($request->input('delposts')) ? 0 : 1;
            $delcomments = empty($request->input('delcomments')) ? 0 : 1;
            $delimages = empty($request->input('delimages')) ? 0 : 1;

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->notIn($user->level, User::ADMIN_GROUPS, __('users.admins_remove_forbidden'));

            if ($validator->isValid()) {
                if ($loginblack) {
                    $duplicate = BlackList::query()->where('type', 'login')->where('value', $user->login)->first();
                    if (! $duplicate) {
                        BlackList::query()->create([
                            'type'       => 'login',
                            'value'      => $user->login,
                            'user_id'    => getUser('id'),
                            'created_at' => SITETIME,
                        ]);
                    }
                }

                if ($mailblack) {
                    $duplicate = BlackList::query()->where('type', 'email')->where('value', $user->email)->first();
                    if (! $duplicate) {
                        BlackList::query()->create([
                            'type'       => 'email',
                            'value'      => $user->email,
                            'user_id'    => getUser('id'),
                            'created_at' => SITETIME,
                        ]);
                    }
                }

                // Удаление тем форума
                if ($deltopics) {
                    $topics = Topic::query()->where('user_id', $user->id)->get();

                    $topics->each(static function (Topic $topic) {
                        $topic->delete();
                    });

                    if ($topics->isNotEmpty()) {
                        restatement('forums');
                    }
                }

                // Удаление постов форума
                if ($delposts) {
                    $posts = Post::query()->where('user_id', $user->id)->get();

                    $posts->each(static function (Post $post) {
                        $post->delete();
                    });

                    if ($posts->isNotEmpty()) {
                        restatement('forums');
                    }
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
                        restatement('blogs');
                        restatement('loads');
                        restatement('news');
                        restatement('photos');
                        restatement('offers');
                    }
                }

                // Удаление фотографий в галерее
                if ($delimages) {
                    $user->deleteAlbum();
                }

                $user->delete();

                setFlash('success', __('users.user_success_deleted'));

                return redirect('admin/users');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/users/delete', compact('user'));
    }
}
