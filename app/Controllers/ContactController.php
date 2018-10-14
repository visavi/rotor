<?php

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Request;

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
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function index(Request $request, Validator $validator): string
    {
        if ($request->isMethod('post')) {
            $page  = int($request->input('page', 1));
            $token = check($request->input('token'));
            $login = check($request->input('user'));

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
                setInput($request->all());
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
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function note(int $id, Request $request, Validator $validator): string
    {
        $contact = Contact::query()
            ->where('user_id', getUser('id'))
            ->where('id', $id)
            ->first();

        if (! $contact) {
            abort(404, 'Запись не найдена');
        }

        if ($request->isMethod('post')) {

            $token = check($request->input('token'));
            $msg   = check($request->input('msg'));

            $validator->equal($token, $_SESSION['token'], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->length($msg, 0, 1000, ['msg' => 'Слишком большая заметка, не более 1000 символов!']);

            if ($validator->isValid()) {

                $contact->update([
                    'text' => $msg,
                ]);

                setFlash('success', 'Заметка успешно отредактирована!');
                redirect('/contacts');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('contacts/note', compact('contact'));
    }

    /**
     * Удаление контактов
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
