<?php


if (isAdmin([101])) {

    switch ($action):



        ############################################################################################
        ##                            Форма основных прочих настроек                              ##
        ############################################################################################
        case 'seteight':

            echo '<b>Прочее/Другое</b><br><hr>';

            echo '<div class="form">';
            echo '<form method="post" action="/admin/setting?act=editeight&amp;uid='.$_SESSION['token'].'">';


            $checked = ($setting['errorlog'] == 1) ? ' checked' : '';
            echo '<input name="errorlog" type="checkbox" value="1"'.$checked.'> Включить запись логов<br>';

            echo 'Ключевые слова (keywords):<br><input name="keywords" maxlength="250" value="'.$setting['keywords'].'"><br>';
            echo 'Краткое описание (description):<br><input name="description" maxlength="250" value="'.$setting['description'].'"><br>';
            echo 'Не сканируемые расширения (через запятую):<br><input name="nocheck" maxlength="100" value="'.$setting['nocheck'].'"><br>';
            echo 'Максимальное время бана (суток):<br><input name="maxbantime" maxlength="2" value="'.round($setting['maxbantime'] / 1440).'"><br>';
            echo 'Название денег:<br><input name="moneyname" maxlength="100" value="'.$setting['moneyname'].'"><br>';
            echo 'Название баллов:<br><input name="scorename" maxlength="100" value="'.$setting['scorename'].'"><br>';
            echo 'Статусы пользователей:<br><input name="statusname" maxlength="100" value="'.$setting['statusname'].'"><br>';
            echo 'Статус по умолчанию:<br><input name="statusdef" maxlength="20" value="'.$setting['statusdef'].'"><br>';

            $checked = ($setting['addbansend'] == 1) ? ' checked' : '';
            echo '<input name="addbansend" type="checkbox" value="1"'.$checked.'> Объяснение из бана<br>';

            echo 'Curl прокси (ip:port):<br><input name="proxy" maxlength="50" value="'.$setting['proxy'].'"><br>';

            echo '<input value="Изменить" type="submit"></form></div><br>';

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                 Форма прочих настроек                                  ##
        ############################################################################################
        case 'editeight':

            $uid = check($_GET['uid']);
            $errorlog = (empty($_POST['errorlog'])) ? 0 : 1;
            $addbansend = (empty($_POST['addbansend'])) ? 0 : 1;

            if ($uid == $_SESSION['token']) {
                if ($_POST['keywords'] != "" && $_POST['description'] != "" && $_POST['nocheck'] != "" && $_POST['maxbantime'] != "" && $_POST['moneyname'] != "" && $_POST['scorename'] != "" && $_POST['statusname'] != "" && $_POST['statusdef'] != "") {
                    $dbr = DB::run() -> prepare("UPDATE `setting` SET `value`=? WHERE `name`=?;");
                    $dbr -> execute(check($_POST['keywords']), 'keywords');
                    $dbr -> execute(check($_POST['description']), 'description');
                    $dbr -> execute(check($_POST['nocheck']), 'nocheck');
                    $dbr -> execute(intval($_POST['maxbantime'] * 1440), 'maxbantime');
                    $dbr -> execute(check($_POST['moneyname']), 'moneyname');
                    $dbr -> execute(check($_POST['scorename']), 'scorename');
                    $dbr -> execute(check($_POST['statusname']), 'statusname');
                    $dbr -> execute(check($_POST['statusdef']), 'statusdef');
                    $dbr -> execute($errorlog, 'errorlog');
                    $dbr -> execute($addbansend, 'addbansend');
                    $dbr -> execute(check($_POST['proxy']), 'proxy');

                    saveSetting();

                    setFlash('success', 'Настройки сайта успешно изменены!');
                    redirect("/admin/setting?act=seteight");

                } else {
                    showError('Ошибка! Все поля настроек обязательны для заполнения!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting?act=seteight">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                               Форма изменения кеширования                              ##
        ############################################################################################
        /**
        * case "setnine":
        *
        * echo '<b>Настройки кэширования</b><br><hr>';
        *
        * echo '<div class="form">';
        * echo '<form method="post" action="/admin/setting?act=editnine&amp;uid='.$_SESSION['token'].'">';
        *
        * echo 'Кеш в юзерлисте: <br><input name="userlistcache" maxlength="2" value="'.$setting['userlistcache'].'"><br>';
        * echo 'Рейтинг репутации: <br><input name="avtorlistcache" maxlength="2" value="'.$setting['avtorlistcache'].'"><br>';
        * echo 'Рейтинг толстосумов: <br><input name="raitinglistcache" maxlength="2" value="'.$setting['raitinglistcache'].'"><br>';
        * echo 'Поиск пользователей: <br><input name="usersearchcache" maxlength="2" value="'.$setting['usersearchcache'].'"><br>';
        * echo 'Рейтинг долгожителей: <br><input name="lifelistcache" maxlength="2" value="'.$setting['lifelistcache'].'"><br>';
        * echo 'Рейтинг вкладчиков: <br><input name="vkladlistcache" maxlength="2" value="'.$setting['vkladlistcache'].'"><br>';
        * echo 'Листинг администрации: <br><input name="adminlistcache" maxlength="2" value="'.$setting['adminlistcache'].'"><br>';
        * echo 'Кеширование при регистрации: <br><input name="regusercache" maxlength="2" value="'.$setting['regusercache'].'"><br>';
        * echo 'Популярныe скины: <br><input name="themescache" maxlength="2" value="'.$setting['themescache'].'"><br>';
        *
        * echo '<br><input value="Изменить" type="submit"></form></div>';
        *
        * echo '<br>* Все настройки измеряются в часах<br>';
        * echo '<br><i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting">Вернуться</a><br>';
        * break;
        *
        * ############################################################################################
        * ##                                  Изменение кэширования                                 ##
        * ############################################################################################
        * case "editnine":
        *
        * $uid = check($_GET['uid']);
        *
        * if ($uid==$_SESSION['token']){
        * if ($_POST['userlistcache']!="" && $_POST['avtorlistcache']!="" && $_POST['raitinglistcache']!="" &&  $_POST['usersearchcache']!="" && $_POST['lifelistcache']!="" && $_POST['vkladlistcache']!="" && $_POST['adminlistcache']!="" && $_POST['regusercache']!="" && $_POST['themescache']!=""){
        *
        * $dbr = DB::run()->prepare("UPDATE setting SET value=? WHERE name=?;");
        * $dbr->execute(intval($_POST['userlistcache']), 'userlistcache');
        * $dbr->execute(intval($_POST['avtorlistcache']), 'avtorlistcache');
        * $dbr->execute(intval($_POST['raitinglistcache']), 'raitinglistcache');
        * $dbr->execute(intval($_POST['usersearchcache']), 'usersearchcache');
        * $dbr->execute(intval($_POST['lifelistcache']), 'lifelistcache');
        * $dbr->execute(intval($_POST['adminlistcache']), 'adminlistcache');
        * $dbr->execute(intval($_POST['regusercache']), 'regusercache');
        * $dbr->execute(intval($_POST['themescache']), 'themescache');
        *
        * saveSetting();
        *
        * setFlash('success', 'Настройки сайта успешно изменены!');
        * redirect("/admin/setting");
        *
        * } else {showError('Ошибка! Все поля настроек обязательны для заполнения!');}
        * } else {showError('Ошибка! Неверный идентификатор сессии, повторите действие!');}
        *
        * echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting?act=setnine">Вернуться</a><br>';
        * break;
        */

        ############################################################################################
        ##                               Форма изменения безопасности                             ##
        ############################################################################################
        case 'setten':

            echo '<b>Настройки безопасности</b><br><hr>';

            echo '<div class="form">';
            echo '<form method="post" action="/admin/setting?act=editten&amp;uid='.$_SESSION['token'].'">';

            echo '<b>Captcha</b><br>';
            echo 'Допустимые символы [a-z0-9]:<br><input name="captcha_symbols" maxlength="26" value="'.$setting['captcha_symbols'].'"><br>';

            echo 'Максимальное количество символов [4-6]:<br><input name="captcha_maxlength" maxlength="1" value="'.$setting['captcha_maxlength'].'"><br>';

            echo 'Поворот букв [0-30]:<br><input name="captcha_angle" maxlength="2" value="'.$setting['captcha_angle'].'"><br>';

            echo 'Амплитуда колебаний символов [0-10]:<br><input name="captcha_offset" maxlength="2" value="'.$setting['captcha_offset'].'"><br>';

            $checked = ($setting['captcha_distortion']) ? ' checked' : '';
            echo '<input name="captcha_distortion" type="checkbox" value="1"'.$checked.'> Искажение<br>';

            $checked = ($setting['captcha_interpolation']) ? ' checked' : '';
            echo '<input name="captcha_interpolation" type="checkbox" value="1"'.$checked.'> Размытие<br>';

            echo '<input value="Изменить" type="submit"></form></div><br>';

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                               Изменение безопасности                                   ##
        ############################################################################################
        case 'editten':

            $uid = check($_GET['uid']);
            $captcha_symbols = check(strtolower($_POST['captcha_symbols']));
            $captcha_maxlength = intval(strtolower($_POST['captcha_maxlength']));
            $captcha_angle = intval(strtolower($_POST['captcha_angle']));
            $captcha_offset = intval(strtolower($_POST['captcha_offset']));
            $captcha_distortion = (empty($_POST['captcha_distortion'])) ? 0 : 1;
            $captcha_interpolation = (empty($_POST['captcha_interpolation'])) ? 0 : 1;

            if ($uid == $_SESSION['token']) {

                if (preg_match('|^[a-z0-9]+$|', $captcha_symbols)) {
                    if (preg_match('|^[4-6]{1}+$|', $captcha_maxlength)) {
                        if (preg_match('|^[0-9]{1,}+$|', $captcha_offset)) {

                            $dbr = DB::run() -> prepare("UPDATE `setting` SET `value`=? WHERE `name`=?;");
                            $dbr -> execute($captcha_symbols, 'captcha_symbols');
                            $dbr -> execute($captcha_maxlength, 'captcha_maxlength');
                            $dbr -> execute($captcha_angle, 'captcha_angle');
                            $dbr -> execute($captcha_offset, 'captcha_offset');
                            $dbr -> execute($captcha_distortion, 'captcha_distortion');
                            $dbr -> execute($captcha_interpolation, 'captcha_interpolation');
                            saveSetting();

                            setFlash('success', 'Настройки сайта успешно изменены!');
                            redirect("/admin/setting?act=setten");

                        } else {
                            showError('Ошибка! Амплитуда колебаний может быть от 0 до 8!');
                        }
                    } else {
                        showError('Ошибка! Максимальное количество символов может быть от 4 до 6!');
                    }
                } else {
                    showError('Ошибка! Недопустимые символы в captcha!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting?act=setten">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                            Форма изменения стоимости и цен                             ##
        ############################################################################################
        case 'seteleven':

            echo '<b>Стоимость и цены</b><br><hr>';

            echo '<div class="form">';
            echo '<form method="post" action="/admin/setting?act=editeleven&amp;uid='.$_SESSION['token'].'">';

            echo 'Актива для перечисления денег: <br><input name="sendmoneypoint" maxlength="4" value="'.$setting['sendmoneypoint'].'"><br>';
            echo 'Актива для изменения репутации: <br><input name="editratingpoint" maxlength="4" value="'.$setting['editratingpoint'].'"><br>';
            echo 'Актива для изменения тем форума: <br><input name="editforumpoint" maxlength="4" value="'.$setting['editforumpoint'].'"><br>';
            echo 'Актива для скрытия рекламы: <br><input name="advertpoint" maxlength="4" value="'.$setting['advertpoint'].'"><br><hr>';

            echo 'Актива для изменения статуса: <br><input name="editstatuspoint" maxlength="4" value="'.$setting['editstatuspoint'].'"><br>';
            echo 'Стоимость изменения статуса: <br><input name="editstatusmoney" maxlength="9" value="'.$setting['editstatusmoney'].'"><br><hr>';

            echo 'Ежедневный бонус: <br><input name="bonusmoney" maxlength="10" value="'.$setting['bonusmoney'].'"><br>';
            echo 'Денег за регистрацию: <br><input name="registermoney" maxlength="10" value="'.$setting['registermoney'].'"><br><hr>';

            echo '<input value="Изменить" type="submit"></form></div><br>';

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                               Изменение стоимости и цен                                ##
        ############################################################################################
        case 'editeleven':

            $uid = check($_GET['uid']);
            if ($uid == $_SESSION['token']) {
                if ($_POST['sendmoneypoint'] != "" && $_POST['editratingpoint'] != "" && $_POST['editforumpoint'] != "" && $_POST['editstatuspoint'] != "" && $_POST['editstatusmoney'] != "" && $_POST['bonusmoney'] != "" && $_POST['registermoney'] != "") {

                    $dbr = DB::run() -> prepare("UPDATE `setting` SET `value`=? WHERE `name`=?;");
                    $dbr -> execute(intval($_POST['sendmoneypoint']), 'sendmoneypoint');
                    $dbr -> execute(intval($_POST['editratingpoint']), 'editratingpoint');
                    $dbr -> execute(intval($_POST['editforumpoint']), 'editforumpoint');
                    $dbr -> execute(intval($_POST['advertpoint']), 'advertpoint');
                    $dbr -> execute(intval($_POST['editstatuspoint']), 'editstatuspoint');
                    $dbr -> execute(intval($_POST['editstatusmoney']), 'editstatusmoney');
                    $dbr -> execute(intval($_POST['bonusmoney']), 'bonusmoney');
                    $dbr -> execute(intval($_POST['registermoney']), 'registermoney');

                    saveSetting();

                    setFlash('success', 'Настройки сайта успешно изменены!');
                    redirect("/admin/setting?act=seteleven");

                } else {
                    showError('Ошибка! Все поля настроек обязательны для заполнения!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting?act=seteleven">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                            Форма изменения рекламы на сайте                            ##
        ############################################################################################
        case 'setadv':

            echo '<b>Реклама на сайте</b><br><hr>';

            echo '<div class="form">';
            echo '<form method="post" action="/admin/setting?act=editadv&amp;uid='.$_SESSION['token'].'">';

            echo 'Кол. рекламных ссылок: <br><input name="rekusershow" maxlength="2" value="'.$setting['rekusershow'].'"><br>';
            echo 'Цена рекламы: <br><input name="rekuserprice" maxlength="7" value="'.$setting['rekuserprice'].'"><br>';
            echo 'Цена опций (жирность, цвет): <br><input name="rekuseroptprice" maxlength="7" value="'.$setting['rekuseroptprice'].'"><br>';
            echo 'Срок рекламы (часов): <br><input name="rekusertime" maxlength="3" value="'.$setting['rekusertime'].'"><br>';
            echo 'Максимум ссылок разрешено: <br><input name="rekusertotal" maxlength="3" value="'.$setting['rekusertotal'].'"><br>';
            echo 'Листинг всех ссылок: <br><input name="rekuserpost" maxlength="2" value="'.$setting['rekuserpost'].'"><br>';

            echo '<input value="Изменить" type="submit"></form></div><br>';

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                               Изменение настроек рекламы                               ##
        ############################################################################################
        case 'editadv':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if ($_POST['rekusershow'] != "" && $_POST['rekuserprice'] != "" && $_POST['rekuseroptprice'] != "" && $_POST['rekusertime'] != "" && $_POST['rekusertotal'] != "" && $_POST['rekuserpost'] != "") {
                    $dbr = DB::run() -> prepare("UPDATE `setting` SET `value`=? WHERE `name`=?;");
                    $dbr -> execute(intval($_POST['rekusershow']), 'rekusershow');
                    $dbr -> execute(intval($_POST['rekuserprice']), 'rekuserprice');
                    $dbr -> execute(intval($_POST['rekuseroptprice']), 'rekuseroptprice');
                    $dbr -> execute(intval($_POST['rekusertime']), 'rekusertime');
                    $dbr -> execute(intval($_POST['rekusertotal']), 'rekusertotal');
                    $dbr -> execute(intval($_POST['rekuserpost']), 'rekuserpost');

                    saveSetting();

                    setFlash('success', 'Настройки сайта успешно изменены!');
                    redirect("/admin/setting?act=setadv");

                } else {
                    showError('Ошибка! Все поля настроек обязательны для заполнения!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting?act=setadv">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                         Форма изменения настройки изображений                          ##
        ############################################################################################
        case 'setimage':

            echo '<b>Загрузка изображений</b><br><hr>';

            echo '<div class="form">';
            echo '<form method="post" action="/admin/setting?act=editimage&amp;uid='.$_SESSION['token'].'">';

            echo 'Максимальный вес фото (Mb) (Ограничение: '.ini_get('upload_max_filesize').'):<br><input name="filesize" maxlength="6" value="'.round($setting['filesize'] / 1024 / 1024).'"><br>';
            echo 'Максимальный размер фото (px):<br><input name="fileupfoto" maxlength="6" value="'.$setting['fileupfoto'].'"><br>';
            echo 'Уменьшение фото при загрузке (px):<br><input name="screensize" maxlength="4" value="'.$setting['screensize'].'"><br>';

            echo 'Размер скриншотов (px):<br><input name="previewsize" maxlength="3" value="'.$setting['previewsize'].'"><br>';

            $checked = ($setting['copyfoto'] == 1) ? ' checked' : '';
            echo '<input name="copyfoto" type="checkbox" value="1"'.$checked.'> Наложение копирайта<br>';
            echo '<img src="/assets/img/images/watermark.png" alt="watermark" title="'.siteUrl().'/assets/img/images/watermark.png"><br>';

            echo '<input value="Изменить" type="submit"></form></div><br>';

            echo 'Не устанавливайте слишком большие размеры веса и размера изображений, так как может не хватить процессорного времени для обработки<br>';
            echo 'При изменении размера скриншота, необходимо вручную очистить кэш изображений<br><br>';



            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                             Изменение настроек изображения                             ##
        ############################################################################################
        case 'editimage':

            $uid = check($_GET['uid']);
            $copyfoto = (empty($_POST['copyfoto'])) ? 0 : 1;

            if ($uid == $_SESSION['token']) {
                if ($_POST['filesize'] != "" && $_POST['fileupfoto'] != "" && $_POST['screensize'] != "" && $_POST['previewsize'] != "") {

                    $dbr = DB::run() -> prepare("UPDATE `setting` SET `value`=? WHERE `name`=?;");

                    $dbr -> execute(intval($_POST['filesize'] * 1024 * 1024), 'filesize');
                    $dbr -> execute(intval($_POST['fileupfoto']), 'fileupfoto');
                    $dbr -> execute(intval($_POST['screensize']), 'screensize');
                    $dbr -> execute(intval($_POST['previewsize']), 'previewsize');
                    $dbr -> execute($copyfoto, 'copyfoto');
                    saveSetting();

                    setFlash('success', 'Настройки сайта успешно изменены!');
                    redirect("/admin/setting?act=setimage");

                } else {
                    showError('Ошибка! Все поля настроек обязательны для заполнения!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting?act=setimage">Вернуться</a><br>';
        break;


        ############################################################################################
        ##                         Форма изменения настройки смайлов                              ##
        ############################################################################################
        case 'setsmile':

            echo '<div class="form">';
            echo '<form method="post" action="/admin/setting?act=editsmile&amp;uid='.$_SESSION['token'].'">';

            echo '<b>Смайлы</b><br>';
            echo 'Максимальный вес смайла (kb):<br><input name="smilemaxsize" maxlength="3" value="'.round($setting['smilemaxsize'] / 1024).'"><br>';
            echo 'Максимальный размер смайла (px):<br><input name="smilemaxweight" maxlength="3" value="'.$setting['smilemaxweight'].'"><br>';
            echo 'Минимальный размер смайла (px):<br><input name="smileminweight" maxlength="3" value="'.$setting['smileminweight'].'"><br>';

            echo '<input value="Изменить" type="submit"></form></div><br>';


            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                             Изменение настроек смайлов                                 ##
        ############################################################################################
        case 'editsmile':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if ($_POST['smilemaxsize'] != "" && $_POST['smilemaxweight'] != "" && $_POST['smileminweight'] != "") {

                    $dbr = DB::run() -> prepare("UPDATE `setting` SET `value`=? WHERE `name`=?;");

                    $dbr -> execute(intval($_POST['smilemaxsize'] * 1024), 'smilemaxsize');
                    $dbr -> execute(intval($_POST['smilemaxweight']), 'smilemaxweight');
                    $dbr -> execute(intval($_POST['smileminweight']), 'smileminweight');
                    saveSetting();

                    setFlash('success', 'Настройки сайта успешно изменены!');
                    redirect("/admin/setting?act=setsmile");

                } else {
                    showError('Ошибка! Все поля настроек обязательны для заполнения!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting?act=setsmile">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                       Форма изменения предложения и проблемы                           ##
        ############################################################################################
        case 'setoffer':

            echo '<div class="form">';
            echo '<form method="post" action="/admin/setting?act=editoffer&amp;uid='.$_SESSION['token'].'">';

            echo '<b>Предложения и проблемы </b><br>';
            echo 'Предложений на страницу:<br><input name="postoffers" maxlength="2" value="'.$setting['postoffers'].'"><br>';
            echo 'Комментариев на страницу:<br><input name="postcommoffers" maxlength="2" value="'.$setting['postcommoffers'].'"><br>';
            echo 'Актива для создания предложения или проблемы: <br><input name="addofferspoint" maxlength="4" value="'.$setting['addofferspoint'].'"><br>';
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
