<?php

class BlogController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $blogs = CatsBlog::orderBy('sort')
            ->with('new')
            ->get()
            ->all();

        if (!$blogs) {
            App::abort('default', 'Разделы блогов еще не созданы!');
        }

        App::view('blog/index', compact('blogs'));
    }

    /**
     * Список блогов
     */
    public function blog($cid)
    {
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
    }

    /**
     * Просмотр статьи
     */
    public function view($id)
    {

        $blog = Blog::select('blogs.*', 'catsblog.name', 'pollings.vote')
            ->where('blogs.id', $id)
            ->leftJoin('catsblog', function ($join) {
                $join->on('blogs.category_id', '=', 'catsblog.id');
            })
            ->leftJoin('pollings', function ($join) {
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

                $blog->increment('visits');
            }
        }

        $end = ($total < $page['offset'] + 1) ? $total : $page['offset'] + 1;

        for ($i = $page['offset']; $i < $end; $i++) {
            $blog['text'] = App::bbCode($text[$i]) . '<br>';
        }

        $tagsList = preg_split('/[\s]*[,][\s]*/', $blog['tags']);

        $tags = '';
        foreach ($tagsList as $key => $value) {
            $comma = (empty($key)) ? '' : ', ';
            $tags .= $comma . '<a href="/blog/tags/' . urlencode($value) . '">' . $value . '</a>';
        }

        App::view('blog/view', compact('blog', 'tags', 'page'));
    }

    /**
     * Редактированиe статьи
     */
    public function edit($id)
    {
        if (! is_user()) {
            App::abort(403, 'Для редактирования статьи необходимо авторизоваться');
        }

        $blog = Blog::find($id);

        if (! $blog) {
            App::abort(404, 'Данной статьи не существует!');
        }

        if ($blog->user_id != App::getUserId()) {
            App::abort('default', 'Изменение невозможно, вы не автор данной статьи!');
        }

        $cats = CatsBlog::select('id', 'name')
            ->pluck('name', 'id')
            ->all();

        App::view('blog/edit', compact('blog', 'cats'));
    }

    /**
     * Редактирование статьи
     */
    public function changeblog($id)
    {
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
                            $querycats = DB::run()->querySingle("SELECT `id` FROM `catsblog` WHERE `id`=? LIMIT 1;", [$cats]);
                            if (!empty($cats)) {
                                $blogs = DB::run()->queryFetch("SELECT * FROM `blogs` WHERE `id`=? LIMIT 1;", [$id]);

                                if (!empty($blogs)) {
                                    if ($blogs['user'] == App::getUsername()) {

                                        // Обновление счетчиков
                                        if ($blogs['category_id'] != $cats) {
                                            DB::run()->query("UPDATE `comments` SET `relate_category_id`=? WHERE `relate_id`=?;", [$cats, $id]);
                                            DB::run()->query("UPDATE `catsblog` SET `count`=`count`+1 WHERE `id`=?", [$cats]);
                                            DB::run()->query("UPDATE `catsblog` SET `count`=`count`-1 WHERE `id`=?", [$blogs['category_id']]);
                                        }

                                        DB::run()->query("UPDATE `blogs` SET `category_id`=?, `title`=?, `text`=?, `tags`=? WHERE `id`=?;", [$cats, $title, $text, $tags, $id]);

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
                        show_error('Ошибка! Слишком длинный или короткий текст статьи (от 100 до ' . Setting::get('maxblogpost') . ' символов)!');
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

        App::view('includes/back', ['link' => '/blog/blog?act=editblog&amp;id=' . $id, 'title' => 'Вернуться']);
        App::view('includes/back', ['link' => '/blog/blog?act=view&amp;id=' . $id, 'title' => 'К статье', 'icon' => 'fa-arrow-circle-up']);
    }

    /**
     * Просмотр по категориям
     */
    public function blogs()
    {
        //Setting::get('newtitle') = 'Статьи пользователей';

        $total = DB::run()->querySingle("select COUNT(DISTINCT `user`) from `blogs`");
        $page = App::paginate(Setting::get('bloggroup'), $total);

        if ($total > 0) {

            $queryblogs = DB::run()->query("SELECT COUNT(*) AS cnt, `user` FROM `blogs` GROUP BY `user` ORDER BY cnt DESC LIMIT " . $page['offset'] . ", " . Setting::get('bloggroup') . ";");
            $blogs = $queryblogs->fetchAll();

            App::view('blog/blog_blogs', compact('blogs', 'total'));

            App::pagination($page);

        } else {
            show_error('Статей еще нет!');
        }

        App::view('includes/back', ['link' => '/blog', 'title' => 'К блогам']);
    }

    /**
     * Создание статьи
     */
    public function create()
    {
        $cid = abs(intval(Request::input('cid')));

        if (! is_user()) {
            App::abort(403, 'Для публикации новой статьи необходимо авторизоваться');
        }

        $cats = CatsBlog::select('id', 'name')
            ->pluck('name', 'id')
            ->all();

        if (! $cats) {
            App::abort('default', 'Разделы блогов еще не созданы!');
        }

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $title = check(Request::input('title'));
            $text = check(Request::input('text'));
            $tags = check(Request::input('tags'));

            $category = CatsBlog::find($cid);

            $validation = new Validation();
            $validation
                ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('string', $title, ['title' => 'Слишком длинный или короткий заголовок!'], true, 5, 50)
                ->addRule('string', $text, ['text' => 'Слишком длинный или короткий текст статьи!'], true, 100, Setting::get('maxblogpost'))
                ->addRule('string', $tags, ['tags' => 'Слишком длинные или короткие метки статьи!'], true, 2, 50)
                ->addRule('bool', Flood::isFlood(), ['text' => 'Антифлуд! Разрешается добавлять статьи раз в ' . Flood::getPeriod() . ' секунд!'])
                ->addRule('not_empty', $category, ['cid' => 'Раздела для новой статьи не существует!']);

            if ($validation->run()) {

                $text = antimat($text);

                $article = Blog::create([
                    'category_id' => $cid,
                    'user_id'     => App::getUserId(),
                    'title'       => $title,
                    'text'        => $text,
                    'tags'        => $tags,
                    'created_at'  => SITETIME,
                ]);

                $category->increment('count');

                $user = User::where('id', App::getUserId());
                $user->update([
                    'point' => Capsule::raw('point + 5'),
                    'money' => Capsule::raw('money + 100'),
                ]);

                App::setFlash('success', 'Статья успешно опубликована!');
                App::redirect('/article/'.$article->id);
            } else {
                App::setInput(Request::all());
                App::setFlash('danger', $validation->getErrors());
            }
        }

        App::view('blog/create', ['cats' => $cats, 'cid' => $cid]);
    }

    /**
     * Комментарии
     */
    public function comments($id)
    {
        $blog = Blog::where('id', $id)->first();

        if (!$blog) {
            App::abort('default', 'Данной статьи не существует!');
        }

        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $msg = check(Request::input('msg'));

            $validation = new Validation();
            $validation
                ->addRule('bool', is_user(), 'Чтобы добавить комментарий необходимо авторизоваться')
                ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('string', $msg, ['msg' => 'Слишком длинное или короткое название!'], true, 5, 1000)
                ->addRule('bool', Flood::isFlood(), ['msg' => 'Антифлуд! Разрешается отправлять сообщения раз в ' . Flood::getPeriod() . ' секунд!']);

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

                $user = User::where('id', App::getUserId());
                $user->update([
                    'allcomments' => Capsule::raw('allcomments + 1'),
                    'point'       => Capsule::raw('point + 1'),
                    'money'       => Capsule::raw('money + 5'),
                ]);

                $blog->update([
                    'comments' => Capsule::raw('comments + 1'),
                ]);

                App::setFlash('success', 'Комментарий успешно добавлен!');
                App::redirect('/article/' . $blog->id . '/end');
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
    }


    /**
     * Подготовка к редактированию комментария
     */
    public function editComment($id, $cid)
    {
        $page = abs(intval(Request::input('page', 1)));

        if (!is_user()) {
            App::abort(403, 'Для редактирования комментариев небходимо авторизоваться!');
        }

        $comment = Comment::where('relate_type', Blog::class)
            ->where('id', $cid)
            ->where('user_id', App::getUserId())
            ->first();

        if (!$comment) {
            App::abort('default', 'Комментарий удален или вы не автор этого комментария!');
        }

        if ($comment['created_at'] + 600 < SITETIME) {
            App::abort('default', 'Редактирование невозможно, прошло более 10 минут!');
        }

        App::view('blog/blog_edit', compact('comment', 'page'));
    }

    /**
     * Редактирование комментария
     */
    public function editpost($id)
    {
        $uid = check(Request::input('uid'));
        $pid = abs(intval(Request::input('pid')));
        $msg = check(Request::input('msg'));
        $page = abs(intval(Request::input('page', 1)));

        if (is_user()) {
            if ($uid == $_SESSION['token']) {
                if (utf_strlen($msg) >= 5 && utf_strlen($msg) < 1000) {
                    $post = DB::run()->queryFetch("SELECT * FROM `comments` WHERE relate_type=? AND `id`=? AND `user`=? LIMIT 1;", ['blog', $pid, App::getUsername()]);

                    if (!empty($post)) {
                        if ($post['time'] + 600 > SITETIME) {
                            $msg = antimat($msg);

                            DB::run()->query("UPDATE `comments` SET `text`=? WHERE relate_type=? AND `id`=?", [$msg, 'blog', $pid]);

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

        App::view('includes/back', ['link' => '/blog/blog?act=edit&amp;id=' . $id . '&amp;pid=' . $pid . '&amp;page=' . $page, 'title' => 'Вернуться']);
    }

    /**
     * Удаление комментариев
     */
    public function del($id)
    {
        $uid = check(Request::input('uid'));
        $page = abs(intval(Request::input('page', 1)));

        if (isset($_POST['del'])) {
            $del = intar($_POST['del']);
        } else {
            $del = 0;
        }

        if (is_admin()) {
            if ($uid == $_SESSION['token']) {
                if (!empty($del)) {
                    $del = implode(',', $del);

                    $delcomments = DB::run()->exec("DELETE FROM `comments` WHERE relate_type='blog' AND `id` IN (" . $del . ") AND `relate_id`=" . $id . ";");
                    DB::run()->query("UPDATE `blogs` SET `comments`=`comments`-? WHERE `id`=?;", [$delcomments, $id]);

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

        App::view('includes/back', ['link' => '/blog/blog?act=comments&amp;id=' . $id . '&amp;page=' . $page, 'title' => 'Вернуться']);
    }

    /**
     * Переадресация на последнюю страницу
     */
    public function end($id)
    {

        $blog = Blog::find($id);

        if (empty($blog)) {
            App::abort(404, 'Выбранная вами статья не существует, возможно она была удалена!');
        }

        $total = Comment::where('relate_type', Blog::class)
            ->where('relate_id', $id)
            ->count();

        $end = ceil($total / Setting::get('blogpost'));
        App::redirect('/article/' . $id . '/comments?page=' . $end);
    }

    /**
     * Печать
     */
    public function print($id)
    {
        $blog = Blog::find($id);

        if (empty($blog)) {
            App::abort('default', 'Данной статьи не существует!');
        }

        $blog['text'] = preg_replace('|\[nextpage\](<br * /?>)*|', '', $blog['text']);

        App::view('blog/print', compact('blog'));
    }

    /**
     * RSS всех блогов
     */
    public function rss()
    {
        $blogs = Blog::orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        if ($blogs->isEmpty()) {
            App::abort('default', 'Блоги не найдены!');
        }

        App::view('blog/rss', compact('blogs'));
    }

    /**
     * RSS комментариев к блогу
     */
    public function rssComments($id)
    {
        $blog = Blog::where('id', $id)->with('lastComments')->first();

        if (!$blog) {
            App::abort('default', 'Статья не найдена!');
        }

        App::view('blog/rss_comments', compact('blog'));
    }

    public function tags($tag = null)
    {

        if ($tag) {
            $tag = urldecode($tag);

            if (! is_utf($tag)){
                $tag = win_to_utf($tag);
            }

            if (utf_strlen($tag) < 2) {
                App::setFlash('danger', 'Ошибка! Необходимо не менее 2-х символов в запросе!');
                App::redirect('/blog/tags');
            }

            if (empty($_SESSION['findresult']) || empty($_SESSION['blogfind']) || $tag!=$_SESSION['blogfind']) {

                $result = Blog::select('id')
                    ->where('tags', 'like', '%'.$tag.'%')
                    ->limit(500)
                    ->pluck('id')
                    ->all();

                $_SESSION['blogfind'] = $tag;
                $_SESSION['findresult'] = $result;
            }

            $total = count($_SESSION['findresult']);
            $page = App::paginate(Setting::get('blogpost'), $total);

            $blogs = Blog::select('blogs.*', 'catsblog.name')
                ->whereIn('blogs.id', $_SESSION['findresult'])
                ->join('catsblog', 'blogs.category_id', '=', 'catsblog.id')
                ->orderBy('created_at', 'desc')
                ->offset($page['offset'])
                ->limit(Setting::get('blogpost'))
                ->with('user')
                ->get();

            App::view('blog/tags_search', compact('blogs', 'tag', 'page'));

        } else {
            if (@filemtime(STORAGE."/temp/tagcloud.dat") < time() - 3600) {

                $tags =  Blog::select('tags')
                    ->pluck('tags')
                    ->all();

                $alltag = implode(',', $tags);

                $dumptags = preg_split('/[\s]*[,][\s]*/s', $alltag);
                $tags = array_count_values(array_map('utf_lower', $dumptags));

                arsort($tags);
                array_splice($tags, 100);
                shuffle_assoc($tags);

                file_put_contents(STORAGE."/temp/tagcloud.dat", serialize($tags), LOCK_EX);
            }

            $tags = unserialize(file_get_contents(STORAGE."/temp/tagcloud.dat"));

            $max = max($tags);
            $min = min($tags);

            App::view('blog/tags', compact('tags', 'max', 'min'));
        }
    }
}
