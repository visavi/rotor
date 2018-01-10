<?php
view(setting('themes').'/index');

$act = check(Request::input('act', 'index'));
$id  = int(Request::input('id', 0));

if (isAdmin([101, 102])) {
    //show_title('Управление новостями');

switch ($action):


############################################################################################
##                                    Удаление новостей                                   ##
############################################################################################
case 'del':

    $token = check(Request::input('token'));
    $del = intar(Request::input('del'));
    $page  = int(Request::input('page', 1));

    if ($token == $_SESSION['token']) {
        if (!empty($del)) {
            if (is_writable(UPLOADS.'/news')){

                $del = implode(',', $del);

                $querydel = DB::select("SELECT `image` FROM `news` WHERE `id` IN (".$del.");");
                $arr_image = $querydel->fetchAll();

                if (count($arr_image)>0){
                    foreach ($arr_image as $delete){
                        deleteImage('uploads/news/', $delete['image']);
                    }
                }

                DB::delete("DELETE FROM `news` WHERE `id` IN (".$del.");");
                DB::delete("DELETE FROM `comments` WHERE relate_type = 'News' AND `relate_id` IN (".$del.");");

                setFlash('success', 'Выбранные новости успешно удалены!');
                redirect("/admin/news?page=$page");

            } else {
                showError('Ошибка! Не установлены атрибуты доступа на дирекоторию с изображениями!');
            }
        } else {
            showError('Ошибка! Отсутствуют выбранные новости!');
        }
    } else {
        showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/news?page='.$page.'">Вернуться</a><br>';
break;

endswitch;

echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect('/');
}

view(setting('themes').'/foot');
