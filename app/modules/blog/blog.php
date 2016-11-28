<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$cid = (isset($_GET['cid'])) ? abs(intval($_GET['cid'])) : 0;
$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;
$uz = (empty($_GET['uz'])) ? check($log) : check($_GET['uz']);
$page = abs(intval(Request::input('page', 1)));

show_title('Блоги');
$config['newtitle'] = 'Блоги - Список статей';

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    if (!empty($cid)) {
        $cats = DB::run() -> queryFetch("SELECT * FROM `catsblog` WHERE `id`=? LIMIT 1;", [$cid]);

        if (!empty($cats)) {

            $total = DB::run() -> querySingle("SELECT count(*) FROM `blogs` WHERE `category_id`=?;", [$cid]);
            $page = App::paginate(App::setting('blogpost'), $total);

            if ($total > 0) {

                $config['newtitle'] = $cats['name'].' (Стр. '.$page['current'].')';

                $queryblog = DB::run() -> query("SELECT * FROM `blogs` WHERE `category_id`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".$config['blogpost'].";", [$cid]);
                $blogs = $queryblog->fetchAll();

                render('blog/blog', compact('blogs', 'cats', 'page'));

                App::pagination($page);

            } else {
                show_error('Статей еще нет, будь первым!');
            }
        } else {
            show_error('Ошибка! Данного раздела не существует!');
        }
    } else {
        redirect("/blog");
    }
break;

############################################################################################
##                                   Просмотр статьи                                      ##
############################################################################################
case 'view':

    $blogs = DB::run() -> queryFetch("SELECT * FROM `blogs` b LEFT JOIN `catsblog` cb ON b.`category_id`=cb.`id` WHERE b.`id`=? LIMIT 1;", [$id]);

    if (!empty($blogs)) {
        $text = preg_split('|\[nextpage\](<br * /?>)*|', $blogs['text'], -1, PREG_SPLIT_NO_EMPTY);

        $total = count($text);
        $page = App::paginate(1, $total);

        if ($total > 0) {
            $config['newtitle'] = $blogs['title'];
            $config['keywords'] = $blogs['tags'];
            $config['description'] = strip_str($blogs['text']);

            // --------------
            if ($page['current'] == 1) {
                $queryreads = DB::run() -> querySingle("SELECT `ip` FROM `readblog` WHERE `blog`=? AND `ip`=? LIMIT 1;", [$id, App::getClientIp()]);

                if (empty($queryreads)) {
                    $expiresread = SITETIME + 3600 * $config['blogexpread'];
                    DB::run() -> query("DELETE FROM `readblog` WHERE `time`<?;", [SITETIME]);
                    DB::run() -> query("INSERT INTO `readblog` (`blog`, `ip`, `time`) VALUES (?, ?, ?);", [$id, App::getClientIp(), $expiresread]);
                    DB::run() -> query("UPDATE `blogs` SET `visits`=`visits`+1 WHERE `id`=? LIMIT 1;", [$id]);
                }
            }

            $end = ($total < $page['offset'] + 1) ? $total : $page['offset'] + 1;

            for ($i = $page['offset']; $i < $end; $i++) {
                $blogs['text'] = App::bbCode($text[$i]).'<br />';
            }

            $tagsList = preg_split('/[\s]*[,][\s]*/', $blogs['tags']);

            $tags = '';
            foreach($tagsList as $key => $value) {
                $comma = (empty($key)) ? '' : ', ';
                $tags .= $comma.'<a href="/blog/tags?act=search&amp;tags='.urlencode($value).'">'.$value.'</a>';
            }

            render('blog/blog_view', compact('blogs', 'tags', 'page'));

        } else {
            show_error('Текста статьи еще нет!');
        }
    } else {
        show_error('Ошибка! Данной статьи не существует!');
    }

    render('includes/back', ['link' => '/blog', 'title' => 'К блогам']);
break;

