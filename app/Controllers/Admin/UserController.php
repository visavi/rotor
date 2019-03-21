<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Banhist;
use App\Models\BlackList;
use App\Models\Comment;
use App\Models\File;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $total = User::query()->count();
        $page = paginate(setting('userlist'), $total);

        $users = User::query()
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->get();

        return view('admin/users/index', compact('users', 'page'));
    }

    /**
     * Поиск пользователей
     *
     * @param Request $request
     * @return string
     */
    public function search(Request $request): string
    {
        $q = check($request->input('q'));

        $search = $q === '1' ? "RLIKE '^[-0-9]'" : "LIKE '$q%'";

        $total = User::query()->whereRaw('login ' . $search)->count();
        $page = paginate(setting('usersearch'), $total);

        $users = User::query()
            ->whereRaw('login ' . $search)
            ->offset($page->offset)
            ->limit($page->limit)
            ->orderBy('point', 'desc')
            ->get();

        return view('admin/users/search', compact('users', 'page'));
    }

    /**
     * Редактирование пользователя
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function edit(Request $request, Validator $validator): string
    {
        $login = check($request->input('user'));

        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort(404, trans('validator.user'));
        }

        $allThemes   = array_map('basename', glob(HOME . '/themes/*', GLOB_ONLYDIR));
        $adminGroups = User::ADMIN_GROUPS;

        $allGroups   = [];
        foreach (User::ALL_GROUPS as $level) {
            $allGroups[$level] = User::getLevelByKey($level);
        }

        if ($request->isMethod('post')) {

            $token     = check($request->input('token'));
            $level     = check($request->input('level'));
            $password  = check($request->input('password'));
            $email     = check($request->input('email'));
            $name      = check($request->input('name'));
            $country   = check($request->input('country'));
            $city      = check($request->input('city'));
            $site      = check($request->input('site'));
            $birthday  = check($request->input('birthday'));
            $icq       = preg_replace('/\D/', '', $request->input('icq'));
            $skype     = check(strtolower($request->input('skype')));
            $point     = int($request->input('point'));
            $money     = int($request->input('money'));
            $status    = check($request->input('status'));
            $posrating = int($request->input('posrating'));
            $negrating = int($request->input('negrating'));
            $themes    = check($request->input('themes'));
            $gender    = $request->input('gender') === 'male' ? 'male' : 'female';
            $info      = check($request->input('info'));
            $created   = check($request->input('created'));

            $validator->equal($token, $_SESSION['token'], trans('validator.token'))
                ->in($level, User::ALL_GROUPS, ['level' => 'Недопустимый уровень пользователя!'])
                ->length($password, 6, 20, 'Слишком длинный или короткий новый пароль!', false)
                ->email($email, ['email' => 'Вы ввели неверный адрес email, необходим формат name@site.domen!'])
                ->regex($site, '#^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', ['site' => 'Недопустимый адрес сайта, необходим формата http://my_site.domen!'], false)
                ->regex($birthday, '#^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$#', ['birthday' => 'Недопустимая дата рождения, необходим формат дд.мм.гггг!'], false)
                ->regex($created, '#^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$#', ['created' => 'Недопустимая дата регистрации, необходим формат (дд.мм.гггг)!'], false)
                ->regex($icq, '#^[0-9]{5,10}$#', ['icq' => 'Недопустимый формат ICQ, только цифры от 5 до 10 символов!'], false)
                ->regex($skype, '#^[a-z]{1}[0-9a-z\_\.\-]{5,31}$#', ['skype' => 'Недопустимый формат Skype, только латинские символы от 6 до 32!'], false)
                ->length($status, 3, 20, ['status' => 'Слишком длинный или короткий статус!'], false)
                ->true(\in_array($themes, $allThemes, true) || empty($themes), ['themes' => 'Данная тема не установлена на сайте!'])
                ->length($info, 0, 1000, ['info' => 'Слишком большая информация о себе, не более 1000 символов!']);

            if ($validator->isValid()) {

                if ($password) {
                    $text     = '<br>Новый пароль пользователя: ' . $password;
                    $password = password_hash($password, PASSWORD_BCRYPT);
                } else {
                    $text     = null;
                    $password = $user->password;
                }

                $name    = utfSubstr($name, 0, 20);
                $country = utfSubstr($country, 0, 30);
                $city    = utfSubstr($city, 0, 50);
                $rating  = $posrating - $negrating;

                $user->update([
                    'password'   => $password,
                    'level'      => $level,
                    'email'      => $email,
                    'name'       => $name,
                    'country'    => $country,
                    'city'       => $city,
                    'site'       => $site,
                    'birthday'   => $birthday,
                    'icq'        => $icq,
                    'skype'      => $skype,
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

                $user->saveStatus();

                setFlash('success', 'Данные пользователя успешно изменены!' . $text);
                redirect('/admin/users/edit?user=' . $user->login);

            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $banhist = Banhist::query()
            ->where('user_id', $user->id)
            ->whereIn('type', ['ban', 'change'])
            ->orderBy('created_at', 'desc')
            ->first();

        return view('admin/users/edit', compact('user', 'banhist', 'allThemes', 'allGroups', 'adminGroups'));
    }

    /**
     * Удаление пользователя
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     * @throws \Exception
     */
    public function delete(Request $request, Validator $validator): string
    {
        $login = check($request->input('user'));

        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort(404, trans('validator.user'));
        }

        if ($request->isMethod('post')) {

            $token       = check($request->input('token'));
            $loginblack  = empty($request->input('loginblack')) ? 0 : 1;
            $mailblack   = empty($request->input('mailblack')) ? 0 : 1;
            $deltopics   = empty($request->input('deltopics')) ? 0 : 1;
            $delposts    = empty($request->input('delposts')) ? 0 : 1;
            $delcomments = empty($request->input('delcomments')) ? 0 : 1;
            $delimages   = empty($request->input('delimages')) ? 0 : 1;

            $validator->equal($token, $_SESSION['token'], trans('validator.token'))
                ->notIn($user->level, User::ADMIN_GROUPS, 'Запрещено удалять пользователей из группы администраторов!');

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
                    $topics = Topic::query()->where('user_id', $user->id)->pluck('id')->all();
                    $posts  = Post::query()->whereIn('topic_id', $topics)->pluck('id')->all();

                    // Удаление загруженных файлов
                    if ($posts) {
                        $files = File::query()
                            ->where('relate_type', Post::class)
                            ->whereIn('relate_id', $posts)
                            ->get();

                        if ($files->isNotEmpty()) {
                            foreach ($files as $file) {
                                deleteFile(HOME . $file->hash);
                                $file->delete();
                            }
                        }
                    }

                    Post::query()->whereIn('topic_id', $topics)->delete();
                    Topic::query()->where('user_id', $user->id)->delete();
                    restatement('forums');
                }

                // Удаление постов форума
                if ($delposts) {
                    $posts  = Post::query()->where('user_id', $user->id)->pluck('id')->all();

                    // Удаление загруженных файлов
                    if ($posts) {
                        $files = File::query()
                            ->where('relate_type', Post::class)
                            ->whereIn('relate_id', $posts)
                            ->get();

                        if ($files->isNotEmpty()) {
                            foreach ($files as $file) {
                                deleteFile(HOME . $file->hash);
                                $file->delete();
                            }
                        }
                    }

                    Post::query()->where('user_id', $user->id)->delete();
                    restatement('forums');
                }

                // Удаление комментариев
                if ($delcomments) {

                    $deletes = Comment::query()
                        ->where('user_id', $user->id)
                        ->delete();

                    if ($deletes) {
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

                setFlash('success', 'Пользователь успешно удален!');
                redirect('/admin/users');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/users/delete', compact('user'));
    }
}
