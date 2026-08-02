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
     */
    public function index(Request $request, Validator $validator): View|RedirectResponse
    {
        if ($request->isMethod('post')) {
            $page = int($request->input('page', 1));
            $choice = intar($request->input('choice'));
            $action = $request->input('action');

            $validator
                ->notEmpty($choice, __('admin.reglists.users_not_selected'))
                ->in($action, ['yes', 'no'], ['action' => __('main.action_not_selected')]);

            if ($validator->isValid()) {
                if ($action === 'yes') {
                    User::query()
                        ->whereIn('id', $choice)
                        ->update([
                            'level' => User::USER,
                        ]);

                    $message = __('admin.reglists.users_success_approved');
                } else {
                    $users = User::query()
                        ->whereIn('id', $choice)
                        ->get();

                    $users->each(static function (User $user) {
                        $user->delete();
                    });

                    $message = __('admin.reglists.users_success_deleted');
                }

                return redirect('admin/reglists?page=' . $page)
                    ->with('success', $message);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors($validator->getErrors());
        }

        $users = User::query()
            ->where('level', User::PENDED)
            ->orderByDesc('created_at')
            ->paginate(setting('reglist'));

        return view('admin/reglists/index', compact('users'));
    }
}
