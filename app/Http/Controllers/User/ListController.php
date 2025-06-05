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
        $user = $request->input('user', getUser('login'));
        $position = (int) $request->input('position');

        $sort = $request->input('sort', 'point');
        $order = $request->input('order', 'desc');

        [$sorting, $orderBy] = User::getSorting($sort, $order);

        $baseSearch = User::query()
            ->when($type === 'admins', static function (Builder $query) {
                return $query->whereIn('level', User::ADMIN_GROUPS);
            })
            ->when($type === 'birthdays', static function (Builder $query) {
                return $query->whereRaw('substr(birthday, 1, 5) = ?', date('d.m', SITETIME));
            })
            ->orderBy(...$orderBy)
            ->orderBy('id');

        $users = $baseSearch
            ->paginate(setting('userlist'))
            ->appends(compact('type', 'sort', 'order'));

        if ($request->isMethod('post')) {
            $search = $baseSearch
                ->select('login', 'name')
                ->toBase()
                ->lazy();

            $position = $search->search(function ($userModel) use ($user) {
                return utfLower($userModel->login) === utfLower($user)
                    || mb_stripos($userModel->login, $user) !== false
                    || mb_stripos((string) $userModel->name, $user) !== false;
            });

            if ($position !== false) {
                $position++;
                $end = ceil($position / setting('userlist'));

                return redirect('users?page=' . $end . '&user=' . $user . '&type=' . $type . '&sort=' . $sort . '&position=' . $position)
                    ->with('success', __('users.rating_position', ['position' => $position]));
            }

            session()->flash('danger', __('validator.user'));
        }

        return view('users/users', compact('users', 'user', 'type', 'sort', 'order', 'sorting', 'position'));
    }
}
