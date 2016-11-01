<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;
$uz = (empty($_GET['uz'])) ? check($log) : check($_GET['uz']);
$gid = (isset($_GET['gid'])) ? abs(intval($_GET['gid'])) : 0;

show_title('Галерея сайта');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    $photos = array();
    $total = DB::run() -> querySingle("SELECT count(*) FROM `photo`;");

    if ($total > 0) {
        if ($start >= $total) {
            $start = last_page($total, $config['fotolist']);
        }

        $page = floor(1 + $start / $config['fotolist']);
        $config['newtitle'] = 'Галерея сайта (Стр. '.$page.')';

        $queryphoto = DB::run() -> query("SELECT * FROM `photo` ORDER BY `photo_time` DESC LIMIT ".$start.", ".$config['fotolist'].";");
        $photos = $queryphoto->fetchAll();

    }

    render('gallery/index', array('photos' => $photos, 'start' => $start, 'total' => $total));
break;

    ############################################################################################
    ##                             Просмотр полной фотографии                                 ##
    ############################################################################################
    case 'view':

        $photo = DB::run() -> queryFetch("SELECT * FROM `photo` WHERE `photo_id`=? LIMIT 1;", array($gid));

        render('gallery/view', array('photo' => $photo, 'start' => $start));

    break;

    ############################################################################################
    ##                                       Оценка фотографии                                ##
    ############################################################################################
    case 'vote':

        $uid = check($_GET['uid']);
        $vote = check($_GET['vote']);

        if (is_user()) {
            if ($uid == $_SESSION['token']) {
                if ($vote == 'up' || $vote == 'down') {

                    $score = ($vote == 'up') ? 1 : -1;

                    $data = DB::run() -> queryFetch("SELECT * FROM `photo` WHERE `photo_id`=? LIMIT 1;", array($gid));

                    if (!empty($data)) {
                        if ($log != $data['photo_user']) {
                            $queryrated = DB::run() -> querySingle("SELECT `rated_id` FROM `ratedphoto` WHERE `rated_photo`=? AND `rated_user`=? LIMIT 1;", array($gid, $log));

                            if (empty($queryrated)) {
                                $expiresrated = SITETIME + 3600 * $config['photoexprated'];

                                DB::run() -> query("DELETE FROM `ratedphoto` WHERE `rated_time`<?;", array(SITETIME));
                                DB::run() -> query("INSERT INTO `ratedphoto` (`rated_photo`, `rated_user`, `rated_time`) VALUES (?, ?, ?);", array($gid, $log, $expiresrated));
                                DB::run() -> query("UPDATE `photo` SET `photo_rating`=`photo_rating`+? WHERE `photo_id`=?;", array($score, $gid));

                                $_SESSION['note'] = 'Ваша оценка принята! Рейтинг фотографии: '.format_num($data['photo_rating'] + $score);
                                redirect("/gallery?act=view&gid=$gid");

                            } else {
                                show_error('Ошибка! Вы уже оценивали данную фотографию!');
                            }
                        } else {
                            show_error('Ошибка! Нельзя голосовать за свои фотографии!');
                        }
                    } else {
                        show_error('Ошибка! Данной фотографии не существует!');
                    }
                } else {
                    show_error('Ошибка! Необходимо проголосовать за или против фотографии!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }
        } else {
            show_login('Вы не авторизованы, для голосования за фотографии, необходимо');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery?act=view&amp;gid='.$gid.'">Вернуться</a><br />';
    break;

    ############################################################################################
    ##                                  Форма загрузки фото                                   ##
    ############################################################################################
    case 'addphoto':

        $config['newtitle'] = 'Добавление фотографии';

        if (is_user()) {
            echo '<div class="form">';
            echo '<form action="/gallery?act=add&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';
            echo 'Прикрепить фото:<br /><input type="file" name="photo" /><br />';
            echo 'Название: <br /><input type="text" name="title" /><br />';
            echo 'Подпись к фото: <br /><textarea cols="25" rows="5" name="text"></textarea><br />';

            echo 'Закрыть комментарии: <input name="closed" type="checkbox" value="1" /><br />';

            echo '<input type="submit" value="Добавить" /></form></div><br />';

            echo 'Разрешается добавлять фотки с расширением jpg, jpeg, gif и png<br />';
            echo 'Весом не более '.formatsize($config['filesize']).' и размером от 100 до '.(int)$config['fileupfoto'].' px<br /><br />';
        } else {
            show_login('Вы не авторизованы, чтобы добавить фотографию, необходимо');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery?start='.$start.'">Вернуться</a><br />';
    break;

    ############################################################################################
    ##                                   Загрузка фото                                        ##
    ############################################################################################
    case 'add':

        $config['newtitle'] = 'Результат добавления';

        $uid = check($_GET['uid']);
        $title = check($_POST['title']);
        $text = (!empty($_POST['text'])) ? check($_POST['text']) : '';
        $closed = (empty($_POST['closed'])) ? 0 : 1;

        if (is_user()) {
            if ($uid == $_SESSION['token']) {
                if (is_uploaded_file($_FILES['photo']['tmp_name'])) {
                    if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
                        if (utf_strlen($text) <= 1000) {
                            if (is_flood($log)) {

                                $text = antimat($text);

                                DB::run() -> query("INSERT INTO `photo` (`photo_user`, `photo_title`, `photo_text`, `photo_link`, `photo_time`, `photo_closed`) VALUES (?, ?, ?, ?, ?, ?);", array($log, $title, $text, '', SITETIME, $closed));

                                $lastid = DB::run() -> lastInsertId();

                                // ------------------------------------------------------//
                                $handle = upload_image($_FILES['photo'], $config['filesize'], $config['fileupfoto'], $lastid);
                                if ($handle) {

                                    $handle -> process(HOME.'/upload/pictures/');
                                    if ($handle -> processed) {

                                        DB::run() -> query("UPDATE `photo` SET `photo_link`=? WHERE `photo_id`=?;", array($handle -> file_dst_name, $lastid));

                                        $handle -> clean();

                                        notice('Фотография успешно загружена!');
                                        redirect("/gallery");

                                    } else {
                                        show_error($handle -> error);
                                    }
                                } else {
                                    show_error('Ошибка! Не удалось загрузить изображение!');
                                }
                            } else {
                                show_error('Антифлуд! Вы слишком часто добавляете фотографии!');
                            }
                        } else {
                            show_error('Слишком длинное описание (Необходимо до 1000 символов)!');
                        }
                    } else {
                        show_error('Слишком длинное или короткое название (Необходимо от 5 до 50 символов)!');
                    }
                } else {
                    show_error('Ошибка! Не удалось загрузить изображение!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }
        } else {
            show_login('Вы не авторизованы, чтобы добавить фотографию, необходимо');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery?act=addphoto">Вернуться</a><br />';
        break;
    ############################################################################################
    ##                                 Редактирование фото                                    ##
    ############################################################################################
    case 'edit':

        if (is_user()) {
            $photo = DB::run() -> queryFetch("SELECT * FROM `photo` WHERE `photo_id`=? AND `photo_user`=? LIMIT 1;", array($gid, $log));

            if (!empty($photo)) {

                echo '<div class="form">';
                echo '<form action="/gallery?act=change&amp;gid='.$gid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo 'Название: <br /><input type="text" name="title" value="'.$photo['photo_title'].'" /><br />';
                echo 'Подпись к фото: <br /><textarea cols="25" rows="5" name="text">'.$photo['photo_text'].'</textarea><br />';

                echo 'Закрыть комментарии: ';
                $checked = ($photo['photo_closed'] == 1) ? ' checked="checked"' : '';
                echo '<input name="closed" type="checkbox" value="1"'.$checked.' /><br />';

                echo '<input type="submit" value="Изменить" /></form></div><br />';
            } else {
                show_error('Ошибка! Фотография удалена или вы не автор этой фотографии!');
            }
        } else {
            show_login('Вы не авторизованы, чтобы редактировать фотографию, необходимо');
        }

        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/gallery/album?act=photo&amp;uz='.$uz.'&amp;start='.$start.'">Альбом</a><br />';
        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery">Галерея</a><br />';
    break;

    ############################################################################################
    ##                                 Изменение описания                                     ##
    ############################################################################################
    case 'change':

        $uid = check($_GET['uid']);
        $title = check($_POST['title']);
        $text = (!empty($_POST['text'])) ? check($_POST['text']) : '';
        $closed = (empty($_POST['closed'])) ? 0 : 1;

        if ($uid == $_SESSION['token']) {
            if (is_user()) {
                $photo = DB::run() -> queryFetch("SELECT * FROM `photo` WHERE `photo_id`=? AND `photo_user`=? LIMIT 1;", array($gid, $log));

                if (!empty($photo)) {
                    if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
                        if (utf_strlen($text) <= 1000) {

                            $text = antimat($text);

                            DB::run() -> query("UPDATE `photo` SET `photo_title`=?, `photo_text`=?, `photo_closed`=? WHERE `photo_id`=?;", array($title, $text, $closed, $gid));

                            $_SESSION['note'] = 'Фотография успешно отредактирована!';
                            redirect("/gallery/album?act=photo&uz=$uz&start=$start");

                        } else {
                            show_error('Ошибка! Слишком длинное описание (Необходимо до 1000 символов)!');
                        }
                    } else {
                        show_error('Ошибка! Слишком длинное или короткое название!');
                    }
                } else {
                    show_error('Ошибка! Фотография удалена или вы не автор этой фотографии!');
                }
            } else {
                show_login('Вы не авторизованы, чтобы редактировать фотографию, необходимо');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/gallery?act=edit&amp;gid='.$gid.'&amp;start='.$start.'">Вернуться</a><br />';
        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery">Галерея</a><br />';
    break;

    ############################################################################################
    ##                                   Список комментариев                                  ##
    ############################################################################################
    case 'comments':

        $photo = DB::run() -> queryFetch("SELECT * FROM `photo` WHERE `photo_id`=? LIMIT 1;", array($gid));

        if (!empty($photo)) {
            $config['newtitle'] = 'Комментарии - '.$photo['photo_title'];

            echo '<i class="fa fa-picture-o"></i> <b><a href="/gallery?act=view&amp;gid='.$photo['photo_id'].'">'.$photo['photo_title'].'</a></b><br /><br />';

            echo '<a href="/gallery?act=comments&amp;gid='.$gid.'&amp;rand='.mt_rand(100, 999).'">Обновить</a><hr />';

            $total = DB::run() -> querySingle("SELECT count(*) FROM `commphoto` WHERE `commphoto_gid`=?;", array($gid));

            if ($total > 0) {
                if ($start >= $total) {
                    $start = last_page($total, $config['postgallery']);
                }

                $is_admin = is_admin();
                if ($is_admin) {
                    echo '<form action="/gallery?act=delcomm&amp;gid='.$gid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
                }

                $querycomm = DB::run() -> query("SELECT * FROM `commphoto` WHERE `commphoto_gid`=? ORDER BY `commphoto_time` ASC LIMIT ".$start.", ".$config['postgallery'].";", array($gid));

                while ($data = $querycomm -> fetch()) {

                    echo '<div class="b">';
                    echo '<div class="img">'.user_avatars($data['commphoto_user']).'</div>';

                    if ($is_admin) {
                        echo '<span class="imgright"><input type="checkbox" name="del[]" value="'.$data['commphoto_id'].'" /></span>';
                    }

                    echo '<b>'.profile($data['commphoto_user']).'</b> <small>('.date_fixed($data['commphoto_time']).')</small><br />';
                    echo user_title($data['commphoto_user']).' '.user_online($data['commphoto_user']).'</div>';

                    if ($log == $data['commphoto_user'] && $data['commphoto_time'] + 600 > SITETIME) {
                        echo '<div class="right"><a href="/gallery?act=editcomm&amp;gid='.$gid.'&amp;cid='.$data['commphoto_id'].'&amp;start='.$start.'">Редактировать</a></div>';
                    }

                    echo '<div>'.bb_code($data['commphoto_text']).'<br />';

                    if (is_admin() || empty($config['anonymity'])) {
                        echo '<span class="data">('.$data['commphoto_brow'].', '.$data['commphoto_ip'].')</span>';
                    }

                    echo '</div>';
                }

                if ($is_admin) {
                    echo '<span class="imgright"><input type="submit" value="Удалить выбранное" /></span></form>';
                }

                page_strnavigation('/gallery?act=comments&amp;gid='.$gid.'&amp;', $config['postgallery'], $start, $total);
            }

            if (empty($photo['photo_closed'])) {

                if (empty($total)) {
                    show_error('Комментариев еще нет!');
                }

                if (is_user()) {
                    echo '<div class="form">';
                    echo '<form action="/gallery?act=addcomm&amp;gid='.$gid.'&amp;uid='.$_SESSION['token'].'" method="post">';

                    echo '<textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br />';
                    echo '<input type="submit" value="Написать" /></form></div><br />';

                    echo '<a href="/rules">Правила</a> / ';
                    echo '<a href="/smiles">Смайлы</a> / ';
                    echo '<a href="/tags">Теги</a><br /><br />';
                } else {
                    show_login('Вы не авторизованы, чтобы добавить комментарий, необходимо');
                }
            } else {
                show_error('Комментирование данной фотографии закрыто!');
            }

            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/gallery?act=photo&amp;uz='.$photo['photo_user'].'">Альбом</a><br />';
        } else {
            show_error('Ошибка! Данного изображение не существует!');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery">Галерея</a><br />';
    break;

    ############################################################################################
    ##                                   Запись комментариев                                  ##
    ############################################################################################
    case 'addcomm':

        $uid = check($_GET['uid']);
        $msg = check($_POST['msg']);

        $config['newtitle'] = 'Добавление комментария';

        if (is_user()) {
            if ($uid == $_SESSION['token']) {
                if (utf_strlen($msg) >= 5 && utf_strlen($msg) <= 1000) {
                    $data = DB::run() -> queryFetch("SELECT * FROM `photo` WHERE `photo_id`=? LIMIT 1;", array($gid));

                    if (!empty($data)) {
                        if (empty($data['photo_closed'])) {
                            if (is_flood($log)) {
                                $msg = antimat($msg);

                                DB::run() -> query("INSERT INTO `commphoto` (`commphoto_gid`, `commphoto_text`, `commphoto_user`, `commphoto_time`, `commphoto_ip`, `commphoto_brow`) VALUES (?, ?, ?, ?, ?, ?);", array($gid, $msg, $log, SITETIME, App::getClientIp(), App::getUserAgent()));

                                DB::run() -> query("DELETE FROM `commphoto` WHERE `commphoto_gid`=? AND `commphoto_time` < (SELECT MIN(`commphoto_time`) FROM (SELECT `commphoto_time` FROM `commphoto` WHERE `commphoto_gid`=? ORDER BY `commphoto_time` DESC LIMIT ".$config['maxpostgallery'].") AS del);", array($gid, $gid));

                                DB::run() -> query("UPDATE `photo` SET `photo_comments`=`photo_comments`+1 WHERE `photo_id`=?;", array($gid));
                                DB::run() -> query("UPDATE `users` SET `users_allcomments`=`users_allcomments`+1, `users_point`=`users_point`+1, `users_money`=`users_money`+5 WHERE `users_login`=?", array($log));

                                $_SESSION['note'] = 'Комментарий успешно добавлен!';
                                redirect("/gallery?act=end&gid=$gid");

                            } else {
                                show_error('Антифлуд! Разрешается отправлять комментарии раз в '.flood_period().' секунд!');
                            }
                        } else {
                            show_error('Ошибка! Комментирование данной фотографии запрещено!');
                        }
                    } else {
                        show_error('Ошибка! Данного изображения не существует!');
                    }
                } else {
                    show_error('Ошибка! Слишком длинный или короткий комментарий!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }
        } else {
            show_login('Вы не авторизованы, чтобы добавить комментарий, необходимо');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery?act=comments&amp;gid='.$gid.'">Вернуться</a><br />';
        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/gallery">В галерею</a><br />';
    break;

    ############################################################################################
    ##                                Подготовка к редактированию                             ##
    ############################################################################################
    case 'editcomm':

        $cid = abs(intval($_GET['cid']));

        if (is_user()) {
            $comm = DB::run() -> queryFetch("SELECT `commphoto`.*, `photo`.`photo_closed` FROM `commphoto` LEFT JOIN `photo` ON `commphoto`.`commphoto_gid`=`photo`.`photo_id` WHERE `commphoto_id`=? AND `commphoto_user`=? LIMIT 1;", array($cid, $log));

            if (!empty($comm)) {
                if (empty($comm['photo_closed'])) {
                    if ($comm['commphoto_time'] + 600 > SITETIME) {

                        echo '<i class="fa fa-pencil"></i> <b>'.nickname($comm['commphoto_user']).'</b> <small>('.date_fixed($comm['commphoto_time']).')</small><br /><br />';

                        echo '<div class="form">';
                        echo '<form action="/gallery?act=changecomm&amp;gid='.$comm['commphoto_gid'].'&amp;cid='.$cid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
                        echo '<textarea id="markItUp" cols="25" rows="5" name="msg" id="msg">'.$comm['commphoto_text'].'</textarea><br />';
                        echo '<input type="submit" value="Редактировать" /></form></div><br />';

                    } else {
                        show_error('Ошибка! Редактирование невозможно, прошло более 10 минут!!');
                    }
                } else {
                    show_error('Ошибка! Редактирование невозможно, комментирование запрещено!');
                }
            } else {
                show_error('Ошибка! Комментарий удален или вы не автор этого комментария!');
            }
        } else {
            show_login('Вы не авторизованы, чтобы редактировать комментарии, необходимо');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery?act=comments&amp;gid='.$gid.'&amp;start='.$start.'">Вернуться</a><br />';
    break;

    ############################################################################################
    ##                                  Редактирование комментария                            ##
    ############################################################################################
    case 'changecomm':

        $uid = check($_GET['uid']);
        $cid = abs(intval($_GET['cid']));
        $msg = check($_POST['msg']);

        if (is_user()) {
            if ($uid == $_SESSION['token']) {
                if (utf_strlen($msg) >= 5 && utf_strlen($msg) <= 1000) {
                    $comm = DB::run() -> queryFetch("SELECT `commphoto`.*, `photo`.`photo_closed` FROM `commphoto` LEFT JOIN `photo` ON `commphoto`.`commphoto_gid`=`photo`.`photo_id` WHERE `commphoto_id`=? AND `commphoto_user`=? LIMIT 1;", array($cid, $log));

                    if (!empty($comm)) {
                        if (empty($comm['photo_closed'])) {
                            if ($comm['commphoto_time'] + 600 > SITETIME) {

                                $msg = antimat($msg);

                                DB::run() -> query("UPDATE `commphoto` SET `commphoto_text`=? WHERE `commphoto_id`=?;", array($msg, $cid));

                                $_SESSION['note'] = 'Комментарий успешно отредактирован!';
                                redirect("/gallery?act=comments&gid=$gid&start=$start");

                            } else {
                                show_error('Ошибка! Редактирование невозможно, прошло более 10 минут!');
                            }
                        } else {
                            show_error('Ошибка! Редактирование невозможно, комментирование запрещено!');
                        }
                    } else {
                        show_error('Ошибка! Комментарий удален или вы не автор этого комментария!');
                    }
                } else {
                    show_error('Ошибка! Слишком длинный или короткий комментарий!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }
        } else {
            show_login('Вы не авторизованы, чтобы редактировать комментарии, необходимо');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery?act=editcomm&amp;gid='.$gid.'&amp;cid='.$cid.'&amp;start='.$start.'">Вернуться</a><br />';
    break;

    ############################################################################################
    ##                                 Удаление комментариев                                  ##
    ############################################################################################
    case 'delcomm':

        $uid = check($_GET['uid']);
        if (isset($_POST['del'])) {
            $del = intar($_POST['del']);
        } else {
            $del = 0;
        }

        if (is_admin()) {
            if ($uid == $_SESSION['token']) {
                if (!empty($del)) {
                    $del = implode(',', $del);

                    $delcomments = DB::run() -> exec("DELETE FROM commphoto WHERE commphoto_id IN (".$del.") AND commphoto_gid=".$gid.";");
                    DB::run() -> query("UPDATE photo SET photo_comments=photo_comments-? WHERE photo_id=?;", array($delcomments, $gid));

                    $_SESSION['note'] = 'Выбранные комментарии успешно удалены!';
                    redirect("/gallery?act=comments&gid=$gid&start=$start");

                } else {
                    show_error('Ошибка! Отстутствуют выбранные комментарии для удаления!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }
        } else {
            show_error('Ошибка! Удалять комментарии могут только модераторы!');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery?act=comments&amp;gid='.$gid.'">Вернуться</a><br />';
    break;

    ############################################################################################
    ##                                   Удаление фотографий                                  ##
    ############################################################################################
    case 'delphoto':

        $uid = check($_GET['uid']);

        if (is_user()) {
            if ($uid == $_SESSION['token']) {
                if (is_writeable(HOME.'/upload/pictures')) {
                    $querydel = DB::run() -> queryfetch("SELECT `photo_id`, `photo_link`, `photo_comments` FROM `photo` WHERE `photo_id`=? AND `photo_user`=? LIMIT 1;", array($gid, $log));
                    if (!empty($querydel)) {
                        if (empty($querydel['photo_comments'])) {
                            DB::run() -> query("DELETE FROM `photo` WHERE `photo_id`=? LIMIT 1;", array($querydel['photo_id']));
                            DB::run() -> query("DELETE FROM `commphoto` WHERE `commphoto_gid`=?;", array($querydel['photo_id']));

                            unlink_image('upload/pictures/', $querydel['photo_link']);

                            $_SESSION['note'] = 'Фотография успешно удалена!';
                            redirect("/gallery?act=photo&start=$start");

                        } else {
                            show_error('Ошибка! Запрещено удалять фотографии к которым имеются комментарии!');
                        }
                    } else {
                        show_error('Ошибка! Данная фотография не существует или вы не автор этой фотографии!');
                    }
                } else {
                    show_error('Ошибка! Не установлены атрибуты доступа на дирекоторию с фотографиями!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }
        } else {
            show_login('Вы не авторизованы, чтобы удалять фотографии, необходимо');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery?act=photo&amp;start='.$start.'">Вернуться</a><br />';
    break;

    ############################################################################################
    ##                             Переадресация на последнюю страницу                        ##
    ############################################################################################
    case 'end':

        $query = DB::run() -> queryFetch("SELECT count(*) as `total_comments` FROM `commphoto` WHERE `commphoto_gid`=? LIMIT 1;", array($gid));

        if (!empty($query)) {

            $total_comments = (empty($query['total_comments'])) ? 1 : $query['total_comments'];
            $end = last_page($total_comments, $config['postgallery']);

            redirect("/gallery?act=comments&gid=$gid&start=$end");

        } else {
            show_error('Ошибка! Комментарий к данному изображению не существует!');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery">В галерею</a><br />';
    break;

    ############################################################################################
    ##                                   Удаление фотографий                                  ##
    ############################################################################################
    /**
    * case 'delphoto':
    *
    * $uid = check($_GET['uid']);
    * if (isset($_POST['del'])) {$del = intar($_POST['del']);} else {$del = 0;}
    *
    * if (is_user()){
    * if ($uid==$_SESSION['token']){
    * if (!empty($del)){
    *
    * $del = implode(',', $del);
    *
    * if (is_writeable(HOME.'/upload/pictures')){
    *
    * $querydel = DB::run()->query("SELECT `photo_id`, `photo_link` FROM `photo` WHERE `photo_id` IN (".$del.") AND `photo_user`=?;", array($log));
    * $arr_photo = $querydel->fetchAll();
    *
    * if (count($arr_photo)>0){
    * foreach ($arr_photo as $delete){
    * DB::run()->query("DELETE FROM `photo` WHERE `photo_id`=? LIMIT 1;", array($delete['photo_id']));
    * DB::run()->query("DELETE FROM `commphoto` WHERE `commphoto_gid`=?;", array($delete['photo_id']));
    * if (file_exists(HOME.'/upload/pictures/'.$delete['photo_link'])) {unlink(HOME.'/upload/pictures/'.$delete['photo_link']);}
    * }
    *
    * $_SESSION['note'] = 'Выбранные фотографии успешно удалены!';
    * redirect("/gallery?act=album&start=$start");
    *
    * } else {show_error('Ошибка! Данных фотографий не существует или вы не автор этих фотографий!');}
    * } else {show_error('Ошибка! Не установлены атрибуты доступа на дирекоторию с фотографиями!');}
    * } else {show_error('Ошибка! Отсутствуют выбранные фотографии!');}
    * } else {show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');}
    * } else {show_login('Вы не авторизованы, чтобы удалять фотографии, необходимо');}
    *
    * echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery?act=album&amp;start='.$start.'">Вернуться</a><br />';
    * break;
    */

endswitch;

App::view($config['themes'].'/foot');
