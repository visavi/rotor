<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Flood;
use App\Models\Reader;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;

class BlogController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $categories = Category::query()
            ->where('parent_id', 0)
            ->orderBy('sort')
            ->with('children', 'new', 'children.new')
            ->get();

        if ($categories->isEmpty()) {
            abort('default', 'Разделы блогов еще не созданы!');
        }

        return view('blogs/index', compact('categories'));
    }

    /**
     * Список блогов
     */
    public function blog($id)
    {
        $category = Category::query()->with('parent')->find($id);

        if (! $category) {
            abort(404, 'Данного раздела не существует!');
        }

        $total = Blog::query()->where('category_id', $id)->count();

        $page = paginate(setting('blogpost'), $total);

        $blogs = Blog::query()
            ->where('category_id', $id)
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('user')
            ->get();

        return view('blogs/blogs', compact('blogs', 'category', 'page'));
    }

    /**
     * Просмотр статьи
     */
    public function view($id)
    {
        $blog = Blog::query()
            ->select('blogs.*', 'pollings.vote')
            ->where('blogs.id', $id)
            ->leftJoin('pollings', function (JoinClause $join) {
                $join->on('blogs.id', '=', 'pollings.relate_id')
                    ->where('pollings.relate_type', Blog::class)
                    ->where('pollings.user_id', getUser('id'));
            })
            ->with('category.parent')
            ->first();

        if (! $blog) {
            abort(404, 'Данной статьи не существует!');
        }

        $text = preg_split('|\[nextpage\](<br * /?>)*|', $blog['text'], -1, PREG_SPLIT_NO_EMPTY);

        $total = count($text);
        $page = paginate(1, $total);

        if ($page->current == 1) {
            $reader = Reader::query()
                ->where('relate_type', Blog::class)
                ->where('relate_id', $blog->id)
                ->where('ip', getIp())
                ->first();

            if (! $reader) {
                Reader::query()->create([
                    'relate_type' => Blog::class,
                    'relate_id'   => $blog->id,
                    'ip'          => getIp(),
                    'created_at'  => SITETIME,
                ]);

                $blog->increment('visits');
            }
        }

        $end = ($total < $page->offset + 1) ? $total : $page->offset + 1;

        for ($i = $page->offset; $i < $end; $i++) {
            $blog['text'] = bbCode($text[$i]) . '<br>';
        }

        $tagsList = preg_split('/[\s]*[,][\s]*/', $blog['tags']);

        $tags = '';
        foreach ($tagsList as $key => $value) {
            $comma = (empty($key)) ? '' : ', ';
            $tags .= $comma . '<a href="/blogs/tags/' . urlencode($value) . '">' . $value . '</a>';
        }

        return view('blogs/view', compact('blog', 'tags', 'page'));
    }

    /**
     * Редактирование статьи
     */
    public function edit($id)
    {
        if (! getUser()) {
            abort(403, 'Для редактирования статьи необходимо авторизоваться');
        }

        $blog = Blog::query()->find($id);

        if (! $blog) {
            abort(404, 'Данной статьи не существует!');
        }

        if ($blog->user_id != getUser('id')) {
            abort('default', 'Изменение невозможно, вы не автор данной статьи!');
        }

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $cid   = int(Request::input('cid'));
            $title = check(Request::input('title'));
            $text  = check(Request::input('text'));
            $tags  = check(Request::input('tags'));

            $category = Category::query()->find($cid);

            $validator = new Validator();
            $validator
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($title, 5, 50, ['title' => 'Слишком длинный или короткий заголовок!'])
                ->length($text, 100, setting('maxblogpost'), ['text' => 'Слишком длинный или короткий текст статьи!'])
                ->length($tags, 2, 50, ['tags' => 'Слишком длинные или короткие метки статьи!'])
                ->notEmpty($category, ['cid' => 'Категории для статьи не существует или она закрыта!']);

            if ($category) {
                $validator->empty($category->closed, ['cid' => 'В данном разделе запрещено создавать статьи!']);
            }

            if ($validator->isValid()) {

                // Обновление счетчиков
                if ($blog->category_id != $category->id) {
                    $category->increment('count_blogs');
                    Category::query()->where('id', $blog->category_id)->decrement('count_blogs');
                }

                $blog->update([
                    'category_id' => $category->id,
                    'title'       => $title,
                    'text'        => $text,
                    'tags'        => $tags,
                ]);

                setFlash('success', 'Статья успешно отредактирована!');
                redirect('/articles/'.$blog->id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $categories = Category::query()
            ->where('parent_id', 0)
            ->with('children')
            ->orderBy('sort')
            ->get();

        return view('blogs/edit', compact('blog', 'categories'));
    }

    /**
     * Просмотр по категориям
     */
    public function blogs()
    {
        $total = Blog::query()
            ->distinct()
            ->join('users', 'blogs.user_id', 'users.id')
            ->count('user_id');

        $page = paginate(setting('bloggroup'), $total);

        $blogs = Blog::query()
            ->select('user_id', 'login')
            ->selectRaw('count(*) as cnt, sum(count_comments) as count_comments')
            ->join('users', 'blogs.user_id', 'users.id')
            ->offset($page->offset)
            ->limit($page->limit)
            ->groupBy('user_id')
            ->orderBy('cnt', 'desc')
            ->get();

        return view('blogs/user_blogs', compact('blogs', 'page'));
    }

    /**
     * Создание статьи
     */
    public function create()
    {
        $cid = int(Request::input('cid'));

        if (! getUser()) {
            abort(403, 'Для публикации новой статьи необходимо авторизоваться');
        }

        $cats = Category::query()
            ->where('parent_id', 0)
            ->with('children')
            ->orderBy('sort')
            ->get();

        if (! $cats) {
            abort(404, 'Разделы блогов еще не созданы!');
        }

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $title = check(Request::input('title'));
            $text  = check(Request::input('text'));
            $tags  = check(Request::input('tags'));

            $category = Category::query()->find($cid);

            $validator = new Validator();
            $validator
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($title, 5, 50, ['title' => 'Слишком длинный или короткий заголовок!'])
                ->length($text, 100, setting('maxblogpost'), ['text' => 'Слишком длинный или короткий текст статьи!'])
                ->length($tags, 2, 50, ['tags' => 'Слишком длинные или короткие метки статьи!'])
                ->true(Flood::isFlood(), ['text' => 'Антифлуд! Разрешается добавлять статьи раз в ' . Flood::getPeriod() . ' секунд!'])
                ->notEmpty($category, ['cid' => 'Категории для новой статьи не существует или она закрыта!']);

            if ($category) {
                $validator->empty($category->closed, ['cid' => 'В данном разделе запрещено создавать статьи!']);
            }

            if ($validator->isValid()) {

                $text = antimat($text);

                $article = Blog::query()->create([
                    'category_id' => $cid,
                    'user_id'     => getUser('id'),
                    'title'       => $title,
                    'text'        => $text,
                    'tags'        => $tags,
                    'created_at'  => SITETIME,
                ]);

                $category->increment('count_blogs');

                $user = User::query()->where('id', getUser('id'));
                $user->update([
                    'point' => DB::raw('point + 5'),
                    'money' => DB::raw('money + 100'),
                ]);

                setFlash('success', 'Статья успешно опубликована!');
                redirect('/articles/'.$article->id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('blogs/create', ['cats' => $cats, 'cid' => $cid]);
    }

    /**
     * Комментарии
     */
    public function comments($id)
    {
        $blog = Blog::query()->find($id);

        if (! $blog) {
            abort(404, 'Данной статьи не существует!');
        }

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $msg   = check(Request::input('msg'));

            $validator = new Validator();
            $validator
                ->true(getUser(), 'Для добавления комментария необходимо авторизоваться!')
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($msg, 5, 1000, ['msg' => 'Слишком длинный или короткий комментарий!'])
                ->true(Flood::isFlood(), ['msg' => 'Антифлуд! Разрешается отправлять комментарии раз в ' . Flood::getPeriod() . ' секунд!']);

            if ($validator->isValid()) {

                $msg = antimat($msg);

                $comment = Comment::query()->create([
                    'relate_type' => Blog::class,
                    'relate_id'   => $blog->id,
                    'text'        => $msg,
                    'user_id'     => getUser('id'),
                    'created_at'  => SITETIME,
                    'ip'          => getIp(),
                    'brow'        => getBrowser(),
                ]);

                getUser()->update([
                    'allcomments' => DB::raw('allcomments + 1'),
                    'point'       => DB::raw('point + 1'),
                    'money'       => DB::raw('money + 5'),
                ]);

                $blog->increment('count_comments');

                sendNotify($msg, '/articles/comment/' . $blog->id . '/' . $comment->id, $blog->title);

                setFlash('success', 'Комментарий успешно добавлен!');
                redirect('/articles/end/' . $blog->id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $total = Comment::query()
            ->where('relate_type', Blog::class)
            ->where('relate_id', $id)
            ->count();

        $page = paginate(setting('blogcomm'), $total);

        $comments = Comment::query()
            ->where('relate_type', Blog::class)
            ->where('relate_id', $id)
            ->orderBy('created_at')
            ->offset($page->offset)
            ->limit($page->limit)
            ->get();

        return view('blogs/comments', compact('blog', 'comments', 'page'));
    }

    /**
     * Подготовка к редактированию комментария
     */
    public function editComment($id, $cid)
    {
        $page = int(Request::input('page', 1));
        $blog = Blog::query()->find($id);

        if (! $blog) {
            abort(404, 'Данной статьи не существует!');
        }

        if (! getUser()) {
            abort(403, 'Для редактирования комментариев небходимо авторизоваться!');
        }

        $comment = Comment::query()
            ->where('relate_type', Blog::class)
            ->where('id', $cid)
            ->where('user_id', getUser('id'))
            ->first();

        if (! $comment) {
            abort('default', 'Комментарий удален или вы не автор этого комментария!');
        }

        if ($comment->created_at + 600 < SITETIME) {
            abort('default', 'Редактирование невозможно, прошло более 10 минут!');
        }

        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $msg   = check(Request::input('msg'));
            $page  = int(Request::input('page', 1));

            $validator = new Validator();
            $validator
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($msg, 5, 1000, ['msg' => 'Слишком длинный или короткий комментарий!']);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                setFlash('success', 'Комментарий успешно отредактирован!');
                redirect('/articles/comments/' . $blog->id . '?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('blogs/editcomment', compact('blog', 'comment', 'page'));
    }

    /**
     * Переадресация на последнюю страницу
     */
    public function end($id)
    {
        $blog = Blog::query()->find($id);

        if (! $blog) {
            abort(404, 'Выбранная вами статья не существует, возможно она была удалена!');
        }

        $total = Comment::query()
            ->where('relate_type', Blog::class)
            ->where('relate_id', $id)
            ->count();

        $end = ceil($total / setting('blogpost'));
        redirect('/articles/comments/' . $id . '?page=' . $end);
    }

    /**
     * Печать
     */
    public function print($id)
    {
        $blog = Blog::query()->find($id);

        if (! $blog) {
            abort(404, 'Данной статьи не существует!');
        }

        $blog['text'] = preg_replace('|\[nextpage\](<br * /?>)*|', '', $blog['text']);

        return view('blogs/print', compact('blog'));
    }

    /**
     * RSS всех блогов
     */
    public function rss()
    {
        $blogs = Blog::query()
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        if ($blogs->isEmpty()) {
            abort('default', 'Блоги не найдены!');
        }

        return view('blogs/rss', compact('blogs'));
    }

    /**
     * RSS комментариев к блогу
     */
    public function rssComments($id)
    {
        $blog = Blog::query()->where('id', $id)->with('lastComments')->first();

        if (! $blog) {
            abort(404, 'Статья не найдена!');
        }

        return view('blogs/rss_comments', compact('blog'));
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
                redirect('/blogs/tags');
            }

            if (
                empty($_SESSION['findresult']) ||
                empty($_SESSION['blogfind'])   ||
                $tag != $_SESSION['blogfind']
            ) {
                $result = Blog::query()
                    ->select('id')
                    ->where('tags', 'like', '%'.$tag.'%')
                    ->limit(500)
                    ->pluck('id')
                    ->all();

                $_SESSION['blogfind'] = $tag;
                $_SESSION['findresult'] = $result;
            }

            $total = count($_SESSION['findresult']);
            $page = paginate(setting('blogpost'), $total);

            $blogs = Blog::query()
                ->select('blogs.*', 'categories.name')
                ->whereIn('blogs.id', $_SESSION['findresult'])
                ->join('categories', 'blogs.category_id', '=', 'categories.id')
                ->orderBy('created_at', 'desc')
                ->offset($page->offset)
                ->limit($page->limit)
                ->with('user')
                ->get();

            return view('blogs/tags_search', compact('blogs', 'tag', 'page'));

        }

        if (@filemtime(STORAGE."/temp/tagcloud.dat") < time() - 3600) {

            $allTags = Blog::query()
                ->select('tags')
                ->pluck('tags')
                ->all();

            $stingTag = implode(',', $allTags);
            $dumptags = preg_split('/[\s]*[,][\s]*/s', $stingTag, -1, PREG_SPLIT_NO_EMPTY);
            $allTags  = array_count_values(array_map('utfLower', $dumptags));

            arsort($allTags);
            array_splice($allTags, 100);
            shuffleAssoc($allTags);

            file_put_contents(STORAGE."/temp/tagcloud.dat", json_encode($allTags, JSON_UNESCAPED_UNICODE), LOCK_EX);
        }
        $tags = json_decode(file_get_contents(STORAGE.'/temp/tagcloud.dat'), true);
        $max = max($tags);
        $min = min($tags);

        return view('blogs/tags', compact('tags', 'max', 'min'));
    }

    /**
     * Новые статьи
     */
    public function newArticles()
    {
        $total = Blog::query()->count();

        if ($total > 500) {
            $total = 500;
        }
        $page = paginate(setting('blogpost'), $total);

        $blogs = Blog::query()
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('user')
            ->get();

        return view('blogs/new_articles', compact('blogs', 'page'));
    }

    /**
     * Новые комментарии
     */
    public function newComments()
    {
        $total = Comment::query()->where('relate_type', Blog::class)->count();

        if ($total > 500) {
            $total = 500;
        }
        $page = paginate(setting('blogpost'), $total);

        $comments = Comment::query()
            ->select('comments.*', 'title', 'count_comments')
            ->where('relate_type', Blog::class)
            ->leftJoin('blogs', 'comments.relate_id', '=', 'blogs.id')
            ->offset($page->offset)
            ->limit($page->limit)
            ->orderBy('comments.created_at', 'desc')
            ->with('user')
            ->get();

        return view('blogs/new_comments', compact('comments', 'page'));
    }

    /**
     * Статьи пользователя
     */
    public function userArticles()
    {
        $login = check(Request::input('user', getUser('login')));

        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort(404, 'Пользователь не найден!');
        }

        $total = Blog::query()->where('user_id', $user->id)->count();
        $page  = paginate(setting('blogpost'), $total);

        $blogs = Blog::query()->where('user_id', $user->id)
            ->offset($page->offset)
            ->limit($page->limit)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('blogs/active_articles', compact('blogs', 'user', 'page'));
    }

    /**
     * Комментарии пользователя
     */
    public function userComments()
    {
        $login = check(Request::input('user', getUser('login')));

        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort(404, 'Пользователь не найден!');
        }

        $total = Comment::query()
            ->where('relate_type', Blog::class)
            ->where('user_id', $user->id)
            ->count();
        $page = paginate(setting('blogpost'), $total);

        $comments = Comment::query()
            ->select('comments.*', 'title', 'count_comments')
            ->where('relate_type', Blog::class)
            ->where('comments.user_id', $user->id)
            ->leftJoin('blogs', 'comments.relate_id', '=', 'blogs.id')
            ->offset($page->offset)
            ->limit($page->limit)
            ->orderBy('comments.created_at', 'desc')
            ->with('user')
            ->get();

            return view('blogs/active_comments', compact('comments', 'user', 'page'));
    }

    /**
     * Переход к сообщению
     */
    public function viewComment($id, $cid)
    {
        $blog = Blog::query()->find($id);

        if (! $blog) {
            abort(404, 'Данной статьи не существует!');
        }

        $total = Comment::query()
            ->where('relate_type', Blog::class)
            ->where('relate_id', $id)
            ->where('id', '<=', $cid)
            ->orderBy('created_at')
            ->count();

        $end = ceil($total / setting('blogpost'));
        redirect('/articles/comments/' . $blog->id . '?page=' . $end . '#comment_' . $cid);
    }

    /**
     * Топ статей
     */
    public function top()
    {
        $sort = check(Request::get('sort', 'visits'));

        switch ($sort) {
            case 'rated': $order = 'rating';
                break;
            case 'comments': $order = 'count_comments';
                break;
            default: $order = 'visits';
        }

        $total = Blog::query()->count();
        $page = paginate(setting('blogpost'), $total);

        $blogs = Blog::query()
            ->select('blogs.*', 'categories.name')
            ->leftJoin('categories', 'blogs.category_id', '=', 'categories.id')
            ->offset($page->offset)
            ->limit($page->limit)
            ->orderBy($order, 'desc')
            ->with('user')
            ->get();

        return view('blogs/top', compact('blogs', 'order', 'page'));
    }

    /**
     * Поиск
     */
    public function search()
    {
        $find    = check(Request::input('find'));
        $type    = int(Request::input('type'));
        $where   = int(Request::input('where'));

        if (! getUser()) {
            abort(403, 'Чтобы использовать поиск, необходимо авторизоваться');
        }

        if (empty($find)) {
            return view('blogs/search');
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

                            $result = Blog::query()
                                ->select('id')
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
                            $blogs = Blog::query()
                                ->select('blogs.*', 'categories.name')
                                ->whereIn('blogs.id', $_SESSION['blogfindres'])
                                ->join('categories', 'blogs.category_id', '=', 'categories.id')
                                ->orderBy('created_at', 'desc')
                                ->offset($page->offset)
                                ->limit($page->limit)
                                ->with('user')
                                ->get();

                            return view('blogs/search_title', compact('blogs', 'find', 'page'));
                        } else {
                            setInput(Request::all());
                            setFlash('danger', 'По вашему запросу ничего не найдено!');
                            redirect('/blogs/search');
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

                            $result = Blog::query()
                                ->select('id')
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
                            $blogs = Blog::query()
                                ->select('blogs.*', 'categories.name')
                                ->whereIn('blogs.id', $_SESSION['blogfindres'])
                                ->join('categories', 'blogs.category_id', '=', 'categories.id')
                                ->orderBy('created_at', 'desc')
                                ->offset($page->offset)
                                ->limit($page->limit)
                                ->with('user')
                                ->get();

                            return view('blogs/search_text', compact('blogs', 'find', 'page'));
                        } else {
                            setInput(Request::all());
                            setFlash('danger', 'По вашему запросу ничего не найдено!');
                            redirect('/blogs/search');
                        }
                    }
            } else {
                setInput(Request::all());
                setFlash('danger', ['find' => 'Запрос должен содержать от 3 до 50 символов!']);
                redirect('/blogs/search');
            }
        }
    }
}
