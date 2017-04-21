<?php

if (! is_user()) {
    App::abort(403, 'Чтобы загружать фотографии необходимо авторизоваться');
}

switch ($act):
/**
 * Главная страница
 */
case 'index':

    if (Request::isMethod('post')) {

        $newName = uniqid();
        $token   = check(Request::input('token'));

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], ['photo' => 'Неверный идентификатор сессии, повторите действие!']);

        $handle = upload_image($_FILES['photo'], App::setting('filesize'), App::setting('fileupfoto'), $newName);
        if (! $handle) {
            $validation -> addError(['photo' => 'Не удалось загрузить фотографию!']);
        }

        if ($validation->run()) {

            //-------- Удаляем старую фотку и аватар ----------//
            $user = User::find(App::getUserId());

            if (!empty($user['picture'])){
                unlink_image('uploads/photos/', $user['picture']);
                unlink_image('uploads/avatars/', $user['avatar']);

                $user->picture = null;
                $user->avatar = null;
                $user->save();
            }

            //-------- Генерируем аватар ----------//
            $handle->process(HOME.'/uploads/photos/');
            $picture = $handle -> file_dst_name;

            $handle->file_new_name_body = $newName;
            $handle->image_resize = true;
            $handle->image_ratio_crop = true;
            $handle->image_y = 48;
            $handle->image_x = 48;
            $handle->image_watermark = false;
            $handle->image_convert = 'png';
            $handle->file_overwrite = true;

            $handle->process(HOME.'/uploads/avatars/');
            $avatar = $handle -> file_dst_name;

            if ($handle->processed) {

                $user = User::find(App::getUserId());
                $user->picture = $picture;
                $user->avatar = $avatar;
                $user->save();

                save_avatar();
            }

            App::setFlash('success', 'Фотография успешно загружена!');
            App::redirect('/profile');
        } else {
            App::setInput(Request::all());
            App::setFlash('danger', $validation->getErrors());
        }
    }

    $user = User::where('login', App::getUsername())->first();
    App::view('pages/picture', compact('user'));
break;


/**
 * Удаление фото и аватара
 */
case 'delete':

    $token = check(Request::input('token'));

    $validation = new Validation();
    $validation->addRule('equal', [$token, $_SESSION['token']], ['photo' => 'Неверный идентификатор сессии, повторите действие!']);

    $user = User::find(App::getUserId());
    if (! $user || ! $user['picture']) {
        $validation -> addError('Фотографии для удаления не существует!');
    }

    if ($validation->run()) {

        unlink_image('uploads/photos/', $user['picture']);
        unlink_image('uploads/avatars/', $user['avatar']);

        $user->picture = null;
        $user->avatar = null;
        $user->save();

        App::setFlash('success', 'Фотография успешно удалена!');
    } else {
        App::setFlash('danger', $validation->getErrors());
    }

    App::redirect('/profile');

break;
endswitch;
