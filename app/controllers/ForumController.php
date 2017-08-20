<?php

class ForumController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        include_once(APP . '/views/advert/forum.blade.php');

        $forums = Forum::where('parent_id', 0)
            ->with('lastTopic.lastPost.user')
            ->with('children')
            ->orderBy('sort')
            ->get();

        if (empty(count($forums))) {
            App::abort('default', 'Разделы форума еще не созданы!');
        }

        App::view('forum/index', compact('forums'));
    }

    /**
     * Страница списка тем
     */
    public function forum($fid)
    {
        $forum = Forum::with('parent')->find($fid);

        if (!$forum) {
            App::abort('default', 'Данного раздела не существует!');
        }

        $forum->children = Forum::where('parent_id', $forum->id)
            ->with('lastTopic.lastPost.user')
            ->get();

        $total = Topic::where('forum_id', $fid)->count();

        $page = App::paginate(Setting::get('forumtem'), $total);

        $topics = Topic::where('forum_id', $fid)
            ->orderBy('locked', 'desc')
            ->orderBy('updated_at', 'desc')
            ->limit(Setting::get('forumtem'))
            ->offset($page['offset'])
            ->with('lastPost.user')
            ->get();

        App::view('forum/forum', compact('forum', 'topics', 'page'));
    }

    /**
     * Создание новой темы
     */
    public function create()
    {
        $fid = abs(intval(Request::input('fid')));

        if (! is_user()) App::abort(403);

        $forums = Forum::where('parent_id', 0)
            ->with('children')
            ->orderBy('sort')
            ->get();

        if (empty(count($forums))) {
            App::abort('default', 'Разделы форума еще не созданы!');
        }

        if (Request::isMethod('post')) {

            $title    = check(Request::input('title'));
            $msg      = check(Request::input('msg'));
            $token    = check(Request::input('token'));
            $vote     = Request::has('vote') ? 1 : 0;
            $question = check(Request::input('question'));
            $answers  = check(Request::input('answer'));

            $forum = Forum::find($fid);

            $validation = new Validation();
            $validation -> addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                -> addRule('not_empty', $forum, ['fid' => 'Раздела для новой темы не существует!'])
                -> addRule('empty', $forum['closed'], ['fid' => 'В данном разделе запрещено создавать темы!'])
                -> addRule('equal', [Flood::isFlood(), true], ['msg' => 'Антифлуд! Разрешается cоздавать темы раз в '.Flood::getPeriod().' сек!'])
                -> addRule('string', $title, ['title' => 'Слишком длинное или короткое название темы!'], true, 5, 50)
                -> addRule('string', $msg, ['msg' => 'Слишком длинный или короткий текст сообщения!'], true, 5, Setting::get('forumtextlength'));

            if ($vote) {
                $validation->addRule('string', $question, ['question' => 'Слишком длинный или короткий текст вопроса!'], true, 5, 100);
                $answers = array_unique(array_diff($answers, ['']));

                foreach ($answers as $answer) {
                    if (utf_strlen($answer) > 50) {
                        $validation->addError(['answer' => 'Длина вариантов ответа не должна быть более 50 символов!']);
                        break;
                    }
                }

                $validation->addRule('numeric', count($answers), ['answer' => 'Необходимо от 2 до 10 варианта ответов!'], true, 2, 10);
            }

            /* TODO: Сделать проверку поиска похожей темы */

            if ($validation->run()) {

                $title = antimat($title);
                $msg = antimat($msg);

                DB::run() -> query("UPDATE `users` SET `allforum`=`allforum`+1, `point`=`point`+1, `money`=`money`+5 WHERE `login`=?", [App::getUsername()]);

                DB::run() -> query("INSERT INTO `topics` (`forum_id`, `title`, `user_id`, `posts`, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?);", [$fid, $title, App::getUserId(), 1, SITETIME, SITETIME]);

                $lastid = DB::run() -> lastInsertId();

                DB::run() -> query("INSERT INTO `posts` (`topic_id`, `user_id`, `text`, `created_at`, `ip`, `brow`) VALUES (?, ?, ?, ?, ?, ?);", [$lastid, App::getUserId(), $msg, SITETIME, App::getClientIp(), App::getUserAgent()]);
                $lastPostId = DB::run() -> lastInsertId();

                Topic::where('id', $lastid)->update(['last_post_id' => $lastPostId]);

                DB::run() -> query("UPDATE `forums` SET `topics`=`topics`+1, `posts`=`posts`+1, `last_topic_id`=? WHERE `id`=?", [$lastid, $fid]);
                // Обновление родительского форума
                if ($forum->parent) {
                    DB::run() -> query("UPDATE `forums` SET `last_topic_id`=? WHERE `id`=?", [$lastid, $forum->parent->id]);
                }

                // Создание голосования
                if ($vote) {
                    $vote = new Vote();
                    $vote->title = $question;
                    $vote->topic_id = $lastid;
                    $vote->time = SITETIME;
                    $vote->save();

                    $prepareAnswers = [];
                    foreach ($answers as $answer) {
                        $prepareAnswers[] = [
                            'vote_id' => $vote->id,
                            'answer' => $answer
                        ];
                    }

                    VoteAnswer::insert($prepareAnswers);
                }

                App::setFlash('success', 'Новая тема успешно создана!');
                App::redirect('/topic/'.$lastid);
            } else {
                App::setInput(Request::all());
                App::setFlash('danger', $validation->getErrors());
            }
        }

        App::view('forum/forum_create', compact('forums', 'fid'));
    }

    /**
     * Поиск
     */
    public function search()
    {
        $fid     = check(Request::input('fid'));
        $find    = check(Request::input('find'));
        $type    = abs(intval(Request::input('type')));
        $where   = abs(intval(Request::input('where')));
        $period  = abs(intval(Request::input('period')));
        $section = abs(intval(Request::input('section')));

        if (empty($find)) {

            $forums = Forum::where('parent_id', 0)
                ->with('children')
                ->orderBy('sort')
                ->get();

            if (empty(count($forums))) {
                App::abort('default', 'Разделы форума еще не созданы!');
            }

            App::view('forum/search', compact('forums', 'fid'));

        } else {

            $find = str_replace(['@', '+', '-', '*', '~', '<', '>', '(', ')', '"', "'"], '', $find);

            if (!is_utf($find)) {
                $find = win_to_utf($find);
            }

            if (utf_strlen($find) >= 3 && utf_strlen($find) <= 50) {

                $findmewords = explode(' ', utf_lower($find));

                $arrfind = [];
                foreach ($findmewords as $val) {
                    if (utf_strlen($val) >= 3) {
                        $arrfind[] = (empty($type)) ? '+' . $val . '*' : $val . '*';
                    }
                }

                $findme = implode(" ", $arrfind);

                if ($type == 2 && count($findmewords) > 1) {
                    $findme = "\"$find\"";
                }

                //Setting::get('newtitle') = $find . ' - Результаты поиска';

                $wheres = (empty($where)) ? 'topics' : 'posts';

                $forumfind = ($type . $wheres . $period . $section . $find);

                // ----------------------------- Поиск в темах -------------------------------//
                if ($wheres == 'topics') {

                    if (empty($_SESSION['forumfindres']) || $forumfind != $_SESSION['forumfind']) {

                        $searchsec = ($section > 0) ? "forum_id = " . $section . " AND" : '';
                        $searchper = ($period > 0) ? "updated_at > " . (SITETIME - ($period * 24 * 60 * 60)) . " AND" : '';

                        $result = Topic::select('id')
                            ->whereRaw($searchsec . ' ' . $searchper . ' MATCH (`title`) AGAINST (? IN BOOLEAN MODE)', [$findme])
                            ->limit(100)
                            ->pluck('id')
                            ->all();

                        $_SESSION['forumfind'] = $forumfind;
                        $_SESSION['forumfindres'] = $result;
                    }

                    $total = count($_SESSION['forumfindres']);

                    if ($total > 0) {
                        $page = App::paginate(Setting::get('forumtem'), $total);

                        $topics = Topic::whereIn('id', $_SESSION['forumfindres'])
                            ->with('lastPost.user')
                            ->orderBy('updated_at', 'desc')
                            ->offset($page['offset'])
                            ->limit(Setting::get('forumtem'))
                            ->get();

                        App::view('forum/search_topics', compact('topics', 'page', 'find', 'type', 'where', 'section', 'period'));

                    } else {
                        App::setInput(Request::all());
                        App::setFlash('danger', 'По вашему запросу ничего не найдено!');
                        App::redirect('/forum/search');
                    }
                }

                // --------------------------- Поиск в сообщениях -------------------------------//
                if ($wheres == 'posts') {

                    if (empty($_SESSION['forumfindres']) || $forumfind != $_SESSION['forumfind']) {

                        $searchsec = ($section > 0) ? "forum_id = " . $section . " AND" : '';
                        $searchper = ($period > 0) ? "created_at > " . (SITETIME - ($period * 24 * 60 * 60)) . " AND" : '';

                        $result = Post::select('id')
                            ->whereRaw($searchsec . ' ' . $searchper . ' MATCH (`text`) AGAINST (? IN BOOLEAN MODE)', [$findme])
                            ->limit(100)
                            ->pluck('id')
                            ->all();

                        $_SESSION['forumfind'] = $forumfind;
                        $_SESSION['forumfindres'] = $result;
                    }

                    $total = count($_SESSION['forumfindres']);

                    if ($total > 0) {
                        $page = App::paginate(Setting::get('forumpost'), $total);

                        $posts = Post::whereIn('id', $_SESSION['forumfindres'])
                            ->with('user', 'topic')
                            ->orderBy('created_at', 'desc')
                            ->offset($page['offset'])
                            ->limit(Setting::get('forumpost'))
                            ->get();

                        App::view('forum/search_posts', compact('posts', 'page', 'find', 'type', 'where', 'section', 'period'));

                    } else {
                        App::setInput(Request::all());
                        App::setFlash('danger', 'По вашему запросу ничего не найдено!');
                        App::redirect('/forum/search');
                    }
                }

            } else {
                App::setInput(Request::all());
                App::setFlash('danger', ['find' => 'Запрос должен содержать от 3 до 50 символов!']);
                App::redirect('/forum/search');
            }
        }
    }

    /**
     * RSS всех топиков
     */
    public function rss()
    {
        $topics = Topic::where('closed', 0)
            ->with('lastPost.user')
            ->orderBy('updated_at', 'desc')
            ->limit(15)
            ->get();

        if ($topics->isEmpty()) {
            App::abort('default', 'Нет тем для отображения!');
        }

        App::view('forum/rss', compact('topics'));
    }

    /**
     * RSS постов
     */
    public function rssPosts($tid)
    {
        $topic = Topic::find($tid);

        if (empty($topic)) {
            App::abort('default', 'Данной темы не существует!');
        }

        $posts = Post::where('topic_id', $tid)
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->limit(15)
            ->get();

        App::view('forum/rss_posts', compact('topic', 'posts'));
    }

    /**
     * Последние темы
     */
    public function topThemes()
    {
        $total = Topic::count();

        if ($total > 0) {

            if ($total > 500) {
                $total = 500;
            }

            $page = App::paginate(Setting::get('forumtem'), $total);

            $topics = Topic::where('closed', 0)
                ->orderBy('posts', 'desc')
                ->limit(Setting::get('forumtem'))
                ->offset($page['offset'])
                ->with('forum', 'user', 'lastPost.user')
                ->get();

            App::view('forum/top', compact('topics', 'page'));

        } else {
            show_error('Созданных тем еще нет!');
        }
    }

    /**
     * Последние сообщения
     */
    public function topPosts()
    {
        $total = Post::count();

        if ($total > 0) {

            if ($total > 500) {
                $total = 500;
            }

            $page = App::paginate(Setting::get('forumpost'), $total);

            $posts = Post::orderBy('rating', 'desc')
                ->limit(Setting::get('forumpost'))
                ->offset($page['offset'])
                ->with('topic', 'user')
                ->get();

            App::view('forum/top_posts', compact('posts', 'page'));

        } else {
            show_error('Созданных тем еще нет!');
        }
    }
}
