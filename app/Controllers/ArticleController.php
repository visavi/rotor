<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Article;
use App\Models\Blog;
use App\Models\Comment;
use App\Models\File;
use App\Models\Flood;
use App\Models\Reader;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ArticleController extends BaseController
{
    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $categories = Blog::query()
            ->where('parent_id', 0)
            ->orderBy('sort')
            ->with('children', 'new', 'children.new')
            ->get();

        if ($categories->isEmpty()) {
            abort('default', __('blogs.categories_not_created'));
        }

        return view('blogs/index', compact('categories'));
    }

    /**
     * Список статей
     *
     * @param int $id
     *
     * @return string
     */
    public function blog(int $id): string
    {
        $category = Blog::query()->with('parent')->find($id);

        if (! $category) {
            abort(404, __('blogs.category_not_exist'));
        }

        $articles = Article::query()
            ->select('articles.*', 'pollings.vote')
            ->leftJoin('pollings', static function (JoinClause $join) {
                $join->on('articles.id', 'pollings.relate_id')
                    ->where('pollings.relate_type', Article::$morphName)
                    ->where('pollings.user_id', getUser('id'));
            })
            ->where('category_id', $id)
            ->orderByDesc('created_at')
            ->with('user')
            ->paginate(setting('blogpost'));

        return view('blogs/blog', compact('articles', 'category'));
    }

    /**
     * Просмотр статьи
     *
     * @param int $id
     *
     * @return string
     */
    public function view(int $id): string
    {
        /** @var Article $article */
        $article = Article::query()
            ->select('articles.*', 'pollings.vote')
            ->where('articles.id', $id)
            ->leftJoin('pollings', static function (JoinClause $join) {
                $join->on('articles.id', 'pollings.relate_id')
                    ->where('pollings.relate_type', Article::$morphName)
                    ->where('pollings.user_id', getUser('id'));
            })
            ->with('category.parent')
            ->first();

        if (! $article) {
            abort(404, __('blogs.article_not_exist'));
        }

        Reader::countingStat($article);

        $article->text = bbCode($article->text) . '<br>';
        $tagsList = preg_split('/[\s]*[,][\s]*/', $article->tags);

        $tags = '';
        foreach ($tagsList as $key => $value) {
            $comma = empty($key) ? '' : ', ';
            $tags .= $comma . '<a href="/blogs/tags/' . urlencode($value) . '">' . $value . '</a>';
        }

        return view('blogs/view', compact('article', 'tags'));
    }

    /**
     * Редактирование статьи
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function edit(int $id, Request $request, Validator $validator): string
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        /** @var Article $article */
        $article = Article::query()->find($id);

        if (! $article) {
            abort(404, __('blogs.article_not_exist'));
        }

        if ($user->id !== $article->user_id) {
            abort('default', __('main.article_not_author'));
        }

        if ($request->isMethod('post')) {
            $cid   = int($request->input('cid'));
            $title = $request->input('title');
            $text  = $request->input('text');
            $tags  = $request->input('tags');

            /** @var Blog $category */
            $category = Blog::query()->find($cid);

            $validator
                ->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
                ->length($title, 5, 50, ['title' => __('validator.text')])
                ->length($text, 100, setting('maxblogpost'), ['text' => __('validator.text')])
                ->length($tags, 2, 50, ['tags' => __('blogs.article_error_tags')])
                ->notEmpty($category, ['cid' => __('blogs.category_not_exist')]);

            if ($category) {
                $validator->empty($category->closed, ['cid' => __('blogs.category_closed')]);
            }

            if ($validator->isValid()) {
                // Обновление счетчиков
                if ($article->category_id !== $category->id) {
                    $category->increment('count_articles');
                    Blog::query()->where('id', $article->category_id)->decrement('count_articles');
                }

                $article->update([
                    'category_id' => $category->id,
                    'title'       => $title,
                    'text'        => $text,
                    'tags'        => $tags,
                ]);

                clearCache(['statArticles', 'recentArticles']);
                setFlash('success', __('blogs.article_success_edited'));
                redirect('/articles/' . $article->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $categories = Blog::query()
            ->where('parent_id', 0)
            ->with('children')
            ->orderBy('sort')
            ->get();

        return view('blogs/edit', compact('article', 'categories'));
    }

    /**
     * Просмотр по категориям
     *
     * @return string
     */
    public function authors(): string
    {
        $articles = Article::query()
            ->select('user_id', 'login')
            ->selectRaw('count(*) as cnt, sum(count_comments) as count_comments')
            ->join('users', 'articles.user_id', 'users.id')
            ->groupBy('user_id')
            ->orderByDesc('cnt')
            ->paginate(setting('bloggroup'));

        return view('blogs/authors', compact('articles'));
    }

    /**
     * Создание статьи
     *
     * @param Request   $request
     * @param Validator $validator
     * @param Flood     $flood
     *
     * @return string
     */
    public function create(Request $request, Validator $validator, Flood $flood): string
    {
        $cid = int($request->input('cid'));

        if (! isAdmin() && ! setting('blog_create')) {
            abort('default', __('main.articles_closed'));
        }

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $categories = Blog::query()
            ->where('parent_id', 0)
            ->with('children')
            ->orderBy('sort')
            ->get();

        if (! $categories) {
            abort(404, __('blogs.categories_not_created'));
        }

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $text  = $request->input('text');
            $tags  = $request->input('tags');

            /** @var Blog $category */
            $category = Blog::query()->find($cid);

            $validator
                ->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
                ->length($title, 5, 50, ['title' => __('validator.text')])
                ->length($text, 100, setting('maxblogpost'), ['text' => __('validator.text')])
                ->length($tags, 2, 50, ['tags' => __('blogs.article_error_tags')])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
                ->notEmpty($category, ['cid' => __('blogs.category_not_exist')]);

            if ($category) {
                $validator->empty($category->closed, ['cid' => __('blogs.category_closed')]);
            }

            if ($validator->isValid()) {
                $text = antimat($text);

                /** @var Article $article */
                $article = Article::query()->create([
                    'category_id' => $cid,
                    'user_id'     => $user->id,
                    'title'       => $title,
                    'text'        => $text,
                    'tags'        => $tags,
                    'created_at'  => SITETIME,
                ]);

                $category->increment('count_articles');

                $user->increment('point', 5);
                $user->increment('money', 100);

                File::query()
                    ->where('relate_type', Article::$morphName)
                    ->where('relate_id', 0)
                    ->where('user_id', $user->id)
                    ->update(['relate_id' => $article->id]);

                clearCache(['statArticles', 'recentArticles']);
                $flood->saveState();

                setFlash('success', __('blogs.article_success_created'));
                redirect('/articles/' . $article->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $files = File::query()
            ->where('relate_type', Article::$morphName)
            ->where('relate_id', 0)
            ->where('user_id', $user->id)
            ->get();

        return view('blogs/create', compact('categories', 'cid', 'files'));
    }

    /**
     * Комментарии
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @param Flood     $flood
     *
     * @return string
     */
    public function comments(int $id, Request $request, Validator $validator, Flood $flood): string
    {
        /** @var Article $article */
        $article = Article::query()->find($id);

        if (! $article) {
            abort(404, __('blogs.article_not_exist'));
        }

        if ($request->isMethod('post')) {
            $user = getUser();
            $msg  = $request->input('msg');

            $validator
                ->true($user, __('main.not_authorized'))
                ->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
                ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])]);

            if ($validator->isValid()) {
                /** @var Comment $comment */
                $comment = $article->comments()->create([
                    'text'        => antimat($msg),
                    'user_id'     => $user->id,
                    'created_at'  => SITETIME,
                    'ip'          => getIp(),
                    'brow'        => getBrowser(),
                ]);

                $user->increment('allcomments');
                $user->increment('point');
                $user->increment('money', 5);

                $article->increment('count_comments');

                $flood->saveState();
                sendNotify($msg, '/articles/comment/' . $article->id . '/' . $comment->id, $article->title);

                setFlash('success', __('main.comment_added_success'));
                redirect('/articles/end/' . $article->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $comments = $article->comments()
            ->with('user')
            ->orderBy('created_at')
            ->paginate(setting('comments_per_page'));

        return view('blogs/comments', compact('article', 'comments'));
    }

    /**
     * Подготовка к редактированию комментария
     *
     * @param int       $id
     * @param int       $cid
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function editComment(int $id, int $cid, Request $request, Validator $validator): string
    {
        $page = int($request->input('page', 1));

        /** @var Article $article */
        $article = Article::query()->find($id);

        if (! $article) {
            abort(404, __('blogs.article_not_exist'));
        }

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $comment = $article->comments()
            ->where('id', $cid)
            ->where('user_id', $user->id)
            ->first();

        if (! $comment) {
            abort('default', __('main.comment_deleted'));
        }

        if ($comment->created_at + 600 < SITETIME) {
            abort('default', __('main.editing_impossible'));
        }

        if ($request->isMethod('post')) {
            $msg  = $request->input('msg');
            $page = int($request->input('page', 1));

            $validator
                ->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
                ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')]);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                setFlash('success', __('main.comment_edited_success'));
                redirect('/articles/comments/' . $article->id . '?page=' . $page);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('blogs/editcomment', compact('article', 'comment', 'page'));
    }

    /**
     * Переадресация на последнюю страницу
     *
     * @param int $id
     *
     * @return void
     */
    public function end(int $id): void
    {
        /** @var Article $article */
        $article = Article::query()->find($id);

        if (! $article) {
            abort(404, __('blogs.article_not_exist'));
        }

        $total = $article->comments()->count();

        $end = ceil($total / setting('comments_per_page'));
        redirect('/articles/comments/' . $id . '?page=' . $end);
    }

    /**
     * Печать
     *
     * @param int $id
     *
     * @return string
     */
    public function print(int $id): string
    {
        /** @var Article $article */
        $article = Article::query()->find($id);

        if (! $article) {
            abort(404, __('blogs.article_not_exist'));
        }

        return view('blogs/print', compact('article'));
    }

    /**
     * RSS всех блогов
     *
     * @return string
     */
    public function rss(): string
    {
        $articles = Article::query()
            ->orderByDesc('created_at')
            ->with('user')
            ->limit(15)
            ->get();

        if ($articles->isEmpty()) {
            abort('default', __('blogs.article_not_exist'));
        }

        return view('blogs/rss', compact('articles'));
    }

    /**
     * RSS комментариев к блогу
     *
     * @param int $id
     *
     * @return string
     */
    public function rssComments(int $id): string
    {
        $article = Article::query()->where('id', $id)->with('lastComments')->first();

        if (! $article) {
            abort(404, __('blogs.article_not_exist'));
        }

        return view('blogs/rss_comments', compact('article'));
    }

    /**
     * Вывод всех тегов
     *
     * @return string
     */
    public function tags(): string
    {
        $tags = Cache::remember('tagCloud', 3600, static function () {
            $allTags = Article::query()
                ->select('tags')
                ->pluck('tags')
                ->all();

            $stingTag = implode(',', $allTags);
            $dumptags = preg_split('/[\s]*[,][\s]*/', $stingTag, -1, PREG_SPLIT_NO_EMPTY);
            $allTags  = array_count_values(array_map('utfLower', $dumptags));

            arsort($allTags);
            array_splice($allTags, 100);
            shuffleAssoc($allTags);

            return $allTags;
        });

        $max = max($tags);
        $min = min($tags);

        return view('blogs/tags', compact('tags', 'max', 'min'));
    }

    /**
     * Поиск по тегам
     *
     * @param string $tag
     *
     * @return string
     */
    public function searchTag(string $tag): string
    {
        $tag = urldecode($tag);

        if (! isUtf($tag)) {
            $tag = winToUtf($tag);
        }

        if (utfStrlen($tag) < 2) {
            setFlash('danger', __('blogs.tag_search_rule'));
            redirect('/blogs/tags');
        }

        if (empty($_SESSION['findresult'])
            || empty($_SESSION['blogfind'])
            || $tag !== $_SESSION['blogfind']
        ) {
            $result = Article::query()
                ->select('id')
                ->where('tags', 'like', '%'.$tag.'%')
                ->limit(500)
                ->pluck('id')
                ->all();

            $_SESSION['blogfind'] = $tag;
            $_SESSION['findresult'] = $result;
        }

        $articles = Article::query()
            ->select('articles.*', 'blogs.name')
            ->whereIn('articles.id', $_SESSION['findresult'])
            ->join('blogs', 'articles.category_id', 'blogs.id')
            ->orderByDesc('created_at')
            ->with('user')
            ->paginate(setting('blogpost'));

        return view('blogs/tags_search', compact('articles', 'tag'));
    }

    /**
     * Новые статьи
     *
     * @return string
     */
    public function newArticles(): string
    {
        $articles = Article::query()
            ->orderByDesc('created_at')
            ->with('user', 'category')
            ->paginate(setting('blogpost'));

        return view('blogs/new_articles', compact('articles'));
    }

    /**
     * Новые комментарии
     *
     * @return string
     */
    public function newComments(): string
    {
        $comments = Comment::query()
            ->select('comments.*', 'title', 'count_comments')
            ->where('relate_type', Article::$morphName)
            ->leftJoin('articles', 'comments.relate_id', 'articles.id')
            ->orderByDesc('comments.created_at')
            ->with('user', 'relate')
            ->paginate(setting('comments_per_page'));

        return view('blogs/new_comments', compact('comments'));
    }

    /**
     * Статьи пользователя
     *
     * @param Request $request
     *
     * @return string
     */
    public function userArticles(Request $request): string
    {
        $login = $request->input('user', getUser('login'));
        $user  = getUserByLogin($login);

        if (! $user) {
            abort(404, __('validator.user'));
        }

        $articles = Article::query()->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->with('user')
            ->paginate(setting('blogpost'))
            ->appends(['user' => $user->login]);

        return view('blogs/active_articles', compact('articles', 'user'));
    }

    /**
     * Комментарии пользователя
     *
     * @param Request $request
     *
     * @return string
     */
    public function userComments(Request $request): string
    {
        $login = $request->input('user', getUser('login'));
        $user  = getUserByLogin($login);

        if (! $user) {
            abort(404, __('validator.user'));
        }

        $comments = Comment::query()
            ->select('comments.*', 'title', 'count_comments')
            ->where('relate_type', Article::$morphName)
            ->where('comments.user_id', $user->id)
            ->leftJoin('articles', 'comments.relate_id', 'articles.id')
            ->orderByDesc('comments.created_at')
            ->with('user', 'relate')
            ->paginate(setting('comments_per_page'))
            ->appends(['user' => $user->login]);

        return view('blogs/active_comments', compact('comments', 'user'));
    }

    /**
     * Переход к сообщению
     *
     * @param int $id
     * @param int $cid
     *
     * @return void
     */
    public function viewComment(int $id, int $cid): void
    {
        /** @var Article $article */
        $article = Article::query()->find($id);

        if (! $article) {
            abort(404, __('blogs.article_not_exist'));
        }

        $total = $article->comments()
            ->where('id', '<=', $cid)
            ->orderBy('created_at')
            ->count();

        $end = ceil($total / setting('comments_per_page'));
        redirect('/articles/comments/' . $article->id . '?page=' . $end . '#comment_' . $cid);
    }

    /**
     * Топ статей
     *
     * @param Request $request
     *
     * @return string
     */
    public function top(Request $request): string
    {
        $sort = check($request->input('sort', 'visits'));

        switch ($sort) {
            case 'rated':
                $order = 'rating';
                break;
            case 'comments':
                $order = 'count_comments';
                break;
            default:
                $order = 'visits';
        }

        $articles = Article::query()
            ->select('articles.*', 'blogs.name')
            ->leftJoin('blogs', 'articles.category_id', 'blogs.id')
            ->orderByDesc($order)
            ->with('user')
            ->paginate(setting('blogpost'))
            ->appends(['sort' => $sort]);



        return view('blogs/top', compact('articles', 'order'));
    }

    /**
     * Поиск
     *
     * @param Request $request
     *
     * @return string
     */
    public function search(Request $request): ?string
    {
        $find  = check($request->input('find'));
        $type  = int($request->input('type'));
        $where = int($request->input('where'));

        if (! getUser()) {
            abort(403, __('main.not_authorized'));
        }

        if (empty($find)) {
            return view('blogs/search');
        }

        if (! isUtf($find)) {
            $find = winToUtf($find);
        }

        $strlen = utfStrlen($find);

        if ($strlen >= 3 && $strlen <= 50) {
            $findme = utfLower($find);
            $findmewords = explode(' ', $findme);

            $arrfind = [];
            foreach ($findmewords as $valfind) {
                if (utfStrlen($valfind) >= 3) {
                    $arrfind[] = $valfind;
                }
            }
            array_splice($arrfind, 3);

                $types = empty($type) ? 'AND' : 'OR';
                $wheres = empty($where) ? 'title' : 'text';

                $blogfind = ($types . $wheres . $find);

            // Поиск в названии
            if ($wheres === 'title') {
                if ($type === 2) {
                    $arrfind[0] = $findme;
                }
                $search1 = isset($arrfind[1]) && $type !== 2 ? $types . " `title` LIKE '%" . $arrfind[1] . "%'" : '';
                $search2 = isset($arrfind[2]) && $type !== 2 ? $types . " `title` LIKE '%" . $arrfind[2] . "%'" : '';

                if (empty($_SESSION['blogfindres']) || $blogfind !== $_SESSION['blogfind']) {
                    $result = Article::query()
                        ->select('id')
                        ->whereRaw("title like '%".$arrfind[0]."%'".$search1.$search2)
                        ->limit(500)
                        ->pluck('id')
                        ->all();

                    $_SESSION['blogfind'] = $blogfind;
                    $_SESSION['blogfindres'] = $result;
                }

                $total = count($_SESSION['blogfindres']);

                if ($total > 0) {
                    $articles = Article::query()
                        ->select('articles.*', 'blogs.name')
                        ->whereIn('articles.id', $_SESSION['blogfindres'])
                        ->join('blogs', 'articles.category_id', 'blogs.id')
                        ->orderByDesc('created_at')
                        ->with('user')
                        ->paginate(setting('blogpost'))
                        ->appends([
                            'find'  => $find,
                            'where' => $where,
                            'type'  => $type,
                        ]);

                    return view('blogs/search_title', compact('articles', 'find'));
                }

                setInput($request->all());
                setFlash('danger', __('main.empty_found'));
                redirect('/blogs/search');
            }
            // Поиск в текте
            if ($wheres === 'text') {
                if ($type === 2) {
                    $arrfind[0] = $findme;
                }
                $search1 = isset($arrfind[1]) && $type !== 2 ? $types . " `text` LIKE '%" . $arrfind[1] . "%'" : '';
                $search2 = isset($arrfind[2]) && $type !== 2 ? $types . " `text` LIKE '%" . $arrfind[2] . "%'" : '';

                if (empty($_SESSION['blogfindres']) || $blogfind !== $_SESSION['blogfind']) {
                    $result = Article::query()
                        ->select('id')
                        ->whereRaw("text like '%".$arrfind[0]."%'".$search1.$search2)
                        ->limit(500)
                        ->pluck('id')
                        ->all();

                    $_SESSION['blogfind'] = $blogfind;
                    $_SESSION['blogfindres'] = $result;
                }

                $total = count($_SESSION['blogfindres']);

                if ($total > 0) {
                    $articles = Article::query()
                        ->select('articles.*', 'blogs.name')
                        ->whereIn('articles.id', $_SESSION['blogfindres'])
                        ->join('blogs', 'articles.category_id', 'blogs.id')
                        ->orderByDesc('created_at')
                        ->with('user')
                        ->paginate(setting('blogpost'))
                        ->appends([
                            'find'  => $find,
                            'where' => $where,
                            'type'  => $type,
                        ]);

                    return view('blogs/search_text', compact('articles', 'find'));
                }

                setInput($request->all());
                setFlash('danger', __('main.empty_found'));
                redirect('/blogs/search');
            }
        } else {
            setInput($request->all());
            setFlash('danger', ['find' => __('main.request_requirements')]);
            redirect('/blogs/search');
        }
    }

    /**
     * Список всех блогов (Для вывода на главную страницу)
     *
     * @return string
     */
    public function main(): string
    {
        $articles = Article::query()
            ->orderByDesc('created_at')
            ->with('user', 'category.parent')
            ->paginate(setting('blogpost'));

        return view('blogs/main', compact('articles'));
    }
}
