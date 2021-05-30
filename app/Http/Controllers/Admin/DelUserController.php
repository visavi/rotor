<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DelUserController extends AdminController
{
    /**
     * Главная страница
     *
     * @param Request $request
     *
     * @return View
     */
    public function index(Request $request): View
    {
        $users  = collect();
        $period = int($request->input('period'));
        $point  = int($request->input('point'));

        if ($request->isMethod('post')) {
            if ($period < 180) {
                abort(200, __('admin.delusers.invalid_period'));
            }

            $users = User::query()
                ->where('updated_at', '<', strtotime('-' . $period . ' days', SITETIME))
                ->where('point', '<=', $point)
                ->get();

            if ($users->isEmpty()) {
                abort(200, __('admin.delusers.users_not_found'));
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
     *
     * @return RedirectResponse
     */
    public function clear(Request $request, Validator $validator): RedirectResponse
    {
        $period = int($request->input('period'));
        $point  = int($request->input('point'));

        $validator
            ->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->gte($period, 180, __('admin.delusers.invalid_period'));

        $users = User::query()
            ->where('updated_at', '<', strtotime('-' . $period . ' days', SITETIME))
            ->where('point', '<=', $point)
            ->get();

        $validator->true($users->isNotEmpty(), __('admin.delusers.users_not_found'));

        if ($validator->isValid()) {
            foreach ($users as $user) {
                $user->deleteAlbum();
                $user->delete();
            }

            setFlash('success', __('admin.delusers.success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('admin/delusers');
    }
}
