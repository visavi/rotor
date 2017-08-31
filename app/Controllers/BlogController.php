<?php

namespace App\Controllers;

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
            abort('default', 'Разделы блогов еще не созданы!');
        }

        view('blog/index', compact('blogs'));
    }

    /**
     * Список блогов
     */
    public function blog($cid)
    {
        $category = CatsBlog::find($cid);

        if (! $category) {
            abort('default', 'Данного раздела не существует!');
        }

        $total = Blog::where('category_id', $cid)->count();

        $page = paginate(setting('blogpost'), $total);

        $blogs = Blog::where('category_id', $cid)
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit(setting('blogpost'))
            ->with('user')
            ->get();

        view('blog/blog', compact('blogs', 'category', 'page'));
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
                    ->where('pollings.user_id', getUserId());
            })
            ->first();

        if (! $blog) {
            abort(404, 'Данной статьи не существует!');
        }

        $text = preg_split('|\[nextpage\](<br * /?>)*|', $blog['text'], -1, PREG_SPLIT_NO_EMPTY);

        $total = count($text);
        $page = paginate(1, $total);

        if ($page['current'] == 1) {
            $reads = Read::where('relate_type', Blog::class)
                ->where('relate_id', $id)
                ->where('ip', getClientIp())
                ->first();

            if (! $reads) {
                $expiresRead = SITETIME + 3600 * setting('blogexpread');

                Read::where('relate_type', Blog::class)
                    ->where('created_at', '<', SITETIME)
                    ->delete();

                Read::create([
                    'relate_type' => Blog::class,
                    'relate_id'   => $id,
                    'ip'          => getClientIp(),
                    'created_at'  => $expiresRead,
                ]);

                $blog->increment('visits');
            }
        }

        $end = ($total < $page['offset'] + 1) ? $total : $page['offset'] + 1;

        for ($i = $page['offset']; $i < $end; $i++) {
            $blog['text'] = bbCode($text[$i]) . '<br>';
        }

        $tagsList = preg_split('/[\s]*[,][\s]*/', $blog['tags']);

        $tags = '';
        foreach ($tagsList as $key => $value) {
            $comma = (empty($key)) ? '' : ', ';
            $tags .= $comma . '<a href="/blog/tags/' . urlencode($value) . '">' . $value . '</a>';
        }

        view('blog/view', compact('blog', 'tags', 'page'));
    }

    /**
     * Редактирование статьи
     */
    public function edit($id)
    {
        if (! isUser()) {
            abort(403, 'Для редактирования статьи необходимо авторизоваться');
        }

        $blog = Blog::find($id);

        if (! $blog) {
            abort(404, 'Данной статьи не существует!');
        }

        if ($blog->user_id != getUserId()) {
            abort('default', 'Изменение невозможно, вы не автор данной статьи!');
        }

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $cid   = abs(intval(Request::input('cid')));
            $title = check(Request::input('title'));
            $text  = check(Request::input('text'));
            $tags  = check(Request::input('tags'));

            $category = CatsBlog::find($cid);

            $validation = new Validation();
            $validation
                ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('string', $title, ['title' => 'Слишком длинный или короткий заголовок!'], true, 5, 50)
                ->addRule('string', $text, ['text' => 'Слишком длинный или короткий текст статьи!'], true, 100, setting('maxblogpost'))
                ->addRule('string', $tags, ['tags' => 'Слишком длинные или короткие метки статьи!'], true, 2, 50)
                ->addRule('bool', Flood::isFlood(), ['text' => 'Антифлуд! Разрешается добавлять статьи раз в ' . Flood::getPeriod() . ' секунд!'])
                ->addRule('not_empty', $category, ['cid' => 'Раздела для статьи не существует!']);

            if ($validation->run()) {

                // Обновление счетчиков
                if ($blog->category_id != $category->id) {
                    $category->increment('count');
                    CatsBlog::where('id', $blog->category_id)->decrement('count');
                }

                $blog->update([
                    'category_id' => $category->id,
                    'title'       => $title,
                    'text'        => $text,
                    'tags'        => $tags,
                ]);

                setFlash('success', 'Статья успешно отредактирована!');
                redirect('/article/'.$blog->id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validation->getErrors());
            }
        }

        $cats = CatsBlog::select('id', 'name')
            ->pluck('name', 'id')
            ->all();

        view('blog/edit', compact('blog', 'cats'));
    }

    /**
     * Просмотр по категориям
     */
    public function blogs()
    {
        $total = Blog::distinct('user_id')
            ->join('users', 'blogs.user_id', '=', 'users.id')
            ->count('user_id');

        $page = paginate(setting('bloggroup'), $total);

        $blogs = Blog::select('user_id', 'login')
            ->selectRaw('count(*) as cnt, sum(comments) as comments')
            ->join('users', 'blogs.user_id', '=', 'users.id')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->groupBy('user_id')
            ->orderBy('cnt', 'desc')
            ->get();

        view('blog/user_blogs', compact('blogs', 'page'));
    }

    /**
     * Создание статьи
     */
    public function create()
    {
        $cid = abs(intval(Request::input('cid')));

        if (! isUser()) {
            abort(403, 'Для публикации новой статьи необходимо авторизоваться');
        }

        $cats = CatsBlog::select('id', 'name')
            ->pluck('name', 'id')
            ->all();

        if (! $cats) {
            abort('default', 'Разделы блогов еще не созданы!');
        }

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $title = check(Request::input('title'));
            $text  = check(Request::input('text'));
            $tags  = check(Request::input('tags'));

            $category = CatsBlog::find($cid);

            $validation = new Validation();
            $validation
                ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('string', $title, ['title' => 'Слишком длинный или короткий заголовок!'], true, 5, 50)
                ->addRule('string', $text, ['text' => 'Слишком длинный или короткий текст статьи!'], true, 100, setting('maxblogpost'))
                ->addRule('string', $tags, ['tags' => 'Слишком длинные или короткие метки статьи!'], true, 2, 50)
                ->addRule('bool', Flood::isFlood(), ['text' => 'Антифлуд! Разрешается добавлять статьи раз в ' . Flood::getPeriod() . ' секунд!'])
                ->addRule('not_empty', $category, ['cid' => 'Раздела для новой статьи не существует!']);

            if ($validation->run()) {

                $text = antimat($text);

                $article = Blog::create([
                    'category_id' => $cid,
                    'user_id'     => getUserId(),
                    'title'       => $title,
                    'text'        => $text,
                    'tags'        => $tags,
                    'created_at'  => SITETIME,
                ]);

                $category->increment('count');

                $user = User::where('id', getUserId());
                $user->update([
                    'point' => DB::raw('point + 5'),
                    'money' => DB::raw('money + 100'),
                ]);

                setFlash('success', 'Статья успешно опубликована!');
                redirect('/article/'.$article->id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validation->getErrors());
            }
        }

        view('blog/create', ['cats' => $cats, 'cid' => $cid]);
    }

    /**
     * Комментарии
     */
    public function comments($id)
    {
        $blog = Blog::where('id', $id)->first();

        if (!$blog) {
            abort('default', 'Данной статьи не существует!');
        }

        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $msg = check(Request::input('msg'));

            $validation = new Validation();
            $validation
                ->addRule('bool', isUser(), 'Чтобы добавить комментарий необходимо авторизоваться')
                ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('string', $msg, ['msg' => 'Слишком длинное или короткое название!'], true, 5, 1000)
                ->addRule('bool', Flood::isFlood(), ['msg' => 'Антифлуд! Разрешается отправлять сообщения раз в ' . Flood::getPeriod() . ' секунд!']);

            if ($validation->run()) {
                $msg = antimat($msg);

                Comment::create([
                    'relate_type' => Blog::class,
                    'relate_id'   => $blog->id,
                    'text'        => $msg,
                    'user_id'     => getUserId(),
                    'created_at'  => SITETIME,
                    'ip'          => getClientIp(),
                    'brow'        => getUserAgent(),
                ]);

                $user = User::where('id', getUserId());
                $user->update([
                    'allcomments' => DB::raw('allcomments + 1'),
                    'point'       => DB::raw('point + 1'),
                    'money'       => DB::raw('money + 5'),
                ]);

                $blog->update([
                    'comments' => DB::raw('comments + 1'),
                ]);

                setFlash('success', 'Комментарий успешно добавлен!');
                redirect('/article/' . $blog->id . '/end');
            } else {
                setInput(Request::all());
                setFlash('danger', $validation->getErrors());
            }
        }

        $total = Comment::where('relate_type', Blog::class)
            ->where('relate_id', $id)
            ->count();

        $page = paginate(setting('blogcomm'), $total);

        $comments = Comment::where('relate_type', Blog::class)
            ->where('relate_id', $id)
            ->orderBy('created_at')
            ->offset($page['offset'])
            ->limit(setting('blogcomm'))
            ->get();

        view('blog/comments', compact('blog', 'comments', 'page'));
    }

    /**
     * Подготовка к редактированию комментария
     */
    public function editComment($id, $cid)
    {
        $page = abs(intval(Request::input('page', 1)));

        if (!isUser()) {
            abort(403, 'Для редактирования комментариев небходимо авторизоваться!');
        }

        $comment = Comment::where('relate_type', Blog::class)
            ->where('id', $cid)
            ->where('user_id', getUserId())
            ->first();

        if (!$comment) {
            abort('default', 'Комментарий удален или вы не автор этого комментария!');
        }

        if ($comment['created_at'] + 600 < SITETIME) {
            abort('default', 'Редактирование невозможно, прошло более 10 минут!');
        }

        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $msg   = check(Request::input('msg'));
            $page  = abs(intval(Request::input('page', 1)));

            $validation = new Validation();
            $validation
                ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('string', $msg, ['msg' => 'Слишком длинный или короткий комментарий!'], true, 5, 1000);

            if ($validation->run()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                setFlash('success', 'Комментарий успешно отредактирован!');
                redirect('/article/' . $id . '/comments?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validation->getErrors());
            }
        }

        view('blog/editcomment', compact('comment', 'page'));
    }

    /**
     * Переадресация на последнюю страницу
     */
    public function end($id)
    {

        $blog = Blog::find($id);

        if (empty($blog)) {
            abort(404, 'Выбранная вами статья не существует, возможно она была удалена!');
        }

        $total = Comment::where('relate_type', Blog::class)
            ->where('relate_id', $id)
            ->count();

        $end = ceil($total / setting('blogpost'));
        redirect('/article/' . $id . '/comments?page=' . $end);
    }

    /**
     * Печать
     */
    public function print($id)
    {
        $blog = Blog::find($id);

        if (empty($blog)) {
            abort('default', 'Данной статьи не существует!');
        }

        $blog['text'] = preg_replace('|\[nextpage\](<br * /?>)*|', '', $blog['text']);

        view('blog/print', compact('blog'));
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
            abort('default', 'Блоги не найдены!');
        }

        view('blog/rss', compact('blogs'));
    }

    /**
     * RSS комментариев к блогу
     */
    public function rssComments($id)
    {
        $blog = Blog::where('id', $id)->with('lastComments')->first();

        if (!$blog) {
            abort('default', 'Статья не найдена!');
        }

        view('blog/rss_comments', compact('blog'));
    }

    /**
     * Поиск по тегам
     */
    public function tags($tag = null)
    {
        if ($tag) {
            $tag = urldecode($tag);

            if (! isUtf($tag)){
                $tag = winToUtf($tag);
            }

            if (utfStrlen($tag) < 2) {
                setFlash('danger', 'Ошибка! Необходимо не менее 2-х символов в запросе!');
                redirect('/blog/tags');
            }

            if (
                empty($_SESSION['findresult']) ||
                empty($_SESSION['blogfind'])   ||
                $tag != $_SESSION['blogfind']
            ) {
                $result = Blog::select('id')
                    ->where('tags', 'like', '%'.$tag.'%')
                    ->limit(500)
                    ->pluck('id')
                    ->all();

                $_SESSION['blogfind'] = $tag;
                $_SESSION['findresult'] = $result;
            }

            $total = count($_SESSION['findresult']);
            $page = paginate(setting('blogpost'), $total);

            $blogs = Blog::select('blogs.*', 'catsblog.name')
                ->whereIn('blogs.id', $_SESSION['findresult'])
                ->join('catsblog', 'blogs.category_id', '=', 'catsblog.id')
                ->orderBy('created_at', 'desc')
                ->offset($page['offset'])
                ->limit(setting('blogpost'))
                ->with('user')
                ->get();

            view('blog/tags_search', compact('blogs', 'tag', 'page'));

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
                shuffleAssoc($tags);

                file_put_contents(STORAGE."/temp/tagcloud.dat", serialize($tags), LOCK_EX);
            }

            $tags = unserialize(file_get_contents(STORAGE."/temp/tagcloud.dat"));

            $max = max($tags);
            $min = min($tags);

            view('blog/tags', compact('tags', 'max', 'min'));
        }
    }

    /**
     * Новые статьи
     */
    public function newArticles()
    {
        $total = Blog::count();

        if ($total > 500) {
            $total = 500;
        }
        $page = paginate(setting('blogpost'), $total);

        $blogs = Blog::orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit(setting('blogpost'))
            ->with('user')
            ->get();

        view('blog/new_articles', compact('blogs', 'page'));
    }

    /**
     * Новые комментарии
     */
    public function newComments()
    {
        $total = Comment::where('relate_type', Blog::class)->count();

        if ($total > 500) {
            $total = 500;
        }
        $page = paginate(setting('blogpost'), $total);

        $comments = Comment::select('comments.*', 'title', 'comments')
            ->where('relate_type', Blog::class)
            ->leftJoin('blogs', 'comments.relate_id', '=', 'blogs.id')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('comments.created_at', 'desc')
            ->with('user')
            ->get();

        view('blog/new_comments', compact('comments', 'page'));
    }

    /**
     * Статьи пользователя
     */
    public function userArticles()
    {
        $login = check(Request::input('user', getUsername()));

        $user = User::where('login', $login)->first();

        if (! $user) {
            abort('default', 'Пользователь не найден!');
        }

        $total = Blog::where('user_id', $user->id)->count();
        $page  = paginate(setting('blogpost'), $total);

        $blogs = Blog::where('user_id', $user->id)
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('created_at', 'desc')
            ->get();

        view('blog/active_articles', compact('blogs', 'user', 'page'));
    }

    /**
     * Комментарии пользователя
     */
    public function userComments()
    {
        $login = check(Request::input('user', getUsername()));

        $user = User::where('login', $login)->first();

        if (! $user) {
            abort('default', 'Пользователь не найден!');
        }

        $total = Comment::where('relate_type', Blog::class)
            ->where('user_id', $user->id)
            ->count();
        $page = paginate(setting('blogpost'), $total);

        $comments = Comment::select('comments.*', 'title', 'comments')
            ->where('relate_type', Blog::class)
            ->where('comments.user_id', $user->id)
            ->leftJoin('blogs', 'comments.relate_id', '=', 'blogs.id')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('comments.created_at', 'desc')
            ->with('user')
            ->get();

            view('blog/active_comments', compact('comments', 'user', 'page'));
    }

    /**
     * Переход к сообщению
     */
    public function viewComment($id, $сid)
    {
        $total = Comment::where('relate_type', Blog::class)
            ->where('relate_id', $id)
            ->where('id', '<=', $сid)
            ->orderBy('created_at')
            ->count();

        if ($total) {
            $end = ceil($total / setting('blogpost'));
            redirect('/article/' . $id . '/comments?page=' . $end);
        } else {
            setFlash('success', 'Комментариев к данной статье не существует!');
            redirect('/article/' . $id . '/comments');
        }
    }

    /**
     * Топ статей
     */
    public function top()
    {
        $sort =check(Request::get('sort', 'visits'));

        switch ($sort) {
            case 'rated': $order = 'rating';
                break;
            case 'comm': $order = 'comments';
                break;
            default: $order = 'visits';
        }

        $total = Blog::count();
        $page = paginate(setting('blogpost'), $total);

        $blogs = Blog::select('blogs.*', 'catsblog.name')
            ->leftJoin('catsblog', 'blogs.category_id', '=', 'catsblog.id')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy($order, 'desc')
            ->with('user')
            ->get();

        view('blog/top', compact('blogs', 'order', 'page'));
    }

    /**
     * Поиск
     */
    public function search()
    {
        $find    = check(Request::input('find'));
        $type    = abs(intval(Request::input('type')));
        $where   = abs(intval(Request::input('where')));

        if (! isUser()) {
            abort('default', 'Чтобы использовать поиск, необходимо авторизоваться');
        }

        if (empty($find)) {
            view('blog/search');
        } else {

            if (! isUtf($find)) {
                $find = winToUtf($find);
            }

            if (utfStrlen($find) >= 3 && utfStrlen($find) <= 50) {
                $findme = utfLower($find);
                $findmewords = explode(" ", $findme);

                $arrfind = [];
                foreach ($findmewords as $valfind) {
                    if (utfStrlen($valfind) >= 3) {
                        $arrfind[] = $valfind;
                    }
                }
                array_splice($arrfind, 3);

                    $types = (empty($type)) ? 'AND' : 'OR';
                    $wheres = (empty($where)) ? 'title' : 'text';

                    $blogfind = ($types . $wheres . $find);

                    // ----------------------------- Поиск в названии -------------------------------//
                    if ($wheres == 'title') {

                        if ($type == 2) {
                            $arrfind[0] = $findme;
                        }
                        $search1 = (isset($arrfind[1]) && $type != 2) ? $types . " `title` LIKE '%" . $arrfind[1] . "%'" : '';
                        $search2 = (isset($arrfind[2]) && $type != 2) ? $types . " `title` LIKE '%" . $arrfind[2] . "%'" : '';

                        if (empty($_SESSION['blogfindres']) || $blogfind != $_SESSION['blogfind']) {

                            $result = Blog::select('id')
                                ->whereRaw("title like '%".$arrfind[0]."%'".$search1.$search2)
                                ->limit(500)
                                ->pluck('id')
                                ->all();

                            $_SESSION['blogfind'] = $blogfind;
                            $_SESSION['blogfindres'] = $result;
                        }

                        $total = count($_SESSION['blogfindres']);
                        $page = paginate(setting('blogpost'), $total);

                        if ($total > 0) {
                            $blogs = Blog::select('blogs.*', 'catsblog.name')
                                ->whereIn('blogs.id', $_SESSION['blogfindres'])
                                ->join('catsblog', 'blogs.category_id', '=', 'catsblog.id')
                                ->orderBy('created_at', 'desc')
                                ->offset($page['offset'])
                                ->limit(setting('blogpost'))
                                ->with('user')
                                ->get();

                            view('blog/search_title', compact('blogs', 'find', 'page'));
                        } else {
                            setInput(Request::all());
                            setFlash('danger', 'По вашему запросу ничего не найдено!');
                            redirect('/blog/search');
                        }
                    }
                    // --------------------------- Поиск в текте -------------------------------//
                    if ($wheres == 'text') {

                        if ($type == 2) {
                            $arrfind[0] = $findme;
                        }
                        $search1 = (isset($arrfind[1]) && $type != 2) ? $types . " `text` LIKE '%" . $arrfind[1] . "%'" : '';
                        $search2 = (isset($arrfind[2]) && $type != 2) ? $types . " `text` LIKE '%" . $arrfind[2] . "%'" : '';

                        if (empty($_SESSION['blogfindres']) || $blogfind != $_SESSION['blogfind']) {

                            $result = Blog::select('id')
                                ->whereRaw("text like '%".$arrfind[0]."%'".$search1.$search2)
                                ->limit(500)
                                ->pluck('id')
                                ->all();

                            $_SESSION['blogfind'] = $blogfind;
                            $_SESSION['blogfindres'] = $result;
                        }

                        $total = count($_SESSION['blogfindres']);
                        $page = paginate(setting('blogpost'), $total);

                        if ($total > 0) {
                            $blogs = Blog::select('blogs.*', 'catsblog.name')
                                ->whereIn('blogs.id', $_SESSION['blogfindres'])
                                ->join('catsblog', 'blogs.category_id', '=', 'catsblog.id')
                                ->orderBy('created_at', 'desc')
                                ->offset($page['offset'])
                                ->limit(setting('blogpost'))
                                ->with('user')
                                ->get();

                            view('blog/search_text', compact('blogs', 'find', 'page'));
                        } else {
                            setInput(Request::all());
                            setFlash('danger', 'По вашему запросу ничего не найдено!');
                            redirect('/blog/search');
                        }
                    }
            } else {
                setInput(Request::all());
                setFlash('danger', ['find' => 'Запрос должен содержать от 3 до 50 символов!']);
                redirect('/blog/search');
            }
        }
    }
}
