<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\Request;

class ListController extends BaseController
{
    /**
     * Список пользователей
     *
     * @param Request $request
     * @return string
     */
    public function userlist(Request $request): string
    {
        $total = User::query()->count();
        $page = paginate(setting('userlist'), $total);

        $users = User::query()
            ->orderBy('point', 'desc')
            ->orderBy('login')
            ->offset($page->offset)
            ->limit($page->limit)
            ->get();

        $user = check($request->input('user', getUser('login')));

        if ($request->isMethod('post')) {

            $position = User::query()
                ->orderBy('point', 'desc')
                ->orderBy('login')
                ->get()
                ->where('login', $user)
                ->keys()
                ->first();

            if ($position) {
                ++$position;
                $end = ceil($position / $page->limit);

                setFlash('success', 'Позиция в рейтинге: '.$position);
                redirect('/users?page='.$end.'&user='.$user);
            } else {
                setFlash('danger', trans('validator.user'));
            }
        }

        return view('users/users', compact('users', 'page', 'user'));
    }

    /**
     * Список админов
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
     * Рейтинг репутации
     *
     * @param Request $request
     * @return string
     */
    public function authoritylist(Request $request): string
    {
        $total = User::query()->count();
        $page = paginate(setting('avtorlist'), $total);

        $users = User::query()
            ->orderBy('rating', 'desc')
            ->orderBy('login')
            ->offset($page->offset)
            ->limit($page->limit)
            ->get();

        $user = check($request->input('user', getUser('login')));

        if ($request->isMethod('post')) {

            $position = User::query()
                ->orderBy('rating', 'desc')
                ->orderBy('login')
                ->get()
                ->where('login', $user)
                ->keys()
                ->first();

            if ($position) {
                ++$position;
                $end = ceil($position / $page->limit);

                setFlash('success', 'Позиция в рейтинге: '.$position);
                redirect('/authoritylists?page='.$end.'&user='.$user);
            } else {
                setFlash('danger', trans('validator.user'));
            }
        }

        return view('users/authoritylists', compact('users', 'page', 'user'));
    }

    /**
     * Рейтинг толстосумов
     *
     * @param Request $request
     * @return string
     */
    public function ratinglist(Request $request): string
    {
        $total = User::query()->count();
        $page = paginate(setting('userlist'), $total);

        $users = User::query()
            ->orderBy('money', 'desc')
            ->orderBy('login')
            ->offset($page->offset)
            ->limit($page->limit)
            ->get();

        $user = check($request->input('user', getUser('login')));

        if ($request->isMethod('post')) {

            $position = User::query()
                ->orderBy('money', 'desc')
                ->orderBy('login')
                ->get()
                ->where('login', $user)
                ->keys()
                ->first();

            if ($position) {
                ++$position;
                $end = ceil($position / $page->limit);

                setFlash('success', 'Позиция в рейтинге: '.$position);
                redirect('/ratinglists?page='.$end.'&user='.$user);
            } else {
                setFlash('danger', trans('validator.user'));
            }
        }

        return view('users/ratinglists', compact('users', 'page', 'user'));
    }
}
