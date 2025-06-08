<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->middleware('check.user');
    }

    /**
     * Главная страница
     */
    public function index(Request $request, Validator $validator): View|RedirectResponse
    {
        $login = $request->input('user');

        if ($request->isMethod('post')) {
            $page = int($request->input('page', 1));

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'));

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

                return redirect('contacts?page=' . $page);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
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
     */
    public function note(int $id, Request $request, Validator $validator): View|RedirectResponse
    {
        $contact = Contact::query()
            ->where('user_id', getUser('id'))
            ->where('id', $id)
            ->first();

        if (! $contact) {
            abort(404, __('main.record_not_found'));
        }

        if ($request->isMethod('post')) {
            $msg = $request->input('msg');

            $validator->equal($request->input('_token'), csrf_token(), ['msg' => __('validator.token')])
                ->length($msg, 0, 1000, ['msg' => __('users.note_to_big')]);

            if ($validator->isValid()) {
                $contact->update([
                    'text' => $msg,
                ]);

                setFlash('success', __('users.note_saved_success'));

                return redirect('contacts');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('contacts/note', compact('contact'));
    }

    /**
     * Удаление контактов
     */
    public function delete(Request $request, Validator $validator): RedirectResponse
    {
        $page = int($request->input('page', 1));
        $del = intar($request->input('del'));

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
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

        return redirect('contacts?page=' . $page);
    }
}
