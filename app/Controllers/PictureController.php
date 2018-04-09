<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use Intervention\Image\ImageManagerStatic as Image;

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

            $token = check(Request::input('token'));
            $photo = Request::file('photo');

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], ['photo' => 'Неверный идентификатор сессии, повторите действие!']);

            $rules = [
                'maxsize'   => setting('filesize'),
                'maxweight' => setting('fileupfoto'),
                'minweight' => 100,
            ];
            $validator->file($photo, $rules, ['photo' => 'Не удалось загрузить фотографию!']);

            if ($validator->isValid()) {

                //-------- Удаляем старую фотку и аватар ----------//
                if ($this->user->picture) {
                    deleteImage('uploads/photos/', $this->user->picture);
                    deleteImage('uploads/avatars/', $this->user->avatar);

                    $this->user->picture = null;
                    $this->user->avatar = null;
                    $this->user->save();
                }

                $picture = uploadImage($photo, UPLOADS.'/photos/');
                $avatar  = uniqueName('png');

                //-------- Генерируем аватар ----------//
                $img = Image::make($photo);
                $img->fit(48);
                $img->save(UPLOADS . '/avatars/' . $avatar);

                $this->user->picture = $picture;
                $this->user->avatar = $avatar;
                $this->user->save();

                setFlash('success', 'Фотография успешно загружена!');
                redirect('/profile');
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
