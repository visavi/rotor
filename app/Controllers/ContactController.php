<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Contact;
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
            abort(403, __('main.not_authorized'));
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
        $login = check($request->input('user'));

        if ($request->isMethod('post')) {
            $page  = int($request->input('page', 1));
            $token = check($request->input('token'));

            $validator->equal($token, $_SESSION['token'], __('validator.token'));

            $user = getUserByLogin($login);
            $validator->notEmpty($user, ['user' => __('validator.user')]);

            if ($user) {
                $validator->notEqual($user->login, getUser('login'), ['user' => __('contacts.forbidden_yourself')]);

                $totalContact = Contact::query()->where('user_id', getUser('id'))->count();
                $validator->lte($totalContact, setting('limitcontact'), __('contacts.contact_full', ['max' => setting('limitcontact')]));

                $validator->false(getUser()->isContact($user), ['user' => __('contacts.already_contacts')]);
            }

            if ($validator->isValid()) {
                Contact::query()->create([
                    'user_id'    => getUser('id'),
                    'contact_id' => $user->id,
                    'created_at' => SITETIME,
                ]);

                if (! $user->isIgnore(getUser())) {
                    $text = textNotice('contact', ['login' => getUser('login')]);
                    $user->sendMessage(null, $text);
                }

                setFlash('success', __('contacts.success_added'));
                redirect('/contacts?page='.$page);

            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $contacts = Contact::query()
            ->where('user_id', getUser('id'))
            ->orderByDesc('created_at')
            ->with('contactor')
            ->paginate(setting('contactlist'));

        return view('contacts/index', compact('contacts', 'login'));
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
            abort(404, __('main.record_not_found'));
        }

        if ($request->isMethod('post')) {
            $token = check($request->input('token'));
            $msg   = check($request->input('msg'));

            $validator->equal($token, $_SESSION['token'], ['msg' => __('validator.token')])
                ->length($msg, 0, 1000, ['msg' => __('users.note_to_big')]);

            if ($validator->isValid()) {
                $contact->update([
                    'text' => $msg,
                ]);

                setFlash('success', __('users.note_saved_success'));
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

        $validator->equal($token, $_SESSION['token'], __('validator.token'))
            ->true($del, __('validator.deletion'));

        if ($validator->isValid()) {
            Contact::query()
                ->where('user_id', getUser('id'))
                ->whereIn('id', $del)
                ->delete();

            setFlash('success', __('main.records_deleted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/contacts?page='.$page);
    }
}
