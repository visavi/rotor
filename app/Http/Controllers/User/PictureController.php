<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\PhotoRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PictureController extends Controller
{
    public ?User $user;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->middleware('check.user');

        $this->middleware(function ($request, $next) {
            $this->user = getUser();

            return $next($request);
        });
    }

    /**
     * Главная страница
     */
    public function index(): View
    {
        $user = $this->user;

        return view('users/picture', compact('user'));
    }

    /**
     * Загрузка фото
     */
    public function store(PhotoRequest $request, UserService $userService): RedirectResponse
    {
        $userService->savePhoto($this->user, $request->file('photo'));

        return redirect('profile')
            ->with('success', __('users.photo_success_uploaded'));
    }

    /**
     * Удаление фотографии
     */
    public function delete(UserService $userService): RedirectResponse
    {
        if (! $this->user->picture) {
            return redirect('profile')
                ->withErrors(__('users.photo_not_exist'));
        }

        $userService->deletePhoto($this->user);

        return redirect('profile')
            ->with('success', __('users.photo_success_deleted'));
    }
}
