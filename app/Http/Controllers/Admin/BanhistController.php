<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Banhist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BanhistController extends AdminController
{
    /**
     * Главная страница
     *
     * @return View
     */
    public function index(): View
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
     * @return View
     */
    public function view(Request $request): View
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
     * @return RedirectResponse
     */
    public function delete(Request $request, Validator $validator): RedirectResponse
    {
        $page  = int($request->input('page', 1));
        $del   = intar($request->input('del'));
        $login = $request->input('user');

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true($del, __('validator.deletion'));

        if ($validator->isValid()) {
            Banhist::query()->whereIn('id', $del)->delete();

            setFlash('success', __('main.records_deleted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        if ($login) {
            return redirect('admin/banhists/view?user=' . $login . '&page=' . $page);
        }

        return redirect('admin/banhists?page=' . $page);
    }
}