############################################################################################
##                            Подготовка к редактированию статьи                          ##
############################################################################################
case 'editblog':

    if (is_user()) {
        $blogs = DB::run() -> queryFetch("SELECT * FROM `blogs` WHERE `id`=? LIMIT 1;", [$id]);

        if (!empty($blogs)) {
            if ($blogs['user'] == $log) {
                $querycats = DB::run() -> query("SELECT `id`, `name` FROM `catsblog` ORDER BY sort ASC;");
                $cats = $querycats -> fetchAll();

                render('blog/blog_editblog', ['blogs' => $blogs, 'cats' => $cats]);

            } else {
                show_error('Ошибка! Изменение невозможно, вы не автор данной статьи!');
            }
        } else {
            show_error('Ошибка! Данной статьи не существует!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы редактировать статьи, необходимо');
    }

    render('includes/back', ['link' => '/blog/blog/?act=view&amp;id='.$id, 'title' => 'Вернуться']);
    render('includes/back', ['link' => '/blog', 'title' => 'К блогам', 'icon' => 'reload.gif']);

break;

############################################################################################
##                                  Редактирование статьи                                ##
############################################################################################
case 'changeblog':

    $uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';
    $cats = (isset($_POST['cats'])) ? abs(intval($_POST['cats'])) : '';
    $title = (isset($_POST['title'])) ? check($_POST['title']) : '';
    $text = (isset($_POST['text'])) ? check($_POST['text']) : '';
    $tags = (isset($_POST['tags'])) ? check($_POST['tags']) : '';

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
                if (utf_strlen($text) >= 100 && utf_strlen($text) <= $config['maxblogpost']) {
                    if (utf_strlen($tags) >= 2 && utf_strlen($tags) <= 50) {
                        $querycats = DB::run() -> querySingle("SELECT `id` FROM `catsblog` WHERE `id`=? LIMIT 1;", [$cats]);
                        if (!empty($cats)) {
                            $blogs = DB::run() -> queryFetch("SELECT * FROM `blogs` WHERE `id`=? LIMIT 1;", [$id]);

                            if (!empty($blogs)) {
                                if ($blogs['user'] == $log) {

                                    // Обновление счетчиков
                                    if ($blogs['category_id'] != $cats) {
                                        DB::run() -> query("UPDATE `comments` SET `relate_category_id`=? WHERE `relate_id`=?;", [$cats, $id]);
                                        DB::run() -> query("UPDATE `catsblog` SET `count`=`count`+1 WHERE `id`=?", [$cats]);
                                        DB::run() -> query("UPDATE `catsblog` SET `count`=`count`-1 WHERE `id`=?", [$blogs['category_id']]);
                                    }

                                    DB::run() -> query("UPDATE `blogs` SET `category_id`=?, `title`=?, `text`=?, `tags`=? WHERE `id`=?;", [$cats, $title, $text, $tags, $id]);

                                    notice('Статья успешно отредактирована!');
                                    redirect("/blog/blog?act=view&id=$id");

                                } else {
                                    show_error('Ошибка! Изменение невозможно, вы не автор данной статьи!');
                                }
                            } else {
                                show_error('Ошибка! Данной статьи не существует!');
                            }
                        } else {
                            show_error('Ошибка! Выбранного раздела не существует!');
                        }
                    } else {
                        show_error('Ошибка! Слишком длинные или короткие метки статьи (от 2 до 50 символов)!');
                    }
                } else {
                    show_error('Ошибка! Слишком длинный или короткий текст статьи (от 100 до '.$config['maxblogpost'].' символов)!');
                }
            } else {
                show_error('Ошибка! Слишком длинный или короткий заголовок (от 5 до 50 символов)!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы редактировать статьи, необходимо');
    }

    render('includes/back', ['link' => '/blog/blog?act=editblog&amp;id='.$id, 'title' => 'Вернуться']);
    render('includes/back', ['link' => '/blog/blog?act=view&amp;id='.$id, 'title' => 'К статье', 'icon' => 'reload.gif']);
break;

############################################################################################
##                                Просмотр по категориям                                  ##
############################################################################################
case 'blogs':

    $config['newtitle'] = 'Статьи пользователей';

    $total = DB::run() -> querySingle("select COUNT(DISTINCT `user`) from `blogs`");

    if ($total > 0) {

        $queryblogs = DB::run() -> query("SELECT COUNT(*) AS cnt, `user` FROM `blogs` GROUP BY `user` ORDER BY cnt DESC LIMIT ".$page['offset'].", ".$config['bloggroup'].";");
        $blogs = $queryblogs -> fetchAll();

        render('blog/blog_blogs', ['blogs' => $blogs, 'total' => $total]);

        App::pagination($page);

    } else {
        show_error('Статей еще нет!');
    }

    render('includes/back', ['link' => '/blog', 'title' => 'К блогам']);
