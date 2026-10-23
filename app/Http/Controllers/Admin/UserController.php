<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UserRequest;
use App\Models\Banhist;
use App\Models\BlackList;
use App\Models\Comment;
use App\Models\User;
use App\Support\Registry;
use App\Support\Restatement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $users = User::query()
            ->orderByDesc('created_at')
            ->paginate(setting('userlist'));

        return view('admin/users/index', compact('users'));
    }

    /**
     * Поиск пользователей
     */
    public function search(Request $request): View
    {
        // ?q[]= — массив: склейка с % падала. Экранирует вывод Blade, check() здесь лишний
        $q = $request->input('q');
        $q = is_string($q) ? $q : '';

        $users = User::query()
            ->when(
                $q === '1',
                fn ($query) => $query->whereRaw("login RLIKE '^[-0-9]'"),
                fn ($query) => $query->where('login', 'like', $q . '%')
            )
            ->orderByDesc('point')
            ->paginate(setting('usersearch'))
            ->appends(['q' => $q]);

        return view('admin/users/search', compact('users'));
    }

    /**
     * Форма редактирования пользователя
     */
    public function edit(Request $request): View
    {
        $user = $this->findUser($request);

        $allThemes = getAvailableThemes();
        $adminGroups = User::ADMIN_GROUPS;

        $allGroups = [];
        foreach (User::ALL_GROUPS as $level) {
            $allGroups[$level] = User::getLevelByKey($level);
        }

        $banhist = Banhist::query()
            ->where('user_id', $user->id)
            ->whereIn('type', ['ban', 'change'])
            ->orderByDesc('created_at')
            ->first();

        return view('admin/users/edit', compact('user', 'banhist', 'allThemes', 'allGroups', 'adminGroups'));
    }

    /**
     * Редактирование пользователя
     */
    public function update(UserRequest $request): RedirectResponse
    {
        $user = $request->targetUser() ?? abort(404, __('validator.user'));

        $password = $request->validated('password');
        $text = $password ? __('users.user_new_password', ['password' => $password]) : null;
        $posrating = int($request->input('posrating'));
        $negrating = int($request->input('negrating'));

        $user->update([
            'password'  => $password ? Hash::make($password) : $user->password,
            'level'     => $request->validated('level'),
            'email'     => $request->validated('email') ?: null,
            'name'      => $request->string('name')->substr(0, 20)->value(),
            'country'   => $request->string('country')->substr(0, 30)->value(),
            'city'      => $request->string('city')->substr(0, 50)->value(),
            'phone'     => $request->validated('phone'),
            'site'      => $request->validated('site'),
            'birthday'  => $request->validated('birthday'),
            'point'     => int($request->input('point')),
            'money'     => int($request->input('money')),
            'status'    => $request->validated('status'),
            'rating'    => $posrating - $negrating,
            'posrating' => $posrating,
            'negrating' => $negrating,
            'themes'    => $request->validated('themes'),
            'gender'    => $request->input('gender') === User::MALE ? User::MALE : User::FEMALE,
            'info'      => $request->validated('info'),
        ]);

        foreach (Registry::$onProfileSave as $handler) {
            $handler($user, $request);
        }

        clearCache('status');

        return redirect($this->editUrl($user))
            ->with('success', array_filter([__('users.user_success_changed'), $text]));
    }

    /**
     * Форма удаления пользователя
     */
    public function delete(Request $request): View
    {
        $user = $this->findUser($request);

        return view('admin/users/delete', compact('user'));
    }

    /**
     * Удаление пользователя
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $this->findUser($request);

        if (in_array($user->level, User::ADMIN_GROUPS, true)) {
            return redirect('admin/users/delete?' . http_build_query(['user' => $user->login]))
                ->withErrors(__('users.admins_remove_forbidden'));
        }

        if ($request->boolean('loginblack')) {
            BlackList::query()->firstOrCreate(
                ['type' => 'login', 'value' => $user->login],
                ['user_id' => auth()->id()],
            );
        }

        if ($request->boolean('mailblack') && $user->email) {
            BlackList::query()->firstOrCreate(
                ['type' => 'email', 'value' => $user->email],
                ['user_id' => auth()->id()],
            );
        }

        foreach (Registry::$onAdminDeleteUser as $handler) {
            $handler($user, $request);
        }

        // Удаление комментариев
        if ($request->boolean('delcomments')) {
            $comments = Comment::query()
                ->where('user_id', $user->id)
                ->get();

            $comments->each(static function (Comment $comment) {
                $comment->delete();
            });

            if ($comments->isNotEmpty()) {
                Restatement::run(array_keys(Restatement::$handlers));
            }
        }

        $user->delete();

        return redirect('admin/users')
            ->with('success', __('users.user_success_deleted'));
    }

    /**
     * Возвращает пользователя из ?user= или 404
     */
    private function findUser(Request $request): User
    {
        $user = getUserByLogin($request->string('user')->value());

        if (! $user) {
            abort(404, __('validator.user'));
        }

        return $user;
    }

    /**
     * Адрес страницы редактирования пользователя
     */
    private function editUrl(User $user): string
    {
        return 'admin/users/edit?' . http_build_query(['user' => $user->login]);
    }
}
