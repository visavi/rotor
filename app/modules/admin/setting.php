<?php


if (isAdmin([101])) {

    switch ($action):


        ############################################################################################
        ##                     Форма изменения форума, галереи и объявлений                       ##
        ############################################################################################
        case 'setfour':

            echo '<b>Настройки форума, галереи и объявлений</b><br><hr>';

            echo '<div class="form">';
            echo '<form method="post" action="/admin/setting?act=editfour&amp;uid='.$_SESSION['token'].'">';
            echo '<b>Форум</b><br>';


            echo '<input value="Изменить" type="submit"></form></div><br>';
            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                     Изменение в форуме, галерее и объявлениях                          ##
        ############################################################################################
        case 'editfour':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if ($_POST['forumpost'] != "" && $_POST['forumtem'] != "" && $_POST['forumtextlength'] != "" && $_POST['forumloadsize'] != "" && $_POST['forumextload'] != "" && $_POST['forumloadpoints'] != "" && $_POST['fotolist'] != "" && $_POST['postgallery'] != "" && $_POST['photogroup'] != "") {
                    $dbr = DB::run() -> prepare("UPDATE `setting` SET `value`=? WHERE `name`=?;");
                    $dbr -> execute(intval($_POST['forumpost']), 'forumpost');
                    $dbr -> execute(intval($_POST['forumtem']), 'forumtem');
                    $dbr -> execute(intval($_POST['forumtextlength']), 'forumtextlength');
                    $dbr -> execute(intval($_POST['forumloadsize'] * 1024 * 1024), 'forumloadsize');
                    $dbr -> execute(check($_POST['forumextload']), 'forumextload');
                    $dbr -> execute(intval($_POST['forumloadpoints']), 'forumloadpoints');
                    $dbr -> execute(intval($_POST['fotolist']), 'fotolist');
                    $dbr -> execute(intval($_POST['postgallery']), 'postgallery');
                    $dbr -> execute(intval($_POST['photogroup']), 'photogroup');

                    saveSetting();

                    setFlash('success', 'Настройки сайта успешно изменены!');
                    redirect("/admin/setting?act=setfour");

                } else {
                    showError('Ошибка! Все поля настроек обязательны для заполнения!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting?act=setfour">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                    Форма изменения закладок, голосований и привата                     ##
        ############################################################################################
        case 'setfive':

            echo '<b>Настройки закладок, голосований и привата</b><br><hr>';

            echo '<div class="form">';
            echo '<form method="post" action="/admin/setting?act=editfive&amp;uid='.$_SESSION['token'].'">';

            echo 'Кол. писем во входящих:<br><input name="limitmail" maxlength="3" value="'.$setting['limitmail'].'"><br>';
            echo 'Кол. писем в отправленных:<br><input name="limitoutmail" maxlength="3" value="'.$setting['limitoutmail'].'"><br>';
            echo 'Писем в привате на стр.:<br><input name="privatpost" maxlength="2" value="'.$setting['privatpost'].'"><br>';
            echo 'Порог выключения защитной картинки:<br><input name="privatprotect" maxlength="4" value="'.$setting['privatprotect'].'"><br>';
            echo 'Листинг в контакт-листе:<br><input name="contactlist" maxlength="2" value="'.$setting['contactlist'].'"><br>';
            echo 'Листинг в игнор-листе:<br><input name="ignorlist" maxlength="2" value="'.$setting['ignorlist'].'"><br>';
            echo 'Максимальное кол. в контакт-листе:<br><input name="limitcontact" maxlength="2" value="'.$setting['limitcontact'].'"><br>';
            echo 'Максимальное кол. в игнор-листе:<br><input name="limitignore" maxlength="2" value="'.$setting['limitignore'].'"><br>';
            echo 'Кол-во голосований на стр.:<br><input name="allvotes" maxlength="2" value="'.$setting['allvotes'].'"><br>';

            echo '<input value="Изменить" type="submit"></form></div><br>';
            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                  Изменение в закладках, голосованиях и привате                         ##
        ############################################################################################
        case 'editfive':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if ($_POST['limitmail'] != "" && $_POST['limitoutmail'] != "" && $_POST['privatpost'] != "" && $_POST['privatprotect'] != "" && $_POST['contactlist'] != "" && $_POST['ignorlist'] != "" && $_POST['limitcontact'] != "" && $_POST['limitignore'] != "" && $_POST['allvotes'] != "") {
                    $dbr = DB::run() -> prepare("UPDATE `setting` SET `value`=? WHERE `name`=?;");
                    $dbr -> execute(intval($_POST['limitmail']), 'limitmail');
                    $dbr -> execute(intval($_POST['limitoutmail']), 'limitoutmail');
                    $dbr -> execute(intval($_POST['privatpost']), 'privatpost');
                    $dbr -> execute(intval($_POST['privatprotect']), 'privatprotect');
                    $dbr -> execute(intval($_POST['contactlist']), 'contactlist');
                    $dbr -> execute(intval($_POST['ignorlist']), 'ignorlist');
                    $dbr -> execute(intval($_POST['limitcontact']), 'limitcontact');
                    $dbr -> execute(intval($_POST['limitignore']), 'limitignore');
                    $dbr -> execute(intval($_POST['allvotes']), 'allvotes');

                    saveSetting();

                    setFlash('success', 'Настройки сайта успешно изменены!');
                    redirect("/admin/setting?act=setfive");

                } else {
                    showError('Ошибка! Все поля настроек обязательны для заполнения!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting?act=setfive">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                             Форма изменения загруз-центра                              ##
        ############################################################################################
        case 'setload':

            echo '<b>Настройки загруз-центра</b><br><hr>';

            echo '<div class="form">';
            echo '<form method="post" action="/admin/setting?act=editload&amp;uid='.$_SESSION['token'].'">';

            echo 'Файлов в загрузках:<br><input name="downlist" maxlength="2" value="'.$setting['downlist'].'"><br>';
            echo 'Комментариев в загрузках:<br><input name="downcomm" maxlength="2" value="'.$setting['downcomm'].'"><br>';

            echo 'Просмотр архивов на стр.:<br><input name="ziplist" maxlength="2" value="'.$setting['ziplist'].'"><br>';
            echo 'Максимальный вес файла (Mb):<br><input name="fileupload" maxlength="6" value="'.round($setting['fileupload'] / 1024 / 1024).'"><br>';

            echo 'Максимальный вес скриншота (Mb) (Ограничение: '.ini_get('upload_max_filesize').'):<br><input name="screenupload" maxlength="6" value="'.round($setting['screenupload'] / 1024 / 1024).'"><br>';
            echo 'Максимальный размер скриншота (px):<br><input name="screenupsize" maxlength="6" value="'.$setting['screenupsize'].'"><br>';
            echo 'Допустимые расширения файлов:<br><textarea cols="25" rows="5" name="allowextload">'.$setting['allowextload'].'</textarea><br>';

            $checked = ($setting['downupload'] == 1) ? ' checked' : '';
            echo '<input name="downupload" type="checkbox" value="1"'.$checked.'> Разрешать загружать файлы пользователям<br>';

            echo '<input value="Изменить" type="submit"></form></div><br>';
            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                Изменение в загруз-центре                               ##
        ############################################################################################
        case 'editload':

            $uid = check($_GET['uid']);
            $downupload = (empty($_POST['downupload'])) ? 0 : 1;

            if ($uid == $_SESSION['token']) {
                if ($_POST['downlist'] != "" && $_POST['downcomm'] != "" && $_POST['ziplist'] != "" && $_POST['fileupload'] != "" && $_POST['screenupload'] != "" && $_POST['screenupsize'] != "" && $_POST['allowextload'] != "") {
                    $dbr = DB::run() -> prepare("UPDATE `setting` SET `value`=? WHERE `name`=?;");
                    $dbr -> execute(intval($_POST['downlist']), 'downlist');
                    $dbr -> execute(intval($_POST['downcomm']), 'downcomm');
                    $dbr -> execute(intval($_POST['ziplist']), 'ziplist');
                    $dbr -> execute(intval($_POST['fileupload'] * 1024 * 1024), 'fileupload');
                    $dbr -> execute(intval($_POST['screenupload'] * 1024 * 1024), 'screenupload');
                    $dbr -> execute(intval($_POST['screenupsize']), 'screenupsize');
                    $dbr -> execute(check($_POST['allowextload']), 'allowextload');
                    $dbr -> execute($downupload, 'downupload');

                    saveSetting();

                    setFlash('success', 'Настройки сайта успешно изменены!');
                    redirect("/admin/setting?act=setload");

                } else {
                    showError('Ошибка! Все поля настроек обязательны для заполнения!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting?act=setload">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                   Форма изменения блогов                               ##
        ############################################################################################
        case 'setblog':

            echo '<b>Настройки блогов</b><br><hr>';

            echo '<div class="form">';
            echo '<form method="post" action="/admin/setting?act=editblog&amp;uid='.$_SESSION['token'].'">';

            echo 'Статей на страницу:<br><input name="blogpost" maxlength="2" value="'.$setting['blogpost'].'"><br>';
            echo 'Комментариев в блогах:<br><input name="blogcomm" maxlength="2" value="'.$setting['blogcomm'].'"><br>';
            echo 'Хранение прочтений (часов):<br><input name="blogexpread" maxlength="3" value="'.$setting['blogexpread'].'"><br>';
            echo 'Хранение голосований (часов):<br><input name="blogexprated" maxlength="3" value="'.$setting['blogexprated'].'"><br>';
            echo 'Группы блогов:<br><input name="bloggroup" maxlength="2" value="'.$setting['bloggroup'].'"><br>';
            echo 'Кол. символов в статье:<br><input name="maxblogpost" maxlength="6" value="'.$setting['maxblogpost'].'"><br>';

            echo 'Актива для голосования за статьи: <br><input name="blogvotepoint" maxlength="3" value="'.$setting['blogvotepoint'].'"><br>';

            echo '<input value="Изменить" type="submit"></form></div><br>';
            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                     Изменение в блогах                                 ##
        ############################################################################################
        case 'editblog':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if ($_POST['blogpost'] != "" && $_POST['blogcomm'] != "" && $_POST['blogexpread'] != "" && $_POST['blogexprated'] != "" && $_POST['bloggroup'] != "" && $_POST['maxblogpost'] != "" && $_POST['blogvotepoint'] != "") {
                    $dbr = DB::run() -> prepare("UPDATE `setting` SET `value`=? WHERE `name`=?;");
                    $dbr -> execute(intval($_POST['blogpost']), 'blogpost');
                    $dbr -> execute(intval($_POST['blogcomm']), 'blogcomm');
                    $dbr -> execute(intval($_POST['blogexpread']), 'blogexpread');
                    $dbr -> execute(intval($_POST['blogexprated']), 'blogexprated');
                    $dbr -> execute(intval($_POST['bloggroup']), 'bloggroup');
                    $dbr -> execute(intval($_POST['maxblogpost']), 'maxblogpost');
                    $dbr -> execute(intval($_POST['blogvotepoint']), 'blogvotepoint');

                    saveSetting();

                    setFlash('success', 'Настройки сайта успешно изменены!');
                    redirect("/admin/setting?act=setblog");

                } else {
                    showError('Ошибка! Все поля настроек обязательны для заполнения!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting?act=setblog">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                           Форма изменения постраничной навигации                       ##
        ############################################################################################
        case 'setseven':

            echo '<b>Настройки постраничной навигации</b><br><hr>';

            echo '<div class="form">';
            echo '<form method="post" action="/admin/setting?act=editseven&amp;uid='.$_SESSION['token'].'">';

            echo 'Пользователей в юзерлисте:<br><input name="userlist" maxlength="2" value="'.$setting['userlist'].'"><br>';
            echo 'Пользователей в кто-где:<br><input name="showuser" maxlength="2" value="'.$setting['showuser'].'"><br>';
            echo 'Сохраняется истории в кто-где:<br><input name="lastusers" maxlength="3" value="'.$setting['lastusers'].'"><br>';
            echo 'Пользователей в онлайне:<br><input name="onlinelist" maxlength="2" value="'.$setting['onlinelist'].'"><br>';
            echo 'Смайлов на стр.:<br><input name="smilelist" maxlength="2" value="'.$setting['smilelist'].'"><br>';
            echo 'Юзеров в рейтинге репутации на стр.:<br><input name="avtorlist" maxlength="2" value="'.$setting['avtorlist'].'"><br>';
            echo 'Юзеров в рейтинге долгожителей:<br><input name="lifelist" maxlength="2" value="'.$setting['lifelist'].'"><br>';
            echo 'Юзеров в списке забаненных:<br><input name="banlist" maxlength="2" value="'.$setting['banlist'].'"><br>';
            echo 'Листинг истории банов пользователя:<br><input name="listbanhist" maxlength="2" value="'.$setting['listbanhist'].'"><br>';
            echo 'Юзеров в поиске пользователей:<br><input name="usersearch" maxlength="2" value="'.$setting['usersearch'].'"><br>';
            echo 'Листинг в IP-бан панеле:<br><input name="ipbanlist" maxlength="2" value="'.$setting['ipbanlist'].'"><br>';
            echo 'Просмотр логов на страницу:<br><input name="loglist" maxlength="2" value="'.$setting['loglist'].'"><br>';
            echo 'Данных на страницу в черном списке:<br><input name="blacklist" maxlength="2" value="'.$setting['blacklist'].'"><br>';
            echo 'Пользователей в списке ожидающих:<br><input name="reglist" maxlength="2" value="'.$setting['reglist'].'"><br>';
            echo 'Инвайтов в приглашениях:<br><input name="listinvite" maxlength="2" value="'.$setting['listinvite'].'"><br>';
            echo 'Постов на стене сообщений:<br><input name="wallpost" maxlength="2" value="'.$setting['wallpost'].'"><br>';
            echo 'Сохраняется постов на стене сообщений:<br><input name="wallmaxpost" maxlength="3" value="'.$setting['wallmaxpost'].'"><br>';
            echo 'История авторизаций:<br><input name="loginauthlist" maxlength="3" value="'.$setting['loginauthlist'].'"><br>';
            echo 'Денежные операции:<br><input name="listtransfers" maxlength="3" value="'.$setting['listtransfers'].'"><br>';
            echo 'Голосов в истории рейтинга:<br><input name="ratinglist" maxlength="3" value="'.$setting['ratinglist'].'"><br>';
            echo '<input value="Изменить" type="submit"></form></div><br>';

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                               Изменение постраничной навигации                         ##
        ############################################################################################
        case 'editseven':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if ($_POST['userlist'] != "" && $_POST['showuser'] != "" && $_POST['lastusers'] != "" && $_POST['onlinelist'] != "" && $_POST['smilelist'] != "" && $_POST['avtorlist'] != "" && $_POST['lifelist'] != "" && $_POST['banlist'] != "" && $_POST['listbanhist'] != "" && $_POST['usersearch'] != "" && $_POST['ipbanlist'] != "" && $_POST['loglist'] != "" && $_POST['blacklist'] != "" && $_POST['reglist'] != "" && $_POST['listinvite'] != "" && $_POST['wallpost'] != "" && $_POST['wallmaxpost'] != "" && $_POST['loginauthlist'] != "" && $_POST['listtransfers'] != ""&& $_POST['ratinglist'] != "") {

                    $dbr = DB::run() -> prepare("UPDATE `setting` SET `value`=? WHERE `name`=?;");
                    $dbr -> execute(intval($_POST['userlist']), 'userlist');
                    $dbr -> execute(intval($_POST['showuser']), 'showuser');
                    $dbr -> execute(intval($_POST['lastusers']), 'lastusers');
                    $dbr -> execute(intval($_POST['onlinelist']), 'onlinelist');
                    $dbr -> execute(intval($_POST['smilelist']), 'smilelist');
                    $dbr -> execute(intval($_POST['avtorlist']), 'avtorlist');
                    $dbr -> execute(intval($_POST['lifelist']), 'lifelist');
                    $dbr -> execute(intval($_POST['banlist']), 'banlist');
                    $dbr -> execute(intval($_POST['listbanhist']), 'listbanhist');
                    $dbr -> execute(intval($_POST['usersearch']), 'usersearch');
                    $dbr -> execute(intval($_POST['ipbanlist']), 'ipbanlist');
                    $dbr -> execute(intval($_POST['loglist']), 'loglist');
                    $dbr -> execute(intval($_POST['blacklist']), 'blacklist');
                    $dbr -> execute(intval($_POST['reglist']), 'reglist');
                    $dbr -> execute(intval($_POST['listinvite']), 'listinvite');
                    $dbr -> execute(intval($_POST['wallpost']), 'wallpost');
                    $dbr -> execute(intval($_POST['wallmaxpost']), 'wallmaxpost');
                    $dbr -> execute(intval($_POST['loginauthlist']), 'loginauthlist');
                    $dbr -> execute(intval($_POST['listtransfers']), 'listtransfers');
                    $dbr -> execute(intval($_POST['ratinglist']), 'ratinglist');

                    saveSetting();

                    setFlash('success', 'Настройки сайта успешно изменены!');
                    redirect("/admin/setting?act=setseven");

                } else {
                    showError('Ошибка! Все поля настроек обязательны для заполнения!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/setting?act=setseven">Вернуться</a><br>';
        break;

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
