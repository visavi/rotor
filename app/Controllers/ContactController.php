<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validation;
use App\Models\Contact;
use App\Models\User;

class ContactController extends BaseController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isUser()) {
            abort(403, 'Для просмотра контактов необходимо авторизоваться!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        if (Request::isMethod('post')) {
            $page  = abs(intval(Request::input('page', 1)));
            $token = check(Request::input('token'));
            $login = check(Request::input('user'));

            $validation = new Validation();
            $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!');

            $user = User::query()->where('login', $login)->first();
            $validation->addRule('not_empty', $user, 'Данного пользователя не существует!');

            if ($user) {
                $validation->addRule('not_equal', [$user->login, user('login')], 'Запрещено добавлять свой логин!');

                $totalContact = Contact::query()->where('user_id', user('id'))->count();
                $validation->addRule('min', [$totalContact, setting('limitcontact')], 'Ошибка! Контакт-лист переполнен (Максимум ' . setting('limitcontact') . ' пользователей!)');

                $validation->addRule('custom', ! isContact(user(), $user), 'Данный пользователь уже есть в контакт-листе!');
            }

            if ($validation->run()) {

                Contact::query()->create([
                    'user_id'    => user('id'),
                    'contact_id' => $user->id,
                    'created_at' => SITETIME,
                ]);

                if (! isIgnore($user, user())) {
                    $message = 'Пользователь [b]'.user('login').'[/b] добавил вас в свой контакт-лист!';
                    sendPrivate($user->id, user('id'), $message);
                }

                setFlash('success', 'Пользователь успешно добавлен в контакт-лист!');
                redirect('/contact?page='.$page);

            } else {
                setInput(Request::all());
                setFlash('danger', $validation->getErrors());
            }
        }

        $total = Contact::query()->where('user_id', user('id'))->count();
        $page = paginate(setting('contactlist'), $total);

        $contacts = Contact::query()
            ->where('user_id', user('id'))
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('contactor')
            ->get();

        return view('contact/index', compact('contacts', 'page'));
    }

    /**
     * Заметка для пользователя
     */
    public function note($id)
    {
        $contact = Contact::query()
            ->where('user_id', user('id'))
            ->where('id', $id)
            ->first();

        if (! $contact) {
            abort('default', 'Запись не найдена');
        }

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $msg   = check(Request::input('msg'));

            $validation = new Validation();
            $validation->addRule('equal', [$token, $_SESSION['token']], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->addRule('string', $msg, ['msg' => 'Слишком большая заметка, не более 1000 символов!'], true, 0, 1000);

            if ($validation->run()) {

                $contact->update([
                    'text' => $msg,
                ]);

                setFlash('success', 'Заметка успешно отредактирована!');
                redirect("/contact");
            } else {
                setInput(Request::all());
                setFlash('danger', $validation->getErrors());
            }
        }

        return view('contact/note', compact('contact'));
    }

    /**
     * Удаление контактов
     */
    public function delete()
    {
        $page  = abs(intval(Request::input('page', 1)));
        $token = check(Request::input('token'));
        $del   = intar(Request::input('del'));

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            ->addRule('bool', $del, 'Ошибка удаления! Отсутствуют выбранные пользователи');

        if ($validation->run()) {

            Contact::query()
                ->where('user_id', user('id'))
                ->whereIn('id', $del)
                ->delete();

            setFlash('success', 'Выбранные пользователи успешно удалены!');
        } else {
            setFlash('danger', $validation->getErrors());
        }

        redirect("/contact?page=$page");
    }
}
