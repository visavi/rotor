<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Banhist;
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
            ->orderBy('joined', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->get();

        return view('admin/users/index', compact('users', 'page'));
    }

    /**
     * Редактирование
     */
    public function edit()
    {
        $login = check(Request::input('user'));

        $user = User::query()->whereRaw('lower(login) = ?', [strtolower($login)])->first();

        if (! $user) {
            abort('default', 'Пользователь не найден!');
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
            $joined    = check(Request::input('joined'));
            $birthday  = check(Request::input('birthday'));
            $icq       = check(str_replace('-', '', Request::input('icq')));
            $skype     = check(strtolower(Request::input('skype')));
            $point     = int(Request::input('point'));
            $money     = int(Request::input('money'));
            $status    = check(Request::input('status'));
            $posrating = int(Request::input('posrating'));
            $negrating = int(Request::input('negrating'));
            $themes    = check(Request::input('themes'));
            $gender    = Request::input('gender') == 1 ? 1 : 2;
            $info      = check(Request::input('info'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->in($level, User::ALL_GROUPS, ['level' => 'Недопустимый уровень пользователя!'])
                ->length($password, 6, 20, 'Слишком длинный или короткий новый пароль!', false)
                ->email($email, ['email' => 'Вы ввели неверный адрес email, необходим формат name@site.domen!'])
                ->regex($site, '#^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', ['site' => 'Недопустимый адрес сайта, необходим формата http://my_site.domen!'], false)
                ->regex($birthday, '#^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$#', ['birthday' => 'Недопустимая дата рождения, необходим формат дд.мм.гггг!'], false)
                ->regex($joined, '#^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$#', ['joined' => 'Недопустимая дата регистрации, необходим формат (дд.мм.гггг)!'], false)
                ->regex($icq, '#^[0-9]{5,10}$#', ['icq' => 'Недопустимый формат ICQ, только цифры от 5 до 10 символов!'], false)
                ->regex($skype, '#^[a-z]{1}[0-9a-z\_\.\-]{5,31}$#', ['skype' => 'Недопустимый формат Skype, только латинские символы от 6 до 32!'], false)
                ->length($status, 3, 20, ['status' => 'Слишком длинный или короткий статус!'], false)
                ->true(in_array($themes, $allThemes) || $themes == 0, ['themes' => 'Данная тема не установлена на сайте!'])
                ->length($info, 0, 1000, ['info' => 'Слишком большая информация о себе, не более 1000 символов!']);

            if ($validator->isValid()) {

                $name    = utfSubstr($name, 0, 20);
                $country = utfSubstr($country, 0, 30);
                $city    = utfSubstr($city, 0, 50);

                $user->update([
                    'level'     => $level,
                    'email'     => $email,
                    'name'      => $name,
                    'country'   => $country,
                    'city'      => $city,
                    'site'      => $site,
                    'joined'    => $joined,
                    'birthday'  => $birthday,
                    'icq'       => $icq,
                    'skype'     => $skype,
                    'point'     => $point,
                    'money'     => $money,
                    'status'    => $status,
                    'posrating' => $posrating,
                    'negrating' => $negrating,
                    'themes'    => $themes,
                    'gender'    => $gender,
                    'info'      => $info,
                ]);

                saveStatus();

                setFlash('success', 'Ваш профиль успешно изменен!');
                redirect('/admin/users/edit?user=' . $user->login);

            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $banhist = Banhist::query()
            ->where('user_id', $user->id)
            ->whereIn('type', [1, 2])
            ->orderBy('created_at', 'desc')
            ->first();

        return view('admin/users/edit', compact('user', 'banhist', 'allThemes', 'allGroups', 'adminGroups'));
    }
}
