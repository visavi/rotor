<?php

declare(strict_types=1);

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\Request;

class ListController extends BaseController
{
    /**
     * Users list
     *
     * @param Request $request
     * @return string
     */
    public function userlist(Request $request): string
    {
        $users = User::query()
            ->orderByDesc('point')
            ->orderBy('login')
            ->paginate(setting('userlist'));

        $user = check($request->input('user', getUser('login')));

        if ($request->isMethod('post')) {
            $position = User::query()
                ->orderByDesc('point')
                ->orderBy('login')
                ->get()
                ->where('login', $user)
                ->keys()
                ->first();

            if ($position !== null) {
                ++$position;
                $end = ceil($position / setting('userlist'));

                setFlash('success', __('users.rating_position', ['position' => $position]));
                redirect('/users?page='.$end.'&user=' . $user);
            } else {
                setFlash('danger', __('validator.user'));
            }
        }

        return view('users/users', compact('users', 'user'));
    }

    /**
     * Admins List
     *
     * @return string
     */
    public function adminlist(): string
    {
        $users = User::query()
            ->whereIn('level', User::ADMIN_GROUPS)
            ->orderByRaw("field(level, '".implode("','", User::ADMIN_GROUPS)."')")
            ->get();

        return view('users/administrators', compact('users'));
    }

    /**
     * Reputation rating
     *
     * @param Request $request
     * @return string
     */
    public function authoritylist(Request $request): string
    {
        $users = User::query()
            ->orderByDesc('rating')
            ->orderBy('login')
            ->paginate(setting('avtorlist'));

        $user = check($request->input('user', getUser('login')));

        if ($request->isMethod('post')) {

            $position = User::query()
                ->orderByDesc('rating')
                ->orderBy('login')
                ->get()
                ->where('login', $user)
                ->keys()
                ->first();

            if ($position !== null) {
                ++$position;
                $end = ceil($position / setting('avtorlist'));

                setFlash('success', __('users.rating_position', ['position' => $position]));
                redirect('/authoritylists?page=' . $end . '&user=' . $user);
            } else {
                setFlash('danger', __('validator.user'));
            }
        }

        return view('users/authoritylists', compact('users', 'user'));
    }

    /**
     * Riches rating
     *
     * @param Request $request
     * @return string
     */
    public function ratinglist(Request $request): string
    {
        $users = User::query()
            ->orderByDesc('money')
            ->orderBy('login')
            ->paginate(setting('userlist'));

        $user = check($request->input('user', getUser('login')));

        if ($request->isMethod('post')) {

            $position = User::query()
                ->orderByDesc('money')
                ->orderBy('login')
                ->get()
                ->where('login', $user)
                ->keys()
                ->first();

            if ($position !== null) {
                ++$position;
                $end = ceil($position / setting('userlist'));

                setFlash('success', __('users.rating_position', ['position' => $position]));
                redirect('/ratinglists?page='.$end.'&user='.$user);
            } else {
                setFlash('danger', __('validator.user'));
            }
        }

        return view('users/ratinglists', compact('users', 'user'));
    }
}
