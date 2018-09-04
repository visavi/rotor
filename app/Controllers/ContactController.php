<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
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

        if (! getUser()) {
            abort(403, 'Для просмотра контактов необходимо авторизоваться!');
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        if (Request::isMethod('post')) {
            $page  = int(Request::input('page', 1));
            $token = check(Request::input('token'));
            $login = check(Request::input('user'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

            $user = User::query()->where('login', $login)->first();
            $validator->notEmpty($user, ['user' => 'Данного пользователя не существует!']);

            if ($user) {
                $validator->notEqual($user->login, getUser('login'), ['user' => 'Запрещено добавлять свой логин!']);

                $totalContact = Contact::query()->where('user_id', getUser('id'))->count();
                $validator->lte($totalContact, setting('limitcontact'), 'Ошибка! Контакт-лист переполнен (Максимум ' . setting('limitcontact') . ' пользователей!)');

                $validator->false(getUser()->isContact($user), ['user' => 'Данный пользователь уже есть в контакт-листе!']);
            }

            if ($validator->isValid()) {

                Contact::query()->create([
                    'user_id'    => getUser('id'),
                    'contact_id' => $user->id,
                    'created_at' => SITETIME,
                ]);

                if (! $user->isIgnore(getUser())) {
                    $message = 'Пользователь [b]'.getUser('login').'[/b] добавил вас в свой контакт-лист!';
                    $user->sendMessage(getUser(), $message);
                }

                setFlash('success', 'Пользователь успешно добавлен в контакт-лист!');
                redirect('/contacts?page='.$page);

            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $total = Contact::query()->where('user_id', getUser('id'))->count();
        $page = paginate(setting('contactlist'), $total);

        $contacts = Contact::query()
            ->where('user_id', getUser('id'))
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('contactor')
            ->get();

        return view('contacts/index', compact('contacts', 'page'));
    }

    /**
     * Заметка для пользователя
     *
     * @param int $id
     * @return string
     */
    public function note($id): string
    {
        $contact = Contact::query()
            ->where('user_id', getUser('id'))
            ->where('id', $id)
            ->first();

        if (! $contact) {
            abort(404, 'Запись не найдена');
        }

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $msg   = check(Request::input('msg'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->length($msg, 0, 1000, ['msg' => 'Слишком большая заметка, не более 1000 символов!']);

            if ($validator->isValid()) {

                $contact->update([
                    'text' => $msg,
                ]);

                setFlash('success', 'Заметка успешно отредактирована!');
                redirect('/contacts');
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('contacts/note', compact('contact'));
    }

    /**
     * Удаление контактов
     *
     * @return void
     */
    public function delete(): void
    {
        $page  = int(Request::input('page', 1));
        $token = check(Request::input('token'));
        $del   = intar(Request::input('del'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true($del, 'Отсутствуют выбранные пользователи для удаления!');

        if ($validator->isValid()) {

            Contact::query()
                ->where('user_id', getUser('id'))
                ->whereIn('id', $del)
                ->delete();

            setFlash('success', 'Выбранные пользователи успешно удалены!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/contacts?page='.$page);
    }
}
