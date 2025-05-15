<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReglistController extends AdminController
{
    /**
     * Главная страница
     *
     *
     * @return View|RedirectResponse
     */
    public function index(Request $request, Validator $validator)
    {
        if ($request->isMethod('post')) {
            $page = int($request->input('page', 1));
            $choice = intar($request->input('choice'));
            $action = $request->input('action');

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->notEmpty($choice, __('admin.reglists.users_not_selected'))
                ->in($action, ['yes', 'no'], ['action' => __('main.action_not_selected')]);

            if ($validator->isValid()) {
                if ($action === 'yes') {
                    User::query()
                        ->whereIn('id', $choice)
                        ->update([
                            'level' => User::USER,
                        ]);

                    setFlash('success', __('admin.reglists.users_success_approved'));
                } else {
                    $users = User::query()
                        ->whereIn('id', $choice)
                        ->get();

                    $users->each(static function (User $user) {
                        $user->delete();
                    });

                    setFlash('success', __('admin.reglists.users_success_deleted'));
                }

                return redirect('admin/reglists?page=' . $page);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $users = User::query()
            ->where('level', User::PENDED)
            ->orderByDesc('created_at')
            ->paginate(setting('reglist'));

        return view('admin/reglists/index', compact('users'));
    }
}
