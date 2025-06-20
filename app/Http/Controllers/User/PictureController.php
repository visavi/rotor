<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Classes\Validator;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Intervention\Image\ImageManager;

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
        ImageManager $imageManager,
    ): View|RedirectResponse {
        if ($request->isMethod('post')) {
            $photo = $request->file('photo');

            $validator->equal($request->input('_token'), csrf_token(), ['photo' => __('validator.token')]);

            $rules = [
                'maxsize'    => setting('filesize'),
                'extensions' => explode(',', setting('image_extensions')),
                'minweight'  => 100,
            ];
            $validator->file($photo, $rules, ['photo' => __('validator.image_upload_failed')]);

            if ($validator->isValid()) {
                // -------- Удаляем старое фото и аватар ----------//
                if ($this->user->picture) {
                    deleteFile(public_path($this->user->picture));
                    deleteFile(public_path($this->user->avatar));

                    $this->user->picture = null;
                    $this->user->avatar = null;
                    $this->user->save();
                }

                // -------- Загружаем фото ----------//
                $file = $this->user->uploadFile($photo, false);

                // -------- Генерируем аватар -------//
                $image = $imageManager->read($photo);
                $image->coverDown(64, 64);

                $extension = strtolower($photo->getClientOriginalExtension());
                $avatar = $this->user->uploadAvatarPath . '/' . uniqueName($extension);
                $image->save(public_path($avatar));

                $this->user->picture = $file['path'];
                $this->user->avatar = $avatar;
                $this->user->save();

                setFlash('success', __('users.photo_success_uploaded'));

                return redirect('profile');
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
    public function delete(Request $request, Validator $validator): RedirectResponse
    {
        $validator->equal($request->input('_token'), csrf_token(), ['photo' => __('validator.token')]);

        if (! $this->user->picture) {
            $validator->addError(__('users.photo_not_exist'));
        }

        if ($validator->isValid()) {
            deleteFile(public_path($this->user->picture));
            deleteFile(public_path($this->user->avatar));

            $this->user->picture = null;
            $this->user->avatar = null;
            $this->user->save();

            setFlash('success', __('users.photo_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('profile');
    }
}
