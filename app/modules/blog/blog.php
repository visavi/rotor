<?php
App::view(App::setting('themes').'/index');

$act = check(Request::input('act', 'index'));
$cid = abs(intval(Request::input('cid')));
$id = abs(intval(Request::input('id')));
$uz = check(Request::input('uz'));
$page = abs(intval(Request::input('page', 1)));

//show_title('Блоги');
//App::setting('newtitle') = 'Блоги - Список статей';

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

                //App::setting('newtitle') = $cats['name'].' (Стр. '.$page['current'].')';

                $queryblog = DB::run() -> query("SELECT * FROM `blogs` WHERE `category_id`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".App::setting('blogpost').";", [$cid]);
                $blogs = $queryblog->fetchAll();

                App::view('blog/blog', compact('blogs', 'cats', 'page'));

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

    $blogs = DB::run() -> queryFetch("SELECT b.*, cb.name FROM `blogs` b LEFT JOIN `catsblog` cb ON b.`category_id`=cb.`id` WHERE b.`id`=? LIMIT 1;", [$id]);

    if (!empty($blogs)) {
        $text = preg_split('|\[nextpage\](<br * /?>)*|', $blogs['text'], -1, PREG_SPLIT_NO_EMPTY);

        $total = count($text);
        $page = App::paginate(1, $total);

        if ($total > 0) {
            //App::setting('newtitle') = $blogs['title'];
            //App::setting('keywords') = $blogs['tags'];
            //App::setting('description') =  strip_str($blogs['text']);

            // --------------
            if ($page['current'] == 1) {
                $queryreads = DB::run() -> querySingle("SELECT `ip` FROM `readblog` WHERE `blog`=? AND `ip`=? LIMIT 1;", [$id, App::getClientIp()]);

                if (empty($queryreads)) {
                    $expiresread = SITETIME + 3600 * App::setting('blogexpread');
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

            App::view('blog/blog_view', compact('blogs', 'tags', 'page'));

        } else {
            show_error('Текста статьи еще нет!');
        }
    } else {
        show_error('Ошибка! Данной статьи не существует!');
    }

    App::view('includes/back', ['link' => '/blog', 'title' => 'К блогам']);
break;

############################################################################################
##                            Подготовка к редактированию статьи                          ##
############################################################################################
case 'editblog':

    if (is_user()) {
        $blogs = DB::run() -> queryFetch("SELECT * FROM `blogs` WHERE `id`=? LIMIT 1;", [$id]);

        if (!empty($blogs)) {
            if ($blogs['user'] == App::getUsername()) {
                $querycats = DB::run() -> query("SELECT `id`, `name` FROM `catsblog` ORDER BY sort ASC;");
                $cats = $querycats -> fetchAll();

                App::view('blog/blog_editblog', ['blogs' => $blogs, 'cats' => $cats]);

            } else {
                show_error('Ошибка! Изменение невозможно, вы не автор данной статьи!');
            }
        } else {
            show_error('Ошибка! Данной статьи не существует!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы редактировать статьи, необходимо');
    }

    App::view('includes/back', ['link' => '/blog/blog/?act=view&amp;id='.$id, 'title' => 'Вернуться']);
    App::view('includes/back', ['link' => '/blog', 'title' => 'К блогам', 'icon' => 'fa-arrow-circle-up']);

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
                if (utf_strlen($text) >= 100 && utf_strlen($text) <= App::setting('maxblogpost')) {
                    if (utf_strlen($tags) >= 2 && utf_strlen($tags) <= 50) {
                        $querycats = DB::run() -> querySingle("SELECT `id` FROM `catsblog` WHERE `id`=? LIMIT 1;", [$cats]);
                        if (!empty($cats)) {
                            $blogs = DB::run() -> queryFetch("SELECT * FROM `blogs` WHERE `id`=? LIMIT 1;", [$id]);

                            if (!empty($blogs)) {
                                if ($blogs['user'] == App::getUsername()) {

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
                    show_error('Ошибка! Слишком длинный или короткий текст статьи (от 100 до '.App::setting('maxblogpost').' символов)!');
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

    App::view('includes/back', ['link' => '/blog/blog?act=editblog&amp;id='.$id, 'title' => 'Вернуться']);
    App::view('includes/back', ['link' => '/blog/blog?act=view&amp;id='.$id, 'title' => 'К статье', 'icon' => 'fa-arrow-circle-up']);
break;

############################################################################################
##                                Просмотр по категориям                                  ##
############################################################################################
case 'blogs':

    //App::setting('newtitle') = 'Статьи пользователей';

    $total = DB::run() -> querySingle("select COUNT(DISTINCT `user`) from `blogs`");
    $page = App::paginate(App::setting('bloggroup'), $total);

    if ($total > 0) {

        $queryblogs = DB::run() -> query("SELECT COUNT(*) AS cnt, `user` FROM `blogs` GROUP BY `user` ORDER BY cnt DESC LIMIT ".$page['offset'].", ".App::setting('bloggroup').";");
        $blogs = $queryblogs -> fetchAll();

        App::view('blog/blog_blogs', compact('blogs', 'total'));

        App::pagination($page);

    } else {
        show_error('Статей еще нет!');
    }

    App::view('includes/back', ['link' => '/blog', 'title' => 'К блогам']);
break;

############################################################################################
##                                   Создание статьи                                      ##
############################################################################################
case 'new':

    //App::setting('newtitle') = 'Публикация новой статьи';

    if (is_user()) {

        $querycat = DB::run() -> query("SELECT `id`, `name` FROM `catsblog` ORDER BY sort ASC;");
        $cats = $querycat -> fetchAll();

        if (count($cats) > 0) {

            App::view('blog/blog_new', ['cats' => $cats, 'cid' => $cid]);

        } else {
            show_error('Категории блогов еще не созданы!');
        }
    } else {
        show_login('Вы не авторизованы, для создания новой статьи, необходимо');
    }

    App::view('includes/back', ['link' => '/blog', 'title' => 'К блогам']);
break;

############################################################################################
##                                  Публикация скрипта                                    ##
############################################################################################
case 'addblog':

    //App::setting('newtitle') = 'Публикация новой статьи';

    $uid = check(Request::input('uid'));
    $cid = abs(intval(Request::input('cid')));
    $title = check(Request::input('title'));
    $text = check(Request::input('text'));
    $tags = check(Request::input('tags'));

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            if (!empty($cid)) {
                if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
                    if (utf_strlen($text) >= 100 && utf_strlen($text) <= App::setting('maxblogpost')) {
                        if (utf_strlen($tags) >= 2 && utf_strlen($tags) <= 50) {
                            $blogs = DB::run() -> querySingle("SELECT `id` FROM `catsblog` WHERE `id`=? LIMIT 1;", [$cid]);
                            if (!empty($blogs)) {

                                if (is_flood(App::getUsername())) {

                                    $text = antimat($text);

                                    DB::run() -> query("INSERT INTO `blogs` (`category_id`, `user`, `title`, `text`, `tags`, `time`) VALUES (?, ?, ?, ?, ?, ?);", [$cid, App::getUsername(), $title, $text, $tags, SITETIME]);
                                    $lastid = DB::run() -> lastInsertId();

                                    DB::run() -> query("UPDATE `catsblog` SET `count`=`count`+1 WHERE `id`=?;", [$cid]);

                                    DB::run() -> query("UPDATE `users` SET `point`=`point`+5, `money`=`money`+100 WHERE `login`=? LIMIT 1;", [App::getUsername()]);

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
                        show_error('Ошибка! Слишком длинный или короткий текст статьи (от 100 до '.App::setting('maxblogpost').' символов)!');
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

    App::view('includes/back', ['link' => '/blog/blog?act=new&amp;cid='.$cid, 'title' => 'Вернуться']);
break;

############################################################################################
##                                       Оценка статьи                                    ##
############################################################################################
case 'vote':

    $uid = check(Request::input('uid'));
    $vote = check(Request::input('vote'));

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            if (App::user('point') >= App::setting('blogvotepoint')){
                if ($vote == 'up' || $vote == 'down') {

                    $score = ($vote == 'up') ? 1 : -1;

                    $blogs = DB::run() -> queryFetch("SELECT * FROM `blogs` WHERE `id`=? LIMIT 1;", [$id]);

                    if (!empty($blogs)) {
                        if (App::getUsername() != $blogs['user']) {
                            $queryrated = DB::run() -> querySingle("SELECT `id` FROM `pollings` WHERE relate_type=? AND `relate_id`=? AND `user`=? LIMIT 1;", ['blog', $id, App::getUsername()]);

                            if (empty($queryrated)) {
                                $expiresrated = SITETIME + 3600 * App::setting('blogexprated');

                                DB::run() -> query("DELETE FROM `pollings` WHERE relate_type=? AND `time`<?;", ['blog', SITETIME]);
                                DB::run() -> query("INSERT INTO `pollings` (relate_type, `relate_id`, `user`, `time`) VALUES (?, ?, ?, ?);", ['blog', $id, App::getUsername(), $expiresrated]);
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
                show_error('Ошибка! У вас недостаточно актива для голосования (Необходимо '.points(App::setting('blogvotepoint')).')!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_login('Вы не авторизованы, для голосования за статьи, необходимо');
    }

    App::view('includes/back', ['link' => '/blog/blog?act=view&amp;id='.$id, 'title' => 'Вернуться']);
break;

############################################################################################
##                                      Комментарии                                       ##
############################################################################################
case 'comments':

    $blogs = DB::run() -> queryFetch("SELECT * FROM `blogs` WHERE `id`=? LIMIT 1;", [$id]);

    if (!empty($blogs)) {
        //App::setting('newtitle') = 'Комментарии - '.$blogs['title'];

        $total = DB::run() -> querySingle("SELECT count(*) FROM `comments` WHERE relate_type=? AND `relate_id`=?;", ['blog', $id]);
        $page = App::paginate(App::setting('blogcomm'), $total);

        if ($total > 0) {

            $querycomm = DB::run() -> query("SELECT * FROM `comments` WHERE relate_type=? AND `relate_id`=? ORDER BY `time` ASC LIMIT ".$page['offset'].", ".App::setting('blogcomm').";", ['blog', $id]);
            $comments = $querycomm -> fetchAll();

            App::view('blog/blog_comments', ['blogs' => $blogs, 'comments' => $comments, 'is_admin' => is_admin(), 'page' => $page]);

            App::pagination($page);
        } else {
            show_error('Комментариев еще нет!');
        }

        if (is_user()) {
            App::view('blog/blog_comments_form', ['blogs' => $blogs]);
        } else {
            show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
        }
    } else {
        show_error('Ошибка! Данной статьи не существует!');
    }

    App::view('includes/back', ['link' => '/blog/blog?act=view&amp;id='.$id, 'title' => 'Вернуться']);
    App::view('includes/back', ['link' => '/blog', 'title' => 'К блогам', 'icon' => 'fa-arrow-circle-up']);
break;

############################################################################################
##                                Добавление комментариев                                 ##
############################################################################################
case 'add':

    $uid = check(Request::input('uid'));
    $id = abs(intval(Request::input('id')));
    $msg = check(Request::input('msg'));

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            if (utf_strlen($msg) >= 5 && utf_strlen($msg) < 1000) {
                $queryblog = DB::run() -> querySingle("SELECT `category_id` FROM `blogs` WHERE `id`=? LIMIT 1;", [$id]);

                if (!empty($queryblog)) {
                    if (is_flood(App::getUsername())) {

                        $msg = antimat($msg);

                        DB::run() -> query("INSERT INTO `comments` (relate_type, `relate_category_id`, `relate_id`, `text`, `user`, `time`, `ip`, `brow`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", ['blog', $queryblog, $id, $msg, App::getUsername(), SITETIME, App::getClientIp(), App::getUserAgent()]);

                        DB::run() -> query("DELETE FROM `comments` WHERE relate_type=? AND `relate_id`=? AND `time` < (SELECT MIN(`time`) FROM (SELECT `time` FROM `comments` WHERE `relate_type`=? AND `relate_id`=? ORDER BY `time` DESC LIMIT ".App::setting('maxblogcomm').") AS del);", ['blog', $id, 'blog', $id]);

                        DB::run() -> query("UPDATE `blogs` SET `comments`=`comments`+1 WHERE `id`=?;", [$id]);
                        DB::run() -> query("UPDATE `users` SET `allcomments`=`allcomments`+1, `point`=`point`+1, `money`=`money`+5 WHERE `login`=?", [App::getUsername()]);

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

    App::view('includes/back', ['link' => '/blog/blog?act=comments&amp;id='.$id, 'title' => 'Вернуться']);
break;

############################################################################################
##                                    Жалоба на спам                                      ##
############################################################################################
case 'spam':

    $uid = check(Request::input('uid'));
    $pid = abs(intval(Request::input('pid')));

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            $data = DB::run() -> queryFetch("SELECT * FROM `comments` WHERE relate_type=? AND `id`=? LIMIT 1;", ['blog', $pid]);

            if (!empty($data)) {
                $queryspam = DB::run() -> querySingle("SELECT `id` FROM `spam` WHERE relate=? AND `idnum`=? LIMIT 1;", [6, $pid]);

                if (empty($queryspam)) {
                    if (is_flood(App::getUsername())) {
                        DB::run() -> query("INSERT INTO `spam` (relate, `idnum`, `user`, `login`, `text`, `time`, `addtime`, `link`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", [6, $data['id'], App::getUsername(), $data['user'], $data['text'], $data['time'], SITETIME, App::setting('home').'/blog/blog?act=comments&amp;id='.$id.'&amp;page='.$page]);

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

    App::view('includes/back', ['link' => '/blog/blog?act=comments&amp;id='.$id.'&amp;page='.$page, 'title' => 'Вернуться']);
break;

############################################################################################
##                                   Ответ на сообщение                                   ##
############################################################################################
case 'reply':

    $id = abs(intval(Request::input('id')));
    $pid = abs(intval(Request::input('pid')));

    if (is_user()) {
        $post = DB::run() -> queryFetch("SELECT * FROM `comments` WHERE relate_type=? AND `id`=? LIMIT 1;", ['blog', $pid]);

        if (!empty($post)) {
            App::view('blog/blog_reply', ['post' => $post, 'id' => $id]);
        } else {
            show_error('Ошибка! Выбранное вами сообщение для ответа не существует!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы отвечать на сообщения, необходимо');
    }

    App::view('includes/back', ['link' => '/blog/blog?act=comments&amp;id='.$id.'&amp;page='.$page, 'title' => 'Вернуться']);
break;

############################################################################################
##                                   Цитирование сообщения                                ##
############################################################################################
case 'quote':

    $pid = abs(intval(Request::input('pid')));


    if (is_user()) {
        $post = DB::run() -> queryFetch("SELECT * FROM `comments` WHERE relate_type=? AND `id`=? LIMIT 1;", ['blog', $pid]);

        if (!empty($post)) {
            App::view('blog/blog_quote', ['post' => $post, 'id' => $id]);
        } else {
            show_error('Ошибка! Выбранное вами сообщение для цитирования не существует!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы цитировать сообщения, необходимо');
    }

    App::view('includes/back', ['link' => '/blog/blog?act=comments&amp;id='.$id.'&amp;page='.$page, 'title' => 'Вернуться']);
break;

############################################################################################
##                                Подготовка к редактированию                             ##
############################################################################################
case 'edit':

    //App::setting('newtitle') = 'Редактирование сообщения';

    $pid = abs(intval(Request::input('pid')));

    if (is_user()) {
        $post = DB::run() -> queryFetch("SELECT * FROM `comments` WHERE relate_type=? AND `id`=? AND `user`=? LIMIT 1;", ['blog', $pid, App::getUsername()]);

        if (!empty($post)) {
            if ($post['time'] + 600 > SITETIME) {

                App::view('blog/blog_edit', ['post' => $post, 'pid' => $pid, 'page' => $page]);
            } else {
                show_error('Ошибка! Редактирование невозможно, прошло более 10 минут!!');
            }
        } else {
            show_error('Ошибка! Сообщение удалено или вы не автор этого сообщения!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы редактировать сообщения, необходимо');
    }

    App::view('includes/back', ['link' => '/blog/blog?act=comments&amp;id='.$id.'&amp;apage='.$page, 'title' => 'Вернуться']);
break;

############################################################################################
##                                    Редактирование сообщения                            ##
############################################################################################
case 'editpost':

    $uid = check(Request::input('uid'));
    $pid = abs(intval(Request::input('pid')));
    $msg = check(Request::input('msg'));

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            if (utf_strlen($msg) >= 5 && utf_strlen($msg) < 1000) {
                $post = DB::run() -> queryFetch("SELECT * FROM `comments` WHERE relate_type=? AND `id`=? AND `user`=? LIMIT 1;", ['blog', $pid, App::getUsername()]);

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

    App::view('includes/back', ['link' => '/blog/blog?act=edit&amp;id='.$id.'&amp;pid='.$pid.'&amp;page='.$page, 'title' => 'Вернуться']);
break;

############################################################################################
##                                 Удаление комментариев                                  ##
############################################################################################
case 'del':

    $uid = check(Request::input('uid'));

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

    App::view('includes/back', ['link' => '/blog/blog?act=comments&amp;id='.$id.'&amp;page='.$page, 'title' => 'Вернуться']);
break;

############################################################################################
##                             Переадресация на последнюю страницу                        ##
############################################################################################
case 'end':

    $query = DB::run() -> queryFetch("SELECT count(*) as `total_comments` FROM `comments` WHERE relate_type=? AND `relate_id`=? LIMIT 1;", ['blog', $id]);

    if (!empty($query['total_comments'])) {

        $total_comments = (empty($query['total_comments'])) ? 1 : $query['total_comments'];
        $end = ceil($total_comments / App::setting('blogpost'));

        redirect("/blog/blog?act=comments&id=$id&page=$end");

    } else {
        show_error('Ошибка! Комментарий к данной статье не существует!');
    }

    App::view('includes/back', ['link' => '/blog', 'title' => 'К блогам']);
break;

endswitch;

App::view(App::setting('themes').'/foot');
