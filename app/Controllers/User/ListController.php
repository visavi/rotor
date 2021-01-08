<?php

declare(strict_types=1);

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ListController extends BaseController
{
    /**
     * Users list
     *
     * @param Request $request
     *
     * @return string
     */
    public function userlist(Request $request): string
    {
        $type = check($request->input('type', 'users'));
        $sort = check($request->input('sort', 'point'));

        switch ($sort) {
            case 'time':
                $order = 'created_at';
                break;
            case 'rating':
                $order = 'rating';
                break;
            case 'money':
                $order = 'money';
                break;
            default:
                $order = 'point';
        }

        $users = User::query()
            ->when($type === 'admins', static function (Builder $query) {
                return $query->whereIn('level', User::ADMIN_GROUPS);
            })
            ->when($type === 'birthdays', static function (Builder $query) {
                return $query->whereRaw('substr(birthday, 1, 5) = ?', date('d.m', SITETIME));
            })
            ->orderByDesc($order)
            ->paginate(setting('userlist'))
            ->appends([
                'type' => $type,
                'sort' => $sort,
            ]);

        $user = $request->input('user', getUser('login'));

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
                redirect('/users?page=' . $end . '&user=' . $user . '&type=' . $type. '&sort=' . $sort);
            } else {
                setFlash('danger', __('validator.user'));
            }
        }

        return view('users/users', compact('users', 'user', 'type', 'sort'));
    }
}
