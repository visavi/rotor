<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;
$cid = (isset($_GET['cid'])) ? abs(intval($_GET['cid'])) : 0;
$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;
$uz = (empty($_GET['uz'])) ? check($log) : check($_GET['uz']);

show_title('Блоги');
$config['newtitle'] = 'Блоги - Список статей';

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    if (!empty($cid)) {
        $cats = DB::run() -> queryFetch("SELECT * FROM `catsblog` WHERE `cats_id`=? LIMIT 1;", array($cid));

        if (!empty($cats)) {

            $total = DB::run() -> querySingle("SELECT count(*) FROM `blogs` WHERE `blogs_cats_id`=?;", array($cid));

            if ($total > 0) {
                if ($start >= $total) {
                    $start = last_page($total, $config['blogpost']);
                }

                $page = floor(1 + $start / $config['blogpost']);
                $config['newtitle'] = $cats['cats_name'].' (Стр. '.$page.')';

                $queryblog = DB::run() -> query("SELECT * FROM `blogs` WHERE `blogs_cats_id`=? ORDER BY `blogs_time` DESC LIMIT ".$start.", ".$config['blogpost'].";", array($cid));
                $blogs = $queryblog->fetchAll();

                render('blog/blog', array('blogs' => $blogs, 'cats' => $cats, 'start' => $start));

                page_strnavigation('/blog/blog?cid='.$cid.'&amp;', $config['blogpost'], $start, $total);

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

    $blogs = DB::run() -> queryFetch("SELECT `blogs`.*, `catsblog`.`cats_id`, `catsblog`.`cats_name` FROM `blogs` LEFT JOIN `catsblog` ON `blogs`.`blogs_cats_id`=`catsblog`.`cats_id` WHERE `blogs_id`=? LIMIT 1;", array($id));

    if (!empty($blogs)) {
        $text = preg_split('|\[nextpage\](<br * /?>)*|', $blogs['blogs_text'], -1, PREG_SPLIT_NO_EMPTY);

        $total = count($text);
        if ($total > 0) {
            $config['newtitle'] = $blogs['blogs_title'];
            $config['keywords'] = $blogs['blogs_tags'];
            $config['description'] = strip_str($blogs['blogs_text']);

            // --------------
            if (empty($start)) {
                $queryreads = DB::run() -> querySingle("SELECT `read_ip` FROM `readblog` WHERE `read_blog`=? AND `read_ip`=? LIMIT 1;", array($id, App::getClientIp()));

                if (empty($queryreads)) {
                    $expiresread = SITETIME + 3600 * $config['blogexpread'];
                    DB::run() -> query("DELETE FROM `readblog` WHERE `read_time`<?;", array(SITETIME));
                    DB::run() -> query("INSERT INTO `readblog` (`read_blog`, `read_ip`, `read_time`) VALUES (?, ?, ?);", array($id, App::getClientIp(), $expiresread));
                    DB::run() -> query("UPDATE `blogs` SET `blogs_read`=`blogs_read`+1 WHERE `blogs_id`=? LIMIT 1;", array($id));
                }
            }
            // --------------
            if ($start < 0 || $start >= $total) {
                $start = 0;
            }
            $end = ($total < $start + 1) ? $total : $start + 1;

            for ($i = $start; $i < $end; $i++) {
                $blogs['text'] = bb_code($text[$i]).'<br />';
            }

            $tags = preg_split('/[\s]*[,][\s]*/', $blogs['blogs_tags']);

            $arrtags = '';
            foreach($tags as $key => $value) {
                $comma = (empty($key)) ? '' : ', ';
                $arrtags .= $comma.'<a href="/blog/tags?act=search&amp;tags='.urlencode($value).'">'.$value.'</a>';
            }

            render('blog/blog_view', array('blogs' => $blogs, 'tags' => $arrtags, 'start' => $start, 'total' => $total));

        } else {
            show_error('Текста статьи еще нет!');
        }
    } else {
        show_error('Ошибка! Данной статьи не существует!');
    }

    render('includes/back', array('link' => '/blog', 'title' => 'К блогам'));
break;

############################################################################################
##                            Подготовка к редактированию статьи                          ##
############################################################################################
case 'editblog':

    if (is_user()) {
        $blogs = DB::run() -> queryFetch("SELECT * FROM `blogs` WHERE `blogs_id`=? LIMIT 1;", array($id));

        if (!empty($blogs)) {
            if ($blogs['blogs_user'] == $log) {
                $querycats = DB::run() -> query("SELECT `cats_id`, `cats_name` FROM `catsblog` ORDER BY `cats_order` ASC;");
                $cats = $querycats -> fetchAll();

                render('blog/blog_editblog', array('blogs' => $blogs, 'cats' => $cats));

            } else {
                show_error('Ошибка! Изменение невозможно, вы не автор данной статьи!');
            }
        } else {
            show_error('Ошибка! Данной статьи не существует!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы редактировать статьи, необходимо');
    }

    render('includes/back', array('link' => '/blog/blog/?act=view&amp;id='.$id, 'title' => 'Вернуться'));
    render('includes/back', array('link' => '/blog', 'title' => 'К блогам', 'icon' => 'reload.gif'));

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
                        $querycats = DB::run() -> querySingle("SELECT `cats_id` FROM `catsblog` WHERE `cats_id`=? LIMIT 1;", array($cats));
                        if (!empty($cats)) {
                            $blogs = DB::run() -> queryFetch("SELECT * FROM `blogs` WHERE `blogs_id`=? LIMIT 1;", array($id));

                            if (!empty($blogs)) {
                                if ($blogs['blogs_user'] == $log) {

                                    // Обновление счетчиков
                                    if ($blogs['blogs_cats_id'] != $cats) {
                                        DB::run() -> query("UPDATE `commblog` SET `commblog_cats`=? WHERE `commblog_blog`=?;", array($cats, $id));
                                        DB::run() -> query("UPDATE `catsblog` SET `cats_count`=`cats_count`+1 WHERE `cats_id`=?", array($cats));
                                        DB::run() -> query("UPDATE `catsblog` SET `cats_count`=`cats_count`-1 WHERE `cats_id`=?", array($blogs['blogs_cats_id']));
                                    }

                                    DB::run() -> query("UPDATE `blogs` SET `blogs_cats_id`=?, `blogs_title`=?, `blogs_text`=?, `blogs_tags`=? WHERE `blogs_id`=?;", array($cats, $title, $text, $tags, $id));

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

    render('includes/back', array('link' => '/blog/blog?act=editblog&amp;id='.$id, 'title' => 'Вернуться'));
    render('includes/back', array('link' => '/blog/blog?act=view&amp;id='.$id, 'title' => 'К статье', 'icon' => 'reload.gif'));
break;

############################################################################################
##                                Просмотр по категориям                                  ##
############################################################################################
case 'blogs':

    $config['newtitle'] = 'Статьи пользователей';

    $total = DB::run() -> querySingle("select COUNT(DISTINCT `blogs_user`) from `blogs`");

    if ($total > 0) {
        if ($start >= $total) {
            $start = last_page($total, $config['bloggroup']);
        }

        $queryblogs = DB::run() -> query("SELECT COUNT(*) AS cnt, `blogs_user` FROM `blogs` GROUP BY `blogs_user` ORDER BY cnt DESC LIMIT ".$start.", ".$config['bloggroup'].";");
        $blogs = $queryblogs -> fetchAll();

        render('blog/blog_blogs', array('blogs' => $blogs, 'total' => $total));

        page_strnavigation('/blog/blog?act=blogs&amp;', $config['bloggroup'], $start, $total);

    } else {
        show_error('Статей еще нет!');
    }

    render('includes/back', array('link' => '/blog', 'title' => 'К блогам'));
break;

############################################################################################
##                                   Создание статьи                                      ##
############################################################################################
case 'new':

    $config['newtitle'] = 'Публикация новой статьи';

    if (is_user()) {

        $querycat = DB::run() -> query("SELECT `cats_id`, `cats_name` FROM `catsblog` ORDER BY `cats_order` ASC;");
        $cats = $querycat -> fetchAll();

        if (count($cats) > 0) {

            render('blog/blog_new', array('cats' => $cats, 'cid' => $cid));

        } else {
            show_error('Категории блогов еще не созданы!');
        }
    } else {
        show_login('Вы не авторизованы, для создания новой статьи, необходимо');
    }

    render('includes/back', array('link' => '/blog', 'title' => 'К блогам'));
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
                            $blogs = DB::run() -> querySingle("SELECT `cats_id` FROM `catsblog` WHERE `cats_id`=? LIMIT 1;", array($cid));
                            if (!empty($blogs)) {
                                if (is_quarantine($log)) {
                                    if (is_flood($log)) {

                                        $text = antimat($text);

                                        DB::run() -> query("INSERT INTO `blogs` (`blogs_cats_id`, `blogs_user`, `blogs_title`, `blogs_text`, `blogs_tags`, `blogs_time`) VALUES (?, ?, ?, ?, ?, ?);", array($cid, $log, $title, $text, $tags, SITETIME));
                                        $lastid = DB::run() -> lastInsertId();

                                        DB::run() -> query("UPDATE `catsblog` SET `cats_count`=`cats_count`+1 WHERE `cats_id`=?;", array($cid));

                                        DB::run() -> query("UPDATE `users` SET `users_point`=`users_point`+5, `users_money`=`users_money`+100 WHERE `users_login`=? LIMIT 1;", array($log));

                                        notice('Статья успешно опубликована!');
                                        redirect("/blog/blog?act=view&id=$lastid");

                                    } else {
                                        show_error('Антифлуд! Вы слишком часто добавляете статьи!');
                                    }
                                } else {
                                    show_error('Карантин! Вы не можете добавлять статьи в течении '.round($config['karantin'] / 3600).' часов!');
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

    render('includes/back', array('link' => '/blog/blog?act=new&amp;cid='.$cid, 'title' => 'Вернуться'));
break;

############################################################################################
##                                       Оценка статьи                                    ##
############################################################################################
case 'vote':

    $uid = check($_GET['uid']);
    $vote = check($_GET['vote']);

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            if ($udata['users_point'] >= $config['blogvotepoint']){
                if ($vote == 'up' || $vote == 'down') {

                    $score = ($vote == 'up') ? 1 : -1;

                    $blogs = DB::run() -> queryFetch("SELECT * FROM `blogs` WHERE `blogs_id`=? LIMIT 1;", array($id));

                    if (!empty($blogs)) {
                        if ($log != $blogs['blogs_user']) {
                            $queryrated = DB::run() -> querySingle("SELECT `rated_id` FROM `ratedblog` WHERE `rated_blog`=? AND `rated_user`=? LIMIT 1;", array($id, $log));

                            if (empty($queryrated)) {
                                $expiresrated = SITETIME + 3600 * $config['blogexprated'];

                                DB::run() -> query("DELETE FROM `ratedblog` WHERE `rated_time`<?;", array(SITETIME));
                                DB::run() -> query("INSERT INTO `ratedblog` (`rated_blog`, `rated_user`, `rated_time`) VALUES (?, ?, ?);", array($id, $log, $expiresrated));
                                DB::run() -> query("UPDATE `blogs` SET `blogs_rating`=`blogs_rating`+? WHERE `blogs_id`=?;", array($score, $id));

                                notice('Ваша оценка принята! Рейтинг статьи: '.format_num($blogs['blogs_rating'] + $score));
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

    render('includes/back', array('link' => '/blog/blog?act=view&amp;id='.$id, 'title' => 'Вернуться'));
break;

############################################################################################
##                                      Комментарии                                       ##
############################################################################################
case 'comments':

    $blogs = DB::run() -> queryFetch("SELECT * FROM `blogs` WHERE `blogs_id`=? LIMIT 1;", array($id));

    if (!empty($blogs)) {
        $config['newtitle'] = 'Комментарии - '.$blogs['blogs_title'];

        $total = DB::run() -> querySingle("SELECT count(*) FROM `commblog` WHERE `commblog_blog`=?;", array($id));

        if ($total > 0) {
            if ($start >= $total) {
                $start = last_page($total, $config['blogcomm']);
            }

            $querycomm = DB::run() -> query("SELECT * FROM `commblog` WHERE `commblog_blog`=? ORDER BY `commblog_time` ASC LIMIT ".$start.", ".$config['blogcomm'].";", array($id));
            $comments = $querycomm -> fetchAll();

            render('blog/blog_comments', array('blogs' => $blogs, 'comments' => $comments, 'is_admin' => is_admin(), 'start' => $start));

            page_strnavigation('/blog/blog?act=comments&amp;id='.$id.'&amp;', $config['blogcomm'], $start, $total);
        } else {
            show_error('Комментариев еще нет!');
        }

        if (is_user()) {
            render('blog/blog_comments_form', array('blogs' => $blogs));
        } else {
            show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
        }
    } else {
        show_error('Ошибка! Данной статьи не существует!');
    }

    render('includes/back', array('link' => '/blog/blog?act=view&amp;id='.$id, 'title' => 'Вернуться'));
    render('includes/back', array('link' => '/blog', 'title' => 'К блогам', 'icon' => 'reload.gif'));
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
                $queryblog = DB::run() -> querySingle("SELECT `blogs_cats_id` FROM `blogs` WHERE `blogs_id`=? LIMIT 1;", array($id));

                if (!empty($queryblog)) {
                    if (is_quarantine($log)) {
                        if (is_flood($log)) {

                            $msg = antimat($msg);

                            DB::run() -> query("INSERT INTO `commblog` (`commblog_cats`, `commblog_blog`, `commblog_text`, `commblog_author`, `commblog_time`, `commblog_ip`, `commblog_brow`) VALUES (?, ?, ?, ?, ?, ?, ?);", array($queryblog, $id, $msg, $log, SITETIME, App::getClientIp(), App::getUserAgent()));

                            DB::run() -> query("DELETE FROM `commblog` WHERE `commblog_blog`=? AND `commblog_time` < (SELECT MIN(`commblog_time`) FROM (SELECT `commblog_time` FROM `commblog` WHERE `commblog_blog`=? ORDER BY `commblog_time` DESC LIMIT ".$config['maxblogcomm'].") AS del);", array($id, $id));

                            DB::run() -> query("UPDATE `blogs` SET `blogs_comments`=`blogs_comments`+1 WHERE `blogs_id`=?;", array($id));
                            DB::run() -> query("UPDATE `users` SET `users_allcomments`=`users_allcomments`+1, `users_point`=`users_point`+1, `users_money`=`users_money`+5 WHERE `users_login`=?", array($log));

                            notice('Сообщение успешно добавлено!');
                            redirect("/blog/blog?act=end&id=$id");

                        } else {
                            show_error('Антифлуд! Разрешается отправлять сообщения раз в '.flood_period().' секунд!');
                        }
                    } else {
                        show_error('Карантин! Вы не можете писать в течении '.round($config['karantin'] / 3600).' часов!');
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

    render('includes/back', array('link' => '/blog/blog?act=comments&amp;id='.$id, 'title' => 'Вернуться'));
break;

############################################################################################
##                                    Жалоба на спам                                      ##
############################################################################################
case 'spam':

    $uid = check($_GET['uid']);
    $pid = abs(intval($_GET['pid']));

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            $data = DB::run() -> queryFetch("SELECT * FROM `commblog` WHERE `commblog_id`=? LIMIT 1;", array($pid));

            if (!empty($data)) {
                $queryspam = DB::run() -> querySingle("SELECT `spam_id` FROM `spam` WHERE `spam_key`=? AND `spam_idnum`=? LIMIT 1;", array(6, $pid));

                if (empty($queryspam)) {
                    if (is_flood($log)) {
                        DB::run() -> query("INSERT INTO `spam` (`spam_key`, `spam_idnum`, `spam_user`, `spam_login`, `spam_text`, `spam_time`, `spam_addtime`, `spam_link`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", array(6, $data['commblog_id'], $log, $data['commblog_author'], $data['commblog_text'], $data['commblog_time'], SITETIME, $config['home'].'/blog/blog?act=comments&amp;id='.$id.'&amp;start='.$start));

                        notice('Жалоба успешно отправлена!');
                        redirect("/blog/blog?act=comments&id=$id&start=$start");

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

    render('includes/back', array('link' => '/blog/blog?act=comments&amp;id='.$id.'&amp;start='.$start, 'title' => 'Вернуться'));
break;

############################################################################################
##                                   Ответ на сообщение                                   ##
############################################################################################
case 'reply':

    $id = abs(intval($_GET['id']));
    $pid = abs(intval($_GET['pid']));

    if (is_user()) {
        $post = DB::run() -> queryFetch("SELECT * FROM `commblog` WHERE `commblog_id`=? LIMIT 1;", array($pid));

        if (!empty($post)) {
            render('blog/blog_reply', array('post' => $post, 'id' => $id));
        } else {
            show_error('Ошибка! Выбранное вами сообщение для ответа не существует!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы отвечать на сообщения, необходимо');
    }

    render('includes/back', array('link' => '/blog/blog?act=comments&amp;id='.$id.'&amp;start='.$start, 'title' => 'Вернуться'));
break;

############################################################################################
##                                   Цитирование сообщения                                ##
############################################################################################
case 'quote':

    $pid = abs(intval($_GET['pid']));


    if (is_user()) {
        $post = DB::run() -> queryFetch("SELECT * FROM `commblog` WHERE `commblog_id`=? LIMIT 1;", array($pid));

        if (!empty($post)) {
            render('blog/blog_quote', array('post' => $post, 'id' => $id));
        } else {
            show_error('Ошибка! Выбранное вами сообщение для цитирования не существует!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы цитировать сообщения, необходимо');
    }

    render('includes/back', array('link' => '/blog/blog?act=comments&amp;id='.$id.'&amp;start='.$start, 'title' => 'Вернуться'));
break;

############################################################################################
##                                Подготовка к редактированию                             ##
############################################################################################
case 'edit':

    $config['newtitle'] = 'Редактирование сообщения';

    $pid = abs(intval($_GET['pid']));

    if (is_user()) {
        $post = DB::run() -> queryFetch("SELECT * FROM `commblog` WHERE `commblog_id`=? AND `commblog_author`=? LIMIT 1;", array($pid, $log));

        if (!empty($post)) {
            if ($post['commblog_time'] + 600 > SITETIME) {

                render('blog/blog_edit', array('post' => $post, 'pid' => $pid, 'start' => $start));
            } else {
                show_error('Ошибка! Редактирование невозможно, прошло более 10 минут!!');
            }
        } else {
            show_error('Ошибка! Сообщение удалено или вы не автор этого сообщения!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы редактировать сообщения, необходимо');
    }

    render('includes/back', array('link' => '/blog/blog?act=comments&amp;id='.$id.'&amp;start='.$start, 'title' => 'Вернуться'));
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
                $post = DB::run() -> queryFetch("SELECT * FROM `commblog` WHERE `commblog_id`=? AND `commblog_author`=? LIMIT 1;", array($pid, $log));

                if (!empty($post)) {
                    if ($post['commblog_time'] + 600 > SITETIME) {
                        $msg = antimat($msg);

                        DB::run() -> query("UPDATE `commblog` SET `commblog_text`=? WHERE `commblog_id`=?", array($msg, $pid));

                        notice('Сообщение успешно отредактировано!');
                        redirect("/blog/blog?act=comments&id=$id&start=$start");

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

    render('includes/back', array('link' => '/blog/blog?act=edit&amp;id='.$id.'&amp;pid='.$pid.'&amp;start='.$start, 'title' => 'Вернуться'));
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

                $delcomments = DB::run() -> exec("DELETE FROM `commblog` WHERE `commblog_id` IN (".$del.") AND `commblog_blog`=".$id.";");
                DB::run() -> query("UPDATE `blogs` SET `blogs_comments`=`blogs_comments`-? WHERE `blogs_id`=?;", array($delcomments, $id));

                notice('Выбранные комментарии успешно удалены!');
                redirect("/blog/blog?act=comments&id=$id&start=$start");

            } else {
                show_error('Ошибка! Отстутствуют выбранные комментарии для удаления!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_error('Ошибка! Удалять комментарии могут только модераторы!');
    }

    render('includes/back', array('link' => '/blog/blog?act=comments&amp;id='.$id.'&amp;start='.$start, 'title' => 'Вернуться'));
break;

############################################################################################
##                             Переадресация на последнюю страницу                        ##
############################################################################################
case 'end':

    $query = DB::run() -> queryFetch("SELECT count(*) as `total_comments` FROM `commblog` WHERE `commblog_blog`=? LIMIT 1;", array($id));

    if (!empty($query['total_comments'])) {

        $total_comments = (empty($query['total_comments'])) ? 1 : $query['total_comments'];
        $end = last_page($total_comments, $config['blogpost']);

        redirect("/blog/blog?act=comments&id=$id&start=$end");

    } else {
        show_error('Ошибка! Комментарий к данной статье не существует!');
    }

    render('includes/back', array('link' => '/blog', 'title' => 'К блогам'));
break;

endswitch;

App::view($config['themes'].'/foot');
