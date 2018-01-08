<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\BlackList;
use App\Models\User;

class BlacklistController extends AdminController
{
    /**
     * @var array
     */
    private $types;

    /**
     * @var string
     */
    private $type;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::ADMIN)) {
            abort(403, 'Доступ запрещен!');
        }

        $this->types = ['email', 'login', 'domain'];
        $this->type = Request::input('type', 'email');

        if (! in_array($this->type, $this->types)) {
            abort('default', 'Указанный тип не найден!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $type = $this->type;

        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $value = check(utfLower(Request::input('value')));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($value, 1, 100, ['value' => 'Вы не ввели запись или она слишком длинная!']);

            if ($type == 'email') {
                $validator->regex($value, '#^([a-z0-9_\-\.])+\@([a-z0-9_\-\.])+(\.([a-z0-9])+)+$#', ['value' => 'Недопустимый адрес email, необходим формат name@site.domen!']);
            }

            if ($type == 'login') {
                $validator->regex($value, '|^[a-z0-9\-]+$|', ['value' => 'Недопустимые символы в логине!']);
            }

            if ($type == 'domain') {
                $value = siteDomain($value);
                $validator->regex($value, '#([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', ['value' => 'Недопустимый адрес сайта!']);
            }

            $duplicate = BlackList::query()->where('type', $type)->where('value', $value)->first();
            $validator->empty($duplicate, ['value' => 'Данная запись уже имеется в списках!']);

            if ($validator->isValid()) {

                BlackList::query()->create([
                    'type'       => $type,
                    'value'      => $value,
                    'user_id'    => getUser('id'),
                    'created_at' => SITETIME,
                ]);

                setFlash('success', 'Запись успешно добавлена в черный список!');
                redirect('/admin/blacklist?type=' . $type);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $total = BlackList::query()->where('type', $type)->count();
        $page = paginate(setting('blacklist'), $total);

        $lists = BlackList::query()
            ->where('type', $type)
            ->orderBy('created_at', 'desc')
            ->limit($page['limit'])
            ->offset($page['offset'])
            ->with('user')
            ->get();

        return view('admin/blacklist/index', compact('lists', 'type', 'page'));
    }

    /**
     * Удаление записей
     */
    public function delete()
    {
        $page  = int(Request::input('page', 1));
        $token = check(Request::input('token'));
        $del   = intar(Request::input('del'));
        $type  = $this->type;

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true($del, 'Отсутствуют выбранные записи для удаления!');

        if ($validator->isValid()) {
            BlackList::query()->where('type', $type)->whereIn('id', $del)->delete();

            setFlash('success', 'Выбранные записи успешно удалены!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/blacklist?type=' . $type . '&page=' . $page);
    }
}
