<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Article;
use App\Models\Blog;
use App\Models\Comment;
use App\Models\File;
use App\Models\Flood;
use App\Models\Reader;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $categories = Blog::query()
            ->where('parent_id', 0)
            ->orderBy('sort')
            ->with('children', 'new', 'children.new')
            ->get();

        if ($categories->isEmpty()) {
            abort(200, __('blogs.categories_not_created'));
        }

        return view('blogs/index', compact('categories'));
    }

    /**
     * Список статей
     */
    public function blog(int $id): View
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
     */
    public function view(int $id): View
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

        $tagsList = preg_split('/[\s]*[,][\s]*/', $article->tags);

        $tags = [];
        foreach ($tagsList as $value) {
            $tags[] = '<a href="/blogs/tags/' . check(urlencode($value)) . '">' . check($value) . '</a>';
        }
        $tags = implode(', ', $tags);

        return view('blogs/view', compact('article', 'tags'));
    }

    /**
     * Редактирование статьи
     */
    public function edit(int $id, Request $request, Validator $validator): View|RedirectResponse
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
            abort(200, __('main.article_not_author'));
        }

        if ($request->isMethod('post')) {
            $cid = int($request->input('cid'));
            $title = $request->input('title');
            $text = $request->input('text');
            $tags = $request->input('tags');

            /** @var Blog $category */
            $category = Blog::query()->find($cid);

            $validator
                ->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($title, 3, 50, ['title' => __('validator.text')])
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

                clearCache(['statArticles', 'recentArticles', 'ArticleFeed']);
                setFlash('success', __('blogs.article_success_edited'));

                return redirect('articles/' . $article->id);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $categories = $article->category->getChildren();

        return view('blogs/edit', compact('article', 'categories'));
    }

    /**
     * Просмотр по категориям
     */
    public function authors(): View
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
     */
    public function create(Request $request, Validator $validator, Flood $flood): View|RedirectResponse
    {
        $cid = int($request->input('cid'));

        if (! isAdmin() && ! setting('blog_create')) {
            abort(200, __('main.articles_closed'));
        }

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $categories = (new Blog())->getChildren();

        if ($categories->isEmpty()) {
            abort(404, __('blogs.categories_not_created'));
        }

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $text = $request->input('text');
            $tags = $request->input('tags');

            /** @var Blog $category */
            $category = Blog::query()->find($cid);

            $validator
                ->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($title, 3, 50, ['title' => __('validator.text')])
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

                clearCache(['statArticles', 'recentArticles', 'ArticleFeed']);
                $flood->saveState();

                setFlash('success', __('blogs.article_success_created'));

                return redirect('articles/' . $article->id);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
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
     */
    public function comments(int $id, Request $request, Validator $validator, Flood $flood): View|RedirectResponse
    {
        /** @var Article $article */
        $article = Article::query()->find($id);

        if (! $article) {
            abort(404, __('blogs.article_not_exist'));
        }

        if ($request->isMethod('post')) {
            $user = getUser();
            $msg = $request->input('msg');

            $validator
                ->true($user, __('main.not_authorized'))
                ->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])]);

            if ($validator->isValid()) {
                /** @var Comment $comment */
                $comment = $article->comments()->create([
                    'text'       => antimat($msg),
                    'user_id'    => $user->id,
                    'created_at' => SITETIME,
                    'ip'         => getIp(),
                    'brow'       => getBrowser(),
                ]);

                $user->increment('allcomments');
                $user->increment('point');
                $user->increment('money', 5);

                $article->increment('count_comments');

                $flood->saveState();
                sendNotify($msg, '/articles/comment/' . $article->id . '/' . $comment->id, $article->title);

                setFlash('success', __('main.comment_added_success'));

                return redirect('articles/end/' . $article->id);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $comments = $article->comments()
            ->with('user')
            ->orderBy('created_at')
            ->paginate(setting('comments_per_page'));

        return view('blogs/comments', compact('article', 'comments'));
    }

    /**
     * Подготовка к редактированию комментария
     */
    public function editComment(int $id, int $cid, Request $request, Validator $validator): View|RedirectResponse
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
            abort(200, __('main.comment_deleted'));
        }

        if ($comment->created_at + 600 < SITETIME) {
            abort(200, __('main.editing_impossible'));
        }

        if ($request->isMethod('post')) {
            $msg = $request->input('msg');
            $page = int($request->input('page', 1));

            $validator
                ->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')]);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                setFlash('success', __('main.comment_edited_success'));

                return redirect('articles/comments/' . $article->id . '?page=' . $page);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('blogs/editcomment', compact('article', 'comment', 'page'));
    }

    /**
     * Переадресация на последнюю страницу
     */
    public function end(int $id): RedirectResponse
    {
        /** @var Article $article */
        $article = Article::query()->find($id);

        if (! $article) {
            abort(404, __('blogs.article_not_exist'));
        }

        $total = $article->comments()->count();

        $end = ceil($total / setting('comments_per_page'));

        return redirect('articles/comments/' . $id . '?page=' . $end);
    }

    /**
     * Печать
     */
    public function print(int $id): View
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
     */
    public function rss(): View
    {
        $articles = Article::query()
            ->orderByDesc('created_at')
            ->with('user')
            ->limit(15)
            ->get();

        if ($articles->isEmpty()) {
            abort(200, __('blogs.article_not_exist'));
        }

        return view('blogs/rss', compact('articles'));
    }

    /**
     * RSS комментариев к блогу
     */
    public function rssComments(int $id): View
    {
        $article = Article::query()->where('id', $id)->with('lastComments')->first();

        if (! $article) {
            abort(404, __('blogs.article_not_exist'));
        }

        return view('blogs/rss_comments', compact('article'));
    }

    /**
     * Вывод всех тегов
     */
    public function tags(): View
    {
        $tags = Cache::remember('tagCloud', 3600, static function () {
            $allTags = Article::query()
                ->select('tags')
                ->pluck('tags')
                ->all();

            $stingTag = implode(',', $allTags);
            $dumptags = preg_split('/[\s]*[,][\s]*/', $stingTag, -1, PREG_SPLIT_NO_EMPTY);
            $allTags = array_count_values(array_map('utfLower', $dumptags));

            arsort($allTags);
            array_splice($allTags, 100);
            shuffleAssoc($allTags);

            return $allTags;
        });

        $max = $tags ? max($tags) : 0;
        $min = $tags ? min($tags) : 0;

        return view('blogs/tags', compact('tags', 'max', 'min'));
    }

    /**
     * Поиск по тегам
     */
    public function searchTag(string $tag, Request $request): View|RedirectResponse
    {
        $tag = urldecode($tag);

        if (! isUtf($tag)) {
            $tag = winToUtf($tag);
        }

        if (utfStrlen($tag) < 2) {
            setFlash('danger', __('blogs.tag_search_rule'));

            return redirect('blogs/tags');
        }

        if ($request->session()->missing(['findresult', 'blogfind']) ||
            $tag !== $request->session()->get('blogfind')
        ) {
            $result = Article::query()
                ->select('id')
                ->where('tags', 'like', '%' . $tag . '%')
                ->limit(500)
                ->pluck('id')
                ->all();

            $request->session()->put('blogfind', $tag);
            $request->session()->put('findresult', $result);
        }

        $articles = Article::query()
            ->select('articles.*', 'blogs.name')
            ->whereIn('articles.id', $request->session()->get('findresult'))
            ->join('blogs', 'articles.category_id', 'blogs.id')
            ->orderByDesc('created_at')
            ->with('user')
            ->paginate(setting('blogpost'));

        return view('blogs/tags_search', compact('articles', 'tag'));
    }

    /**
     * Новые статьи
     */
    public function newArticles(): View
    {
        $articles = Article::query()
            ->orderByDesc('created_at')
            ->with('user', 'category')
            ->paginate(setting('blogpost'));

        return view('blogs/new_articles', compact('articles'));
    }

    /**
     * Новые комментарии
     */
    public function newComments(): View
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
     */
    public function userArticles(Request $request): View
    {
        $login = $request->input('user', getUser('login'));
        $user = getUserByLogin($login);

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
     */
    public function userComments(Request $request): View
    {
        $login = $request->input('user', getUser('login'));
        $user = getUserByLogin($login);

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
     */
    public function viewComment(int $id, int $cid): RedirectResponse
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

        return redirect('articles/comments/' . $article->id . '?page=' . $end . '#comment_' . $cid);
    }

    /**
     * Топ статей
     */
    public function top(Request $request): View
    {
        $sort = check($request->input('sort', 'visits'));
        $order = match ($sort) {
            'rating'   => 'rating',
            'comments' => 'count_comments',
            default    => 'visits',
        };

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
     *
     * @return View|RedirectResponse
     */
    public function search(Request $request, Validator $validator)
    {
        $find = check($request->input('find'));
        $articles = collect();

        if ($find) {
            $find = rawurldecode(trim(preg_replace('/[^\w\x7F-\xFF\s]/', ' ', $find)));

            $validator->length($find, 3, 64, ['find' => __('main.request_length')]);
            if ($validator->isValid()) {
                $articles = Article::query()
                    ->whereFullText(['title', 'text'], $find . '*', ['mode' => 'boolean'])
                    ->with('user', 'category')
                    ->paginate(setting('blogpost'))
                    ->appends(compact('find'));

                if ($articles->isEmpty()) {
                    setInput($request->all());
                    setFlash('danger', __('main.empty_found'));

                    return redirect('loads/search');
                }
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('blogs/search', compact('articles', 'find'));
    }

    /**
     * Список всех блогов (Для вывода на главную страницу)
     */
    public function main(): View
    {
        $articles = Article::query()
            ->orderByDesc('created_at')
            ->with('user', 'category.parent')
            ->paginate(setting('blogpost'));

        return view('blogs/main', compact('articles'));
    }
}
