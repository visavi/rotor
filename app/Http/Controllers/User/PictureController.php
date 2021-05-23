<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Classes\Validator;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Intervention\Image\ImageManagerStatic as Image;

class PictureController extends Controller
{
    /**
     * @var User
     */
    public $user;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! $this->user = getUser()) {
            abort(403, __('main.not_authorized'));
        }
    }

    /**
     * Главная страница
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View
     */
    public function index(Request $request, Validator $validator): View
    {
        if ($request->isMethod('post')) {
            $photo = $request->file('photo');

            $validator->equal($request->input('_token'), csrf_token(), ['photo' => __('validator.token')]);

            $rules = [
                'maxsize'   => setting('filesize'),
                'minweight' => 100,
            ];
            $validator->file($photo, $rules, ['photo' => __('validator.image_upload_failed')]);

            if ($validator->isValid()) {
                //-------- Удаляем старую фотку и аватар ----------//
                if ($this->user->picture) {
                    deleteFile(HOME . $this->user->picture);
                    deleteFile(HOME . $this->user->avatar);

                    $this->user->picture = null;
                    $this->user->avatar  = null;
                    $this->user->save();
                }

                //-------- Генерируем аватар ----------//
                $avatar = $this->user->uploadAvatarPath . '/' . uniqueName('png');
                $img = Image::make($photo);
                $img->fit(64);
                $img->save($avatar);

                $file = $this->user->uploadFile($photo, false);

                $this->user->picture = $file['path'];
                $this->user->avatar  = str_replace(HOME, '', $avatar);
                $this->user->save();

                setFlash('success', __('users.photo_success_uploaded'));
                redirect('/profile');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $user = $this->user;
        return view('users/picture', compact('user'));
    }

    /**
     * Удаление фотографии
     *
     * @param Request   $request
     * @param Validator $validator
     */
    public function delete(Request $request, Validator $validator): void
    {
        $validator->equal($request->input('_token'), csrf_token(), ['photo' => __('validator.token')]);

        if (! $this->user->picture) {
            $validator->addError(__('users.photo_not_exist'));
        }

        if ($validator->isValid()) {
            deleteFile(HOME . $this->user->picture);
            deleteFile(HOME . $this->user->avatar);

            $this->user->picture = null;
            $this->user->avatar  = null;
            $this->user->save();

            setFlash('success', __('users.photo_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/profile');
    }
}
