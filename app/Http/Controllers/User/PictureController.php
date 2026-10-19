<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FileService;
use App\Services\UserService;
use App\Support\Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function index(
        Request $request,
        Validator $validator,
        UserService $userService,
    ): View|RedirectResponse {
        if ($request->isMethod('post')) {
            $photo = $request->file('photo');

            $rules = [
                'maxsize'    => setting('filesize'),
                'extensions' => FileService::extensionList('media_extensions'),
                'minweight'  => 100,
            ];
            $validator->file($photo, $rules, ['photo' => __('validator.image_upload_failed')]);

            if ($validator->isValid()) {
                $userService->savePhoto($this->user, $photo);

                return redirect('profile')
                    ->with('success', __('users.photo_success_uploaded'));
            }

            return redirect('/pictures')
                ->withErrors($validator->getErrors())
                ->withInput();
        }

        $user = $this->user;

        return view('users/picture', compact('user'));
    }

    /**
     * Удаление фотографии
     */
    public function delete(Validator $validator, UserService $userService): RedirectResponse
    {
        if (! $this->user->picture) {
            $validator->addError(__('users.photo_not_exist'));
        }

        if (! $validator->isValid()) {
            return redirect('profile')
                ->withErrors($validator->getErrors());
        }

        $userService->deletePhoto($this->user);

        return redirect('profile')
            ->with('success', __('users.photo_success_deleted'));
    }
}
