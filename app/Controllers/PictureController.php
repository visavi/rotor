<?php

namespace App\Controllers;

use App\Classes\Validator;
use Illuminate\Http\Request;
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
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function index(Request $request, Validator $validator): string
    {
        if ($request->isMethod('post')) {

            $token = check($request->input('token'));
            $photo = $request->file('photo');

            $validator->equal($token, $_SESSION['token'], ['photo' => 'Неверный идентификатор сессии, повторите действие!']);

            $rules = [
                'maxsize'   => setting('filesize'),
                'minweight' => 100,
            ];
            $validator->file($photo, $rules, ['photo' => 'Не удалось загрузить фотографию!']);

            if ($validator->isValid()) {

                //-------- Удаляем старую фотку и аватар ----------//
                if ($this->user->picture) {
                    deleteFile(HOME . $this->user->picture);
                    deleteFile(HOME . $this->user->avatar);

                    $this->user->picture = null;
                    $this->user->avatar = null;
                    $this->user->save();
                }

                //-------- Генерируем аватар ----------//
                $avatar = $this->user->uploadAvatarPath . '/' . uniqueName('png');
                $img = Image::make($photo);
                $img->fit(48);
                $img->save($avatar);

                $upload = $this->user->uploadFile($photo);

                $this->user->picture = $upload['path'];
                $this->user->avatar  = str_replace(HOME, '', $avatar);
                $this->user->save();

                setFlash('success', 'Фотография успешно загружена!');
                redirect('/profile');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $user = $this->user;
        return view('pages/picture', compact('user'));
    }

    /**
     * Удаление фотографии
     *
     * @param Request   $request
     * @param Validator $validator
     */
    public function delete(Request $request, Validator $validator): void
    {
        $token = check($request->input('token'));

        $validator->equal($token, $_SESSION['token'], ['photo' => 'Неверный идентификатор сессии, повторите действие!']);


        if (! $this->user->picture) {
            $validator->addError('Фотографии для удаления не существует!');
        }

        if ($validator->isValid()) {

            deleteFile(HOME . $this->user->picture);
            deleteFile(HOME . $this->user->avatar);

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
