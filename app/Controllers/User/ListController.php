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
        $sort   = check($request->input('sort', 'point'));
        $admins = int($request->input('admins', 0));

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
            ->when($admins, static function (Builder $query) {
                return $query->whereIn('level', User::ADMIN_GROUPS);
            })
            ->orderByDesc($order)
            ->paginate(setting('userlist'))
            ->appends([
                'admins' => $admins,
                'sort'   => $sort,
            ]);

        $user = $request->input('user', getUser('login'));

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

        return view('users/users', compact('users', 'user', 'admins', 'sort'));
    }
}
