<?php

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\User;
use Illuminate\Http\Request;

class DelUserController extends AdminController
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
     * @param Request $request
     * @return string
     */
    public function index(Request $request): string
    {
        $users  = collect();
        $period = check($request->input('period'));
        $point  = check($request->input('point'));

        if ($request->isMethod('post')) {

            if ($period < 180) {
                abort('default', 'Указанно недопустимое время для удаления!');
            }

            $users = User::query()
                ->where('updated_at', '<', strtotime('-' . $period . ' days', SITETIME))
                ->where('point', '<=', $point)
                ->get();

            if ($users->isEmpty()) {
                abort('default', 'Отсутствуют пользователи для удаления!');
            }
        }

        $total = User::query()->count();

        return view('admin/delusers/index', compact('users', 'total', 'period', 'point'));
    }

    /**
     * Очистка пользователей
     *
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function clear(Request $request, Validator $validator): void
    {
        $token  = check($request->input('token'));
        $period = check($request->input('period'));
        $point  = check($request->input('point'));

        $validator
            ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->gte($period, 180, 'Указанно недопустимое время для удаления!');

        $users = User::query()
            ->where('updated_at', '<', strtotime('-' . $period . ' days', SITETIME))
            ->where('point', '<=', $point)
            ->get();

        $validator->true($users->isNotEmpty(), 'Отсутствуют пользователи для удаления!');

        if ($validator->isValid()) {

            foreach ($users as $user) {
                $user->deleteAlbum();
                $user->delete();
            }

            setFlash('success', 'Пользователи успешно удалены!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/delusers');
    }
}
