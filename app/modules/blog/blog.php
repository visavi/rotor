<?php

$id = param('id');
$cid = param('cid');

$uz = check(Request::input('uz'));
$page = abs(intval(Request::input('page', 1)));

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    $category = CatsBlog::find($cid);

    if (! $category) {
        App::abort('default', 'Данного раздела не существует!');
    }

    $total = Blog::where('category_id', $cid)->count();

    $page = App::paginate(Setting::get('blogpost'), $total);

    $blogs = Blog::where('category_id', $cid)
        ->orderBy('created_at', 'desc')
        ->offset($page['offset'])
        ->limit(Setting::get('blogpost'))
        ->with('user')
        ->get();

    App::view('blog/blog', compact('blogs', 'category', 'page'));
break;

############################################################################################
##                                   Просмотр статьи                                      ##
############################################################################################
case 'view':

    $blog = Blog::select('blogs.*', 'catsblog.name', 'pollings.vote')
        ->where('blogs.id', $id)
        ->leftJoin('catsblog', function($join){
            $join->on('blogs.category_id', '=', 'catsblog.id');
        })
        ->leftJoin ('pollings', function($join) {
            $join->on('blogs.id', '=', 'pollings.relate_id')
                ->where('pollings.relate_type', Blog::class)
                ->where('pollings.user_id', App::getUserId());
        })
        ->first();

    if (! $blog) {
        App::abort(404, 'Данной статьи не существует!');
    }

    $text = preg_split('|\[nextpage\](<br * /?>)*|', $blog['text'], -1, PREG_SPLIT_NO_EMPTY);

    $total = count($text);
    $page = App::paginate(1, $total);

    if ($page['current'] == 1) {
        $reads = Read::where('relate_type', Blog::class)
            ->where('relate_id', $id)
            ->where('ip', App::getClientIp())
            ->first();

        if (! $reads) {
            $expiresRead = SITETIME + 3600 * Setting::get('blogexpread');

            Read::where('relate_type', Blog::class)
                ->where('created_at', '<', SITETIME)
                ->delete();

            Read::create([
                'relate_type' => Blog::class,
                'relate_id'   => $id,
                'ip'          => App::getClientIp(),
                'created_at'  => $expiresRead,
            ]);

            $blog->update([
                'visits' => Capsule::raw('visits + 1'),
            ]);
        }
    }

    $end = ($total < $page['offset'] + 1) ? $total : $page['offset'] + 1;

    for ($i = $page['offset']; $i < $end; $i++) {
        $blog['text'] = App::bbCode($text[$i]).'<br />';
    }

    $tagsList = preg_split('/[\s]*[,][\s]*/', $blog['tags']);

    $tags = '';
    foreach($tagsList as $key => $value) {
        $comma = (empty($key)) ? '' : ', ';
        $tags .= $comma.'<a href="/blog/tags/'.urlencode($value).'">'.$value.'</a>';
    }

    App::view('blog/blog_view', compact('blog', 'tags', 'page'));

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
                if (utf_strlen($text) >= 100 && utf_strlen($text) <= Setting::get('maxblogpost')) {
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

                                    App::setFlash('success', 'Статья успешно отредактирована!');
                                    App::redirect("/blog/blog?act=view&id=$id");

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
                    show_error('Ошибка! Слишком длинный или короткий текст статьи (от 100 до '.Setting::get('maxblogpost').' символов)!');
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

    //Setting::get('newtitle') = 'Статьи пользователей';

    $total = DB::run() -> querySingle("select COUNT(DISTINCT `user`) from `blogs`");
    $page = App::paginate(Setting::get('bloggroup'), $total);

    if ($total > 0) {

        $queryblogs = DB::run() -> query("SELECT COUNT(*) AS cnt, `user` FROM `blogs` GROUP BY `user` ORDER BY cnt DESC LIMIT ".$page['offset'].", ".Setting::get('bloggroup').";");
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

    //Setting::get('newtitle') = 'Публикация новой статьи';

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

    //Setting::get('newtitle') = 'Публикация новой статьи';

    $uid = check(Request::input('uid'));
    $cid = abs(intval(Request::input('cid')));
    $title = check(Request::input('title'));
    $text = check(Request::input('text'));
    $tags = check(Request::input('tags'));

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            if (!empty($cid)) {
                if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
                    if (utf_strlen($text) >= 100 && utf_strlen($text) <= Setting::get('maxblogpost')) {
                        if (utf_strlen($tags) >= 2 && utf_strlen($tags) <= 50) {
                            $blogs = DB::run() -> querySingle("SELECT `id` FROM `catsblog` WHERE `id`=? LIMIT 1;", [$cid]);
                            if (!empty($blogs)) {

                                if (is_flood(App::getUsername())) {

                                    $text = antimat($text);

                                    DB::run() -> query("INSERT INTO `blogs` (`category_id`, `user`, `title`, `text`, `tags`, `time`) VALUES (?, ?, ?, ?, ?, ?);", [$cid, App::getUsername(), $title, $text, $tags, SITETIME]);
                                    $lastid = DB::run() -> lastInsertId();

                                    DB::run() -> query("UPDATE `catsblog` SET `count`=`count`+1 WHERE `id`=?;", [$cid]);

                                    DB::run() -> query("UPDATE `users` SET `point`=`point`+5, `money`=`money`+100 WHERE `login`=? LIMIT 1;", [App::getUsername()]);

                                    App::setFlash('success', 'Статья успешно опубликована!');
                                    App::redirect("/blog/blog?act=view&id=$lastid");

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
                        show_error('Ошибка! Слишком длинный или короткий текст статьи (от 100 до '.Setting::get('maxblogpost').' символов)!');
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
##                                      Комментарии                                       ##
############################################################################################
case 'comments':

    $blog = Blog::where('id', $id)->first();

    if (! $blog) {
        App::abort('default', 'Данной статьи не существует!');
    }

    if (Request::isMethod('post')) {
        $token = check(Request::input('token'));
        $msg   = check(Request::input('msg'));

        $validation = new Validation();
        $validation
            ->addRule('bool', is_user(), 'Чтобы добавить комментарий необходимо авторизоваться')
            ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            ->addRule('string', $msg, ['msg' => 'Слишком длинное или короткое название!'], true, 5, 1000)
            ->addRule('bool', is_flood(App::getUsername()), ['msg' => 'Антифлуд! Разрешается отправлять сообщения раз в '.flood_period().' секунд!']);

        if ($validation->run()) {
            $msg = antimat($msg);

            Comment::create([
                'relate_type' => Blog::class,
                'relate_id'   => $blog->id,
                'text'        => $msg,
                'user_id'     => App::getUserId(),
                'created_at'  => SITETIME,
                'ip'          => App::getClientIp(),
                'brow'        => App::getUserAgent(),
            ]);

            Capsule::delete('
                DELETE FROM comments WHERE relate_type = :relate_type AND relate_id = :relate_id AND created_at < (
                    SELECT MIN(created_at) FROM (
                        SELECT created_at FROM comments WHERE relate_type = :relate_type2 AND relate_id = :relate_id2 ORDER BY created_at DESC LIMIT '.Setting::get('maxpostgallery').'
                    ) AS del
                );', [
                    'relate_type'  => Blog::class,
                    'relate_id'    => $blog->id,
                    'relate_type2' => Blog::class,
                    'relate_id2'   => $blog->id,
                ]
            );

            $user = User::where('id', App::getUserId());
            $user->update([
                'allcomments' => Capsule::raw('allcomments + 1'),
                'point' => Capsule::raw('point + 1'),
                'money' => Capsule::raw('money + 5'),
            ]);

            $blog->update([
                'comments'  => Capsule::raw('comments + 1'),
            ]);

            App::setFlash('success', 'Комментарий успешно добавлен!');
            App::redirect('/article/'.$blog->id.'/end');
        } else {
            App::setInput(Request::all());
            App::setFlash('danger', $validation->getErrors());
        }
    }

    $total = Comment::where('relate_type', Blog::class)
        ->where('relate_id', $id)
        ->count();

    $page = App::paginate(Setting::get('blogcomm'), $total);

    $comments = Comment::where('relate_type', Blog::class)
        ->where('relate_id', $id)
        ->orderBy('created_at')
        ->offset($page['offset'])
        ->limit(Setting::get('blogcomm'))
        ->get();

    App::view('blog/blog_comments', compact('blog', 'comments', 'page'));
break;

############################################################################################
##                                Подготовка к редактированию                             ##
############################################################################################
case 'edit':

    //Setting::get('newtitle') = 'Редактирование сообщения';

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

                        App::setFlash('success', 'Сообщение успешно отредактировано!');
                        App::redirect("/blog/blog?act=comments&id=$id&page=$page");

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

                App::setFlash('success', 'Выбранные комментарии успешно удалены!');
                App::redirect("/blog/blog?act=comments&id=$id&page=$page");

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

    $blog = Blog::find($id);

    if (empty($blog)) {
        App::abort(404, 'Выбранная вами статья не существует, возможно она была удалена!');
    }

    $total = Comment::where('relate_type', Blog::class)
        ->where('relate_id', $id)
        ->count();

    $end = ceil($total / Setting::get('blogpost'));
    App::redirect('/article/'.$id.'/comments?page='.$end);
break;

endswitch;
