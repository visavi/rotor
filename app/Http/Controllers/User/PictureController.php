<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\PhotoRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PictureController extends Controller
{
    /**
     * Главная страница
     */
    public function index(#[CurrentUser] User $user): View
    {
        return view('users/picture', compact('user'));
    }

    /**
     * Загрузка фото
     */
    public function store(PhotoRequest $request, UserService $userService, #[CurrentUser] User $user): RedirectResponse
    {
        $userService->savePhoto($user, $request->file('photo'));

        return redirect('profile')
            ->with('success', __('users.photo_success_uploaded'));
    }

    /**
     * Удаление фотографии
     */
    public function delete(UserService $userService, #[CurrentUser] User $user): RedirectResponse
    {
        if (! $user->picture) {
            return redirect('profile')
                ->withErrors(__('users.photo_not_exist'));
        }

        $userService->deletePhoto($user);

        return redirect('profile')
            ->with('success', __('users.photo_success_deleted'));
    }
}
