<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;

class PictureController extends BaseController
{
    public $user;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! $this->user = getUser()) {
            abort(403, 'Чтобы загружать фотографии необходимо авторизоваться!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        if (Request::isMethod('post')) {

            $newName = uniqid();
            $token   = check(Request::input('token'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], ['photo' => 'Неверный идентификатор сессии, повторите действие!']);

            $handle = uploadImage($_FILES['photo'], setting('filesize'), setting('fileupfoto'), $newName);
            if (! $handle) {
                $validator->addError(['photo' => 'Не удалось загрузить фотографию!']);
            }

            if ($validator->isValid()) {
                //-------- Удаляем старую фотку и аватар ----------//

                if ($this->user->picture) {
                    deleteImage('uploads/photos/', $this->user->picture);
                    deleteImage('uploads/avatars/', $this->user->avatar);

                    $this->user->picture = null;
                    $this->user->avatar = null;
                    $this->user->save();
                }

                //-------- Генерируем аватар ----------//
                $handle->process(HOME.'/uploads/photos/');

                if ($handle->processed) {
                    $picture = $handle->file_dst_name;

                    $handle->file_new_name_body = $newName;
                    $handle->image_resize = true;
                    $handle->image_ratio_crop = true;
                    $handle->image_y = 48;
                    $handle->image_x = 48;
                    $handle->image_watermark = false;
                    $handle->image_convert = 'png';
                    $handle->file_overwrite = true;

                    $handle->process(HOME . '/uploads/avatars/');
                    $avatar = $handle->file_dst_name;

                    if ($handle->processed) {

                        $this->user->picture = $picture;
                        $this->user->avatar = $avatar;
                        $this->user->save();
                    }

                    setFlash('success', 'Фотография успешно загружена!');
                    redirect('/profile');
                } else {
                    $validator->addError(['photo' => $handle->error]);
                }
            }

            setInput(Request::all());
            setFlash('danger', $validator->getErrors());
        }

        $user = $this->user;
        return view('pages/picture', compact('user'));
    }

    /**
     * Удаление фотографии
     */
    public function delete()
    {
        $token = check(Request::input('token'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], ['photo' => 'Неверный идентификатор сессии, повторите действие!']);


        if (! $this->user->picture) {
            $validator->addError('Фотографии для удаления не существует!');
        }

        if ($validator->isValid()) {

            deleteImage('uploads/photos/', $this->user->picture);
            deleteImage('uploads/avatars/', $this->user->avatar);

            $this->user->picture = null;
            $this->user->avatar = null;
            $this->user->save();

            setFlash('success', 'Фотография успешно удалена!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/profile');
    }
}
