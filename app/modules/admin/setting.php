<?php


if (isAdmin([101])) {

    switch ($action):


        ############################################################################################
        ##                       Форма изменения предложения и проблемы                           ##
        ############################################################################################
        case 'setoffer':

            echo '<div class="form">';
            echo '<form method="post" action="/admin/setting?act=editoffer&amp;uid='.$_SESSION['token'].'">';

            echo '<b>Предложения и проблемы </b><br>';

            echo '<input value="Изменить" type="submit"></form></div><br>';


            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                   Изменение настроек предложения и проблемы                            ##
        ############################################################################################
        case 'editoffer':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if ($_POST['postoffers'] != "" && $_POST['postcommoffers'] != "" && $_POST['addofferspoint'] != "") {

                    $dbr = DB::run() -> prepare("UPDATE `setting` SET `value`=? WHERE `name`=?;");

                    $dbr -> execute(intval($_POST['postoffers']), 'postoffers');
                    $dbr -> execute(intval($_POST['postcommoffers']), 'postcommoffers');
                    $dbr -> execute(intval($_POST['addofferspoint']), 'addofferspoint');
                    saveSetting();

                    setFlash('success', 'Настройки сайта успешно изменены!');
                    redirect("/admin/setting?act=setoffer");

                } else {
                    showError('Ошибка! Все поля настроек обязательны для заполнения!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting?act=setoffer">Вернуться</a><br>';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect('/');
}

view(setting('themes').'/foot');
