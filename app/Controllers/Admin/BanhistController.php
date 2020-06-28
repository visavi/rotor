<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Banhist;
use App\Models\User;
use Illuminate\Http\Request;

class BanhistController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::MODER)) {
            abort(403, __('errors.forbidden'));
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $records = Banhist::query()
            ->orderByDesc('created_at')
            ->with('user', 'sendUser')
            ->paginate(setting('listbanhist'));

        return view('admin/banhists/index', compact('records'));
    }

    /**
     * История банов
     *
     * @param Request $request
     *
     * @return string
     */
    public function view(Request $request): string
    {
        $user = getUserByLogin($request->input('user'));

        if (! $user) {
            abort(404, __('validator.user'));
        }

        $banhist = Banhist::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->with('user', 'sendUser')
            ->paginate(setting('listbanhist'))
            ->appends(['user' => $user->login]);

        return view('admin/banhists/view', compact('user', 'banhist'));
    }

    /**
     * Удаление банов
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return void
     */
    public function delete(Request $request, Validator $validator): void
    {
        $page  = int($request->input('page', 1));
        $del   = intar($request->input('del'));
        $login = $request->input('user');

        $validator->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
            ->true($del, __('validator.deletion'));

        if ($validator->isValid()) {
            Banhist::query()->whereIn('id', $del)->delete();

            setFlash('success', __('main.records_deleted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        if ($login) {
            redirect('/admin/banhists/view?user=' . $login . '&page=' . $page);
        } else {
            redirect('/admin/banhists?page=' . $page);
        }
    }
}
