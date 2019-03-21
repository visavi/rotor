<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\User;
use Illuminate\Http\Request;

class ReglistController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::MODER)) {
            abort(403, 'Доступ запрещен!');
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
            $page   = int($request->input('page', 1));
            $token  = check($request->input('token'));
            $choice = intar($request->input('choice'));
            $action = check($request->input('action'));

            $validator->equal($token, $_SESSION['token'], trans('validator.token'))
                ->notEmpty($choice, 'Отсутствуют выбранные пользователи!')
                ->in($action, ['yes', 'no'], ['action' => 'Необходимо выбрать действие!']);

            if ($validator->isValid()) {

                if ($action === 'yes') {

                    User::query()
                        ->whereIn('id', $choice)
                        ->update([
                            'level' => User::USER
                        ]);

                    setFlash('success', 'Выбранные пользователи успешно одобрены!');
                } else {

                    $users = User::query()
                        ->whereIn('id', $choice)
                        ->get();

                    foreach ($users as $user) {
                        $user->delete();
                    }

                    setFlash('success', 'Выбранные пользователи успешно удалены!');
                }

                redirect('/admin/reglists?page=' . $page);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $total = User::query()->where('level', User::PENDED)->count();
        $page = paginate(setting('reglist'), $total);

        $users = User::query()
            ->where('level', User::PENDED)
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->get();

        return view('admin/reglists/index', compact('users', 'page'));
    }
}
