<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListController extends Controller
{
    /**
     * Users list
     *
     *
     * @return View|RedirectResponse
     */
    public function userlist(Request $request)
    {
        $type = check($request->input('type', 'users'));
        $sort = check($request->input('sort', 'point'));
        $user = $request->input('user', getUser('login'));
        $order = match ($sort) {
            'time'   => 'created_at',
            'rating' => 'rating',
            'money'  => 'money',
            default  => 'point',
        };

        $users = User::query()
            ->when($type === 'admins', static function (Builder $query) {
                return $query->whereIn('level', User::ADMIN_GROUPS);
            })
            ->when($type === 'birthdays', static function (Builder $query) {
                return $query->whereRaw('substr(birthday, 1, 5) = ?', date('d.m', SITETIME));
            })
            ->orderByDesc($order)
            ->paginate(setting('userlist'))
            ->appends(compact('type', 'sort'));

        if ($request->isMethod('post')) {
            $position = User::query()
                ->when($type === 'admins', static function (Builder $query) {
                    return $query->whereIn('level', User::ADMIN_GROUPS);
                })
                ->when($type === 'birthdays', static function (Builder $query) {
                    return $query->whereRaw('substr(birthday, 1, 5) = ?', date('d.m', SITETIME));
                })
                ->orderByDesc($order)
                ->get()
                ->where('login', $user)
                ->keys()
                ->first();

            if ($position !== null) {
                ++$position;
                $end = ceil($position / setting('userlist'));


                setFlash('success', __('users.rating_position', ['position' => $position]));

                return redirect('users?page=' . $end . '&user=' . $user . '&type=' . $type. '&sort=' . $sort);
            }

            setFlash('danger', __('validator.user'));
        }

        return view('users/users', compact('users', 'user', 'type', 'sort'));
    }
}
