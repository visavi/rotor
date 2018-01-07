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
            $validator->image($photo, $rules, ['photo' => 'Не удалось загрузить фотографию!']);

            if ($validator->isValid()) {

                //-------- Удаляем старую фотку и аватар ----------//
                if ($this->user->picture) {
                    deleteImage('uploads/photos/', $this->user->picture);
                    deleteImage('uploads/avatars/', $this->user->avatar);

                    $this->user->picture = null;
                    $this->user->avatar = null;
                    $this->user->save();
                }

                $name    = uniqid();
                $picture = $name . '.' . $photo->getClientOriginalExtension();
                $avatar  = $name . '.png';

                $img = Image::make($photo);
                $img->backup();

                $img->resize(setting('screensize'), setting('screensize'), function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                $img->insert(HOME.'/assets/img/images/watermark.png', 'bottom-right', 10, 10);
                $img->save(UPLOADS.'/photos/' . $picture);

                //-------- Генерируем аватар ----------//
                $img->reset();
                $img->resize(48, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $img->crop(48, 48);
                $img->save(UPLOADS.'/avatars/' . $avatar);

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
