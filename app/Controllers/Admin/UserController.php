<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Banhist;
use App\Models\BlackList;
use App\Models\Comment;
use App\Models\File;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;

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
     */
    public function index()
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
     */
    public function search()
    {
        $q = check(Request::input('q'));

        $search = $q == 1 ? "RLIKE '^[-0-9]'" : "LIKE '$q%'";

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
     */
    public function edit()
    {
        $login = check(Request::input('user'));

        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort(404, 'Пользователь не найден!');
        }

        $allThemes   = array_map('basename', glob(HOME."/themes/*", GLOB_ONLYDIR));
        $allGroups   = array_reverse(User::ALL_GROUPS);
        $adminGroups = User::ADMIN_GROUPS;

        if (Request::isMethod('post')) {

            $token     = check(Request::input('token'));
            $level     = check(Request::input('level'));
            $password  = check(Request::input('password'));
            $email     = check(Request::input('email'));
            $name      = check(Request::input('name'));
            $country   = check(Request::input('country'));
            $city      = check(Request::input('city'));
            $site      = check(Request::input('site'));
            $birthday  = check(Request::input('birthday'));
            $icq       = check(str_replace('-', '', Request::input('icq')));
            $skype     = check(strtolower(Request::input('skype')));
            $point     = int(Request::input('point'));
            $money     = int(Request::input('money'));
            $status    = check(Request::input('status'));
            $posrating = int(Request::input('posrating'));
            $negrating = int(Request::input('negrating'));
            $themes    = check(Request::input('themes'));
            $gender    = Request::input('gender') === 'male' ? 'male' : 'female';
            $info      = check(Request::input('info'));
            $created   = check(Request::input('created'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->in($level, User::ALL_GROUPS, ['level' => 'Недопустимый уровень пользователя!'])
                ->length($password, 6, 20, 'Слишком длинный или короткий новый пароль!', false)
                ->email($email, ['email' => 'Вы ввели неверный адрес email, необходим формат name@site.domen!'])
                ->regex($site, '#^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', ['site' => 'Недопустимый адрес сайта, необходим формата http://my_site.domen!'], false)
                ->regex($birthday, '#^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$#', ['birthday' => 'Недопустимая дата рождения, необходим формат дд.мм.гггг!'], false)
                ->regex($created, '#^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$#', ['created' => 'Недопустимая дата регистрации, необходим формат (дд.мм.гггг)!'], false)
                ->regex($icq, '#^[0-9]{5,10}$#', ['icq' => 'Недопустимый формат ICQ, только цифры от 5 до 10 символов!'], false)
                ->regex($skype, '#^[a-z]{1}[0-9a-z\_\.\-]{5,31}$#', ['skype' => 'Недопустимый формат Skype, только латинские символы от 6 до 32!'], false)
                ->length($status, 3, 20, ['status' => 'Слишком длинный или короткий статус!'], false)
                ->true(in_array($themes, $allThemes) || $themes == 0, ['themes' => 'Данная тема не установлена на сайте!'])
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

                saveStatus();

                setFlash('success', 'Данные пользователя успешно изменены!' . $text);
                redirect('/admin/users/edit?user=' . $user->login);

            } else {
                setInput(Request::all());
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
     */
    public function delete()
    {
        $login = check(Request::input('user'));

        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort(404, 'Пользователь не найден!');
        }

        if (Request::isMethod('post')) {

            $token       = check(Request::input('token'));
            $loginblack  = empty(Request::input('loginblack')) ? 0 : 1;
            $mailblack   = empty(Request::input('mailblack')) ? 0 : 1;
            $deltopics   = empty(Request::input('deltopics')) ? 0 : 1;
            $delposts    = empty(Request::input('delposts')) ? 0 : 1;
            $delcomments = empty(Request::input('delcomments')) ? 0 : 1;
            $delimages   = empty(Request::input('delimages')) ? 0 : 1;

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
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
                    foreach ($topics as $topic) {
                        removeDir(UPLOADS . '/forums/' . $topic);
                    }

                    File::query()->where('relate_type', Post::class)->whereIn('relate_id', $posts)->delete();
                    Post::query()->whereIn('topic_id', $topics)->delete();
                    Topic::query()->where('user_id', $user->id)->delete();
                    restatement('forums');
                }

                // Удаление постов форума
                if ($delposts) {

                    $posts  = Post::query()->where('user_id', $user->id)->pluck('topic_id', 'id')->all();

                    $files = File::query()
                        ->where('relate_type', Post::class)
                        ->whereIn('relate_id', array_keys($posts))
                        ->get();

                    if ($files->isNotEmpty()) {
                        foreach ($files as $file) {
                            deleteFile(UPLOADS . '/forums/' . $posts[$file['relate_id']] . '/' . $file->hash);
                            $file->delete();
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
                    deleteAlbum($user);
                }

                deleteUser($user);

                setFlash('success', 'Пользователь успешно удален!');
                redirect('/admin/users');
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/users/delete', compact('user'));
    }
}