break;

############################################################################################
##                                   Создание статьи                                      ##
############################################################################################
case 'new':

    $config['newtitle'] = 'Публикация новой статьи';

    if (is_user()) {

        $querycat = DB::run() -> query("SELECT `id`, `name` FROM `catsblog` ORDER BY sort ASC;");
        $cats = $querycat -> fetchAll();

        if (count($cats) > 0) {

            render('blog/blog_new', ['cats' => $cats, 'cid' => $cid]);

        } else {
            show_error('Категории блогов еще не созданы!');
        }
    } else {
        show_login('Вы не авторизованы, для создания новой статьи, необходимо');
    }

    render('includes/back', ['link' => '/blog', 'title' => 'К блогам']);
break;

############################################################################################
##                                  Публикация скрипта                                    ##
############################################################################################
case 'addblog':

    $config['newtitle'] = 'Публикация новой статьи';

    $uid = check($_GET['uid']);
    $cid = abs(intval($_POST['cid']));
    $title = check($_POST['title']);
    $text = check($_POST['text']);
    $tags = check($_POST['tags']);

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            if (!empty($cid)) {
                if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
                    if (utf_strlen($text) >= 100 && utf_strlen($text) <= $config['maxblogpost']) {
                        if (utf_strlen($tags) >= 2 && utf_strlen($tags) <= 50) {
                            $blogs = DB::run() -> querySingle("SELECT `id` FROM `catsblog` WHERE `id`=? LIMIT 1;", [$cid]);
                            if (!empty($blogs)) {

                                if (is_flood($log)) {

                                    $text = antimat($text);

                                    DB::run() -> query("INSERT INTO `blogs` (`category_id`, `user`, `title`, `text`, `tags`, `time`) VALUES (?, ?, ?, ?, ?, ?);", [$cid, $log, $title, $text, $tags, SITETIME]);
                                    $lastid = DB::run() -> lastInsertId();

                                    DB::run() -> query("UPDATE `catsblog` SET `count`=`count`+1 WHERE `id`=?;", [$cid]);

                                    DB::run() -> query("UPDATE `users` SET `point`=`point`+5, `money`=`money`+100 WHERE `login`=? LIMIT 1;", [$log]);

                                    notice('Статья успешно опубликована!');
                                    redirect("/blog/blog?act=view&id=$lastid");

                                } else {
                                    show_error('Антифлуд! Вы слишком часто добавляете статьи!');
                                }
                            } else {
                                show_error('Ошибка! Выбранный вами раздел не существует!');
                            }
                        } else {
                            show_error('Ошибка! Слишком длинные или короткие метки статьи (от 2 до 50 символов)!');
                        }
                    } else {
                        show_error('Ошибка! Слишком длинный или короткий текст статьи (от 100 до '.$config['maxblogpost'].' символов)!');
                    }
                } else {
                    show_error('Ошибка! Слишком длинный или короткий заголовок (от 5 до 50 символов)!');
                }
            } else {
                show_error('Ошибка! Вы не выбрали категорию для добавления статьи!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_login('Вы не авторизованы, для создания новой статьи, необходимо');
    }

    render('includes/back', ['link' => '/blog/blog?act=new&amp;cid='.$cid, 'title' => 'Вернуться']);
break;

############################################################################################
##                                       Оценка статьи                                    ##
############################################################################################
case 'vote':

    $uid = check($_GET['uid']);
    $vote = check($_GET['vote']);

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            if ($udata['point'] >= $config['blogvotepoint']){
                if ($vote == 'up' || $vote == 'down') {

                    $score = ($vote == 'up') ? 1 : -1;

                    $blogs = DB::run() -> queryFetch("SELECT * FROM `blogs` WHERE `id`=? LIMIT 1;", [$id]);

                    if (!empty($blogs)) {
                        if ($log != $blogs['user']) {
                            $queryrated = DB::run() -> querySingle("SELECT `id` FROM `pollings` WHERE relate_type=? AND `relate_id`=? AND `user`=? LIMIT 1;", ['blog', $id, $log]);

                            if (empty($queryrated)) {
                                $expiresrated = SITETIME + 3600 * $config['blogexprated'];

                                DB::run() -> query("DELETE FROM `pollings` WHERE relate_type=? AND `time`<?;", ['blog', SITETIME]);
                                DB::run() -> query("INSERT INTO `pollings` (relate_type, `relate_id`, `user`, `time`) VALUES (?, ?, ?, ?);", ['blog', $id, $log, $expiresrated]);
                                DB::run() -> query("UPDATE `blogs` SET `rating`=`rating`+? WHERE `id`=?;", [$score, $id]);

                                notice('Ваша оценка принята! Рейтинг статьи: '.format_num($blogs['rating'] + $score));
                                redirect("/blog/blog?act=view&id=$id");

                            } else {
                                show_error('Ошибка! Вы уже оценивали данную статью!');
                            }
                        } else {
                            show_error('Ошибка! Нельзя голосовать за свою статью!');
                        }
                    } else {
                        show_error('Ошибка! Данной статьи не существует!');
                    }
                } else {
                    show_error('Ошибка! Необходимо проголосовать за или против статьи!');
                }
            } else {
                show_error('Ошибка! У вас недостаточно актива для голосования (Необходимо '.points($config['blogvotepoint']).')!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_login('Вы не авторизованы, для голосования за статьи, необходимо');
    }

    render('includes/back', ['link' => '/blog/blog?act=view&amp;id='.$id, 'title' => 'Вернуться']);
break;

############################################################################################
##                                      Комментарии                                       ##
############################################################################################
case 'comments':

    $blogs = DB::run() -> queryFetch("SELECT * FROM `blogs` WHERE `id`=? LIMIT 1;", [$id]);

    if (!empty($blogs)) {
        $config['newtitle'] = 'Комментарии - '.$blogs['title'];

        $total = DB::run() -> querySingle("SELECT count(*) FROM `comments` WHERE relate_type=? AND `relate_id`=?;", ['blog', $id]);
        $page = App::paginate(App::setting('blogcomm'), $total);

        if ($total > 0) {

            $querycomm = DB::run() -> query("SELECT * FROM `comments` WHERE relate_type=? AND `relate_id`=? ORDER BY `time` ASC LIMIT ".$page['offset'].", ".$config['blogcomm'].";", ['blog', $id]);
            $comments = $querycomm -> fetchAll();

            render('blog/blog_comments', ['blogs' => $blogs, 'comments' => $comments, 'is_admin' => is_admin(), 'page' => $page]);

            App::pagination($page);
        } else {
            show_error('Комментариев еще нет!');
        }

        if (is_user()) {
            render('blog/blog_comments_form', ['blogs' => $blogs]);
        } else {
            show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
        }
    } else {
        show_error('Ошибка! Данной статьи не существует!');
    }

    render('includes/back', ['link' => '/blog/blog?act=view&amp;id='.$id, 'title' => 'Вернуться']);
    render('includes/back', ['link' => '/blog', 'title' => 'К блогам', 'icon' => 'reload.gif']);
break;

############################################################################################
##                                Добавление комментариев                                 ##
############################################################################################
case 'add':

    $uid = check($_GET['uid']);
    $id = abs(intval($_GET['id']));
    $msg = check($_POST['msg']);

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            if (utf_strlen($msg) >= 5 && utf_strlen($msg) < 1000) {
                $queryblog = DB::run() -> querySingle("SELECT `category_id` FROM `blogs` WHERE `id`=? LIMIT 1;", [$id]);

                if (!empty($queryblog)) {
                    if (is_flood($log)) {

                        $msg = antimat($msg);

                        DB::run() -> query("INSERT INTO `comments` (relate_type, `relate_category_id`, `relate_id`, `text`, `user`, `time`, `ip`, `brow`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", ['blog', $queryblog, $id, $msg, $log, SITETIME, App::getClientIp(), App::getUserAgent()]);

                        DB::run() -> query("DELETE FROM `comments` WHERE relate_type=? AND `relate_id`=? AND `time` < (SELECT MIN(`time`) FROM (SELECT `time` FROM `comments` WHERE `relate_type`=? AND `relate_id`=? ORDER BY `time` DESC LIMIT ".$config['maxblogcomm'].") AS del);", ['blog', $id, 'blog', $id]);

                        DB::run() -> query("UPDATE `blogs` SET `comments`=`comments`+1 WHERE `id`=?;", [$id]);
                        DB::run() -> query("UPDATE `users` SET `allcomments`=`allcomments`+1, `point`=`point`+1, `money`=`money`+5 WHERE `login`=?", [$log]);

                        notice('Сообщение успешно добавлено!');
                        redirect("/blog/blog?act=end&id=$id");

                    } else {
                        show_error('Антифлуд! Разрешается отправлять сообщения раз в '.flood_period().' секунд!');
                    }
                } else {
                    show_error('Ошибка! Данной статьи не существует!');
                }
            } else {
                show_error('Ошибка! Слишком длинное или короткое сообщение!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
    }

    render('includes/back', ['link' => '/blog/blog?act=comments&amp;id='.$id, 'title' => 'Вернуться']);
break;

############################################################################################
##                                    Жалоба на спам                                      ##
############################################################################################
case 'spam':

    $uid = check($_GET['uid']);
    $pid = abs(intval($_GET['pid']));

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            $data = DB::run() -> queryFetch("SELECT * FROM `comments` WHERE relate_type=? AND `id`=? LIMIT 1;", ['blog', $pid]);

            if (!empty($data)) {
                $queryspam = DB::run() -> querySingle("SELECT `id` FROM `spam` WHERE relate=? AND `idnum`=? LIMIT 1;", [6, $pid]);

                if (empty($queryspam)) {
                    if (is_flood($log)) {
                        DB::run() -> query("INSERT INTO `spam` (relate, `idnum`, `user`, `login`, `text`, `time`, `addtime`, `link`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", [6, $data['id'], $log, $data['user'], $data['text'], $data['time'], SITETIME, $config['home'].'/blog/blog?act=comments&amp;id='.$id.'&amp;page='.$page]);

                        notice('Жалоба успешно отправлена!');
                        redirect("/blog/blog?act=comments&id=$id&page=$page");

                    } else {
                        show_error('Антифлуд! Разрешается жаловаться на спам не чаще чем раз в '.flood_period().' секунд!');
                    }
                } else {
                    show_error('Ошибка! Жалоба на данное сообщение уже отправлена!');
                }
            } else {
                show_error('Ошибка! Выбранное вами сообщение для жалобы не существует!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы подать жалобу, необходимо');
    }

    render('includes/back', ['link' => '/blog/blog?act=comments&amp;id='.$id.'&amp;page='.$page, 'title' => 'Вернуться']);
break;

############################################################################################
##                                   Ответ на сообщение                                   ##
############################################################################################
case 'reply':

    $id = abs(intval($_GET['id']));
    $pid = abs(intval($_GET['pid']));

    if (is_user()) {
        $post = DB::run() -> queryFetch("SELECT * FROM `comments` WHERE relate_type=? AND `id`=? LIMIT 1;", ['blog', $pid]);

        if (!empty($post)) {
            render('blog/blog_reply', ['post' => $post, 'id' => $id]);
        } else {
            show_error('Ошибка! Выбранное вами сообщение для ответа не существует!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы отвечать на сообщения, необходимо');
    }

    render('includes/back', ['link' => '/blog/blog?act=comments&amp;id='.$id.'&amp;page='.$page, 'title' => 'Вернуться']);
break;

############################################################################################
##                                   Цитирование сообщения                                ##
############################################################################################
case 'quote':

    $pid = abs(intval($_GET['pid']));


    if (is_user()) {
        $post = DB::run() -> queryFetch("SELECT * FROM `comments` WHERE relate_type=? AND `id`=? LIMIT 1;", ['blog', $pid]);

        if (!empty($post)) {
            render('blog/blog_quote', ['post' => $post, 'id' => $id]);
        } else {
            show_error('Ошибка! Выбранное вами сообщение для цитирования не существует!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы цитировать сообщения, необходимо');
    }

    render('includes/back', ['link' => '/blog/blog?act=comments&amp;id='.$id.'&amp;page='.$page, 'title' => 'Вернуться']);
break;

############################################################################################
##                                Подготовка к редактированию                             ##
############################################################################################
case 'edit':

    $config['newtitle'] = 'Редактирование сообщения';

    $pid = abs(intval($_GET['pid']));

    if (is_user()) {
        $post = DB::run() -> queryFetch("SELECT * FROM `comments` WHERE relate_type=? AND `id`=? AND `user`=? LIMIT 1;", ['blog', $pid, $log]);

        if (!empty($post)) {
            if ($post['time'] + 600 > SITETIME) {

                render('blog/blog_edit', ['post' => $post, 'pid' => $pid, 'page' => $page]);
            } else {
                show_error('Ошибка! Редактирование невозможно, прошло более 10 минут!!');
            }
        } else {
            show_error('Ошибка! Сообщение удалено или вы не автор этого сообщения!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы редактировать сообщения, необходимо');
    }

    render('includes/back', ['link' => '/blog/blog?act=comments&amp;id='.$id.'&amp;apage='.$page, 'title' => 'Вернуться']);
break;

############################################################################################
##                                    Редактирование сообщения                            ##
############################################################################################
case 'editpost':

    $uid = check($_GET['uid']);
    $pid = abs(intval($_GET['pid']));
    $msg = check($_POST['msg']);

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            if (utf_strlen($msg) >= 5 && utf_strlen($msg) < 1000) {
                $post = DB::run() -> queryFetch("SELECT * FROM `comments` WHERE relate_type=? AND `id`=? AND `user`=? LIMIT 1;", ['blog', $pid, $log]);

                if (!empty($post)) {
                    if ($post['time'] + 600 > SITETIME) {
                        $msg = antimat($msg);

                        DB::run() -> query("UPDATE `comments` SET `text`=? WHERE relate_type=? AND `id`=?", [$msg, 'blog', $pid]);

                        notice('Сообщение успешно отредактировано!');
                       redirect("/blog/blog?act=comments&id=$id&page=$page");

                    } else {
                        show_error('Ошибка! Редактирование невозможно, прошло более 10 минут!!');
                    }
                } else {
                    show_error('Ошибка! Сообщение удалено или вы не автор этого сообщения!');
                }
            } else {
                show_error('Ошибка! Слишком длинное или короткое сообщение!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы редактировать сообщения, необходимо');
    }

    render('includes/back', ['link' => '/blog/blog?act=edit&amp;id='.$id.'&amp;pid='.$pid.'&amp;page='.$page, 'title' => 'Вернуться']);
break;

############################################################################################
##                                 Удаление комментариев                                  ##
############################################################################################
case 'del':

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

                $delcomments = DB::run() -> exec("DELETE FROM `comments` WHERE relate_type='blog' AND `id` IN (".$del.") AND `relate_id`=".$id.";");
                DB::run() -> query("UPDATE `blogs` SET `comments`=`comments`-? WHERE `id`=?;", [$delcomments, $id]);

                notice('Выбранные комментарии успешно удалены!');
                redirect("/blog/blog?act=comments&id=$id&page=$page");

            } else {
                show_error('Ошибка! Отстутствуют выбранные комментарии для удаления!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_error('Ошибка! Удалять комментарии могут только модераторы!');
    }

    render('includes/back', ['link' => '/blog/blog?act=comments&amp;id='.$id.'&amp;page='.$page, 'title' => 'Вернуться']);
break;

############################################################################################
##                             Переадресация на последнюю страницу                        ##
############################################################################################
case 'end':

    $query = DB::run() -> queryFetch("SELECT count(*) as `total_comments` FROM `comments` WHERE relate_type=? AND `relate_id`=? LIMIT 1;", ['blog', $id]);

    if (!empty($query['total_comments'])) {

        $total_comments = (empty($query['total_comments'])) ? 1 : $query['total_comments'];
        $end = ceil($total_comments / $config['blogpost']);

        redirect("/blog/blog?act=comments&id=$id&page=$end");

    } else {
        show_error('Ошибка! Комментарий к данной статье не существует!');
    }

    render('includes/back', ['link' => '/blog', 'title' => 'К блогам']);
break;

endswitch;

App::view($config['themes'].'/foot');
