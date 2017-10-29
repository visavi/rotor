<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Flood;
use App\Models\Forum;
use App\Models\Post;
use App\Models\Topic;
use App\Models\Vote;
use App\Models\VoteAnswer;
use Illuminate\Database\Capsule\Manager as DB;

class ForumController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $forums = Forum::query()
            ->where('parent_id', 0)
            ->with('lastTopic.lastPost.user')
            ->with('children')
            ->orderBy('sort')
            ->get();

        if ($forums->isEmpty()) {
            abort('default', 'Разделы форума еще не созданы!');
        }

        return view('forum/index', compact('forums'));
    }

    /**
     * Страница списка тем
     */
    public function forum($fid)
    {
        $forum = Forum::with('parent')->find($fid);

        if (! $forum) {
            abort('default', 'Данного раздела не существует!');
        }

        $forum->children = Forum::query()
            ->where('parent_id', $forum->id)
            ->with('lastTopic.lastPost.user')
            ->get();

        $total = Topic::query()->where('forum_id', $fid)->count();

        $page = paginate(setting('forumtem'), $total);

        $topics = Topic::query()
            ->where('forum_id', $fid)
            ->orderBy('locked', 'desc')
            ->orderBy('updated_at', 'desc')
            ->limit(setting('forumtem'))
            ->offset($page['offset'])
            ->with('lastPost.user')
            ->get();

        return view('forum/forum', compact('forum', 'topics', 'page'));
    }

    /**
     * Создание новой темы
     */
    public function create()
    {
        $fid = abs(intval(Request::input('fid')));

        $forums = Forum::query()
            ->where('parent_id', 0)
            ->with('children')
            ->orderBy('sort')
            ->get();

        if ($forums->isEmpty()) {
            abort('default', 'Разделы форума еще не созданы!');
        }

        if (! $user = getUser()) {
            abort(403);
        }

        if (Request::isMethod('post')) {

            $title    = check(Request::input('title'));
            $msg      = check(Request::input('msg'));
            $token    = check(Request::input('token'));
            $vote     = Request::has('vote') ? 1 : 0;
            $question = check(Request::input('question'));
            $answers  = check(Request::input('answer'));

            $forum = Forum::query()->find($fid);

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->notEmpty($forum, ['fid' => 'Форума для новой темы не существует!'])
                ->equal(Flood::isFlood(), true, ['msg' => 'Антифлуд! Разрешается cоздавать темы раз в '.Flood::getPeriod().' сек!'])
                ->length($title, 5, 50, ['title' => 'Слишком длинное или короткое название темы!'])
                ->length($msg, 5, setting('forumtextlength'), ['msg' => 'Слишком длинный или короткий текст сообщения!']);

            if ($forum) {
                $validator->empty($forum->closed, ['fid' => 'В данном форуме запрещено создавать темы!']);
            }

            if ($vote) {
                $validator->length($question, 5, 100, ['question' => 'Слишком длинный или короткий текст вопроса!']);
                $answers = array_unique(array_diff($answers, ['']));

                foreach ($answers as $answer) {
                    if (utfStrlen($answer) > 50) {
                        $validator->addError(['answer' => 'Длина вариантов ответа не должна быть более 50 символов!']);
                        break;
                    }
                }

                $validator->between(count($answers), 2, 10, ['answer' => 'Недостаточное количество вариантов ответов!']);
            }

            /* TODO: Сделать проверку поиска похожей темы */

            if ($validator->isValid()) {

                $title = antimat($title);
                $msg   = antimat($msg);

                $user->update([
                    'allforum' => DB::raw('allforum + 1'),
                    'point'    => DB::raw('point + 1'),
                    'money'    => DB::raw('money + 5'),
                ]);

                $topic = Topic::query()->create([
                    'forum_id'   => $forum->id,
                    'title'      => $title,
                    'user_id'    => getUser('id'),
                    'posts'      => 1,
                    'created_at' => SITETIME,
                    'updated_at' => SITETIME,
                ]);

                $post = Post::query()->create([
                    'topic_id'   => $topic->id,
                    'user_id'    => getUser('id'),
                    'text'       => $msg,
                    'created_at' => SITETIME,
                    'ip'         => getIp(),
                    'brow'       => getBrowser(),
                ]);

                Topic::query()->where('id', $topic->id)->update(['last_post_id' => $post->id]);

                $forum->update([
                    'topics'        => DB::raw('topics + 1'),
                    'posts'         => DB::raw('posts + 1'),
                    'last_topic_id' => $topic->id,
                ]);

                // Обновление родительского форума
                if ($forum->parent) {
                    $forum->parent->update([
                        'last_topic_id' => $topic->id
                    ]);
                }

                // Создание голосования
                if ($vote) {
                    $vote = Vote::query()->create([
                        'title'      => $question,
                        'topic_id'   => $topic->id,
                        'created_at' => SITETIME,
                    ]);

                    $prepareAnswers = [];
                    foreach ($answers as $answer) {
                        $prepareAnswers[] = [
                            'vote_id' => $vote->id,
                            'answer'  => $answer
                        ];
                    }

                    VoteAnswer::query()->insert($prepareAnswers);
                }

                setFlash('success', 'Новая тема успешно создана!');
                redirect('/topic/'.$topic->id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('forum/forum_create', compact('forums', 'fid'));
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

            $forums = Forum::query()
                ->where('parent_id', 0)
                ->with('children')
                ->orderBy('sort')
                ->get();

            if ($forums->isEmpty()) {
                abort('default', 'Разделы форума еще не созданы!');
            }

            return view('forum/search', compact('forums', 'fid'));

        } else {

            $find = str_replace(['@', '+', '-', '*', '~', '<', '>', '(', ')', '"', "'"], '', $find);

            if (!isUtf($find)) {
                $find = winToUtf($find);
            }

            if (utfStrlen($find) >= 3 && utfStrlen($find) <= 50) {

                $findmewords = explode(' ', utfLower($find));

                $arrfind = [];
                foreach ($findmewords as $val) {
                    if (utfStrlen($val) >= 3) {
                        $arrfind[] = (empty($type)) ? '+' . $val . '*' : $val . '*';
                    }
                }

                $findme = implode(" ", $arrfind);

                if ($type == 2 && count($findmewords) > 1) {
                    $findme = "\"$find\"";
                }

                //setting('newtitle') = $find . ' - Результаты поиска';

                $wheres = (empty($where)) ? 'topics' : 'posts';

                $forumfind = ($type . $wheres . $period . $section . $find);

                // ----------------------------- Поиск в темах -------------------------------//
                if ($wheres == 'topics') {

                    if (empty($_SESSION['forumfindres']) || $forumfind != $_SESSION['forumfind']) {

                        $searchsec = ($section > 0) ? "forum_id = " . $section . " AND" : '';
                        $searchper = ($period > 0) ? "updated_at > " . (SITETIME - ($period * 24 * 60 * 60)) . " AND" : '';

                        $result = Topic::query()
                            ->select('id')
                            ->whereRaw($searchsec . ' ' . $searchper . ' MATCH (`title`) AGAINST (? IN BOOLEAN MODE)', [$findme])
                            ->limit(100)
                            ->pluck('id')
                            ->all();

                        $_SESSION['forumfind'] = $forumfind;
                        $_SESSION['forumfindres'] = $result;
                    }

                    $total = count($_SESSION['forumfindres']);

                    if ($total > 0) {
                        $page = paginate(setting('forumtem'), $total);

                        $topics = Topic::query()
                            ->whereIn('id', $_SESSION['forumfindres'])
                            ->with('lastPost.user')
                            ->orderBy('updated_at', 'desc')
                            ->offset($page['offset'])
                            ->limit(setting('forumtem'))
                            ->get();

                        return view('forum/search_topics', compact('topics', 'page', 'find', 'type', 'where', 'section', 'period'));

                    } else {
                        setInput(Request::all());
                        setFlash('danger', 'По вашему запросу ничего не найдено!');
                        redirect('/forum/search');
                    }
                }

                // --------------------------- Поиск в сообщениях -------------------------------//
                if ($wheres == 'posts') {

                    if (empty($_SESSION['forumfindres']) || $forumfind != $_SESSION['forumfind']) {

                        $searchsec = ($section > 0) ? "topics.forum_id = " . $section . " AND" : '';
                        $searchper = ($period > 0) ? "created_at > " . (SITETIME - ($period * 24 * 60 * 60)) . " AND" : '';

                        $result = Post::query()
                            ->select('posts.id')
                            ->leftJoin('topics', 'posts.topic_id', 'topics.id')
                            ->whereRaw($searchsec . ' ' . $searchper . ' MATCH (`text`) AGAINST (? IN BOOLEAN MODE)', [$findme])
                            ->limit(100)
                            ->pluck('id')
                            ->all();

                        $_SESSION['forumfind'] = $forumfind;
                        $_SESSION['forumfindres'] = $result;
                    }

                    $total = count($_SESSION['forumfindres']);

                    if ($total > 0) {
                        $page = paginate(setting('forumpost'), $total);

                        $posts = Post::query()
                            ->whereIn('id', $_SESSION['forumfindres'])
                            ->with('user', 'topic')
                            ->orderBy('created_at', 'desc')
                            ->offset($page['offset'])
                            ->limit(setting('forumpost'))
                            ->get();

                        return view('forum/search_posts', compact('posts', 'page', 'find', 'type', 'where', 'section', 'period'));

                    } else {
                        setInput(Request::all());
                        setFlash('danger', 'По вашему запросу ничего не найдено!');
                        redirect('/forum/search');
                    }
                }

            } else {
                setInput(Request::all());
                setFlash('danger', ['find' => 'Запрос должен содержать от 3 до 50 символов!']);
                redirect('/forum/search');
            }
        }
    }

    /**
     * RSS всех топиков
     */
    public function rss()
    {
        $topics = Topic::query()
            ->where('closed', 0)
            ->with('lastPost.user')
            ->orderBy('updated_at', 'desc')
            ->limit(15)
            ->get();

        if ($topics->isEmpty()) {
            abort('default', 'Нет тем для отображения!');
        }

        return view('forum/rss', compact('topics'));
    }

    /**
     * RSS постов
     */
    public function rssPosts($tid)
    {
        $topic = Topic::query()->find($tid);

        if (empty($topic)) {
            abort('default', 'Данной темы не существует!');
        }

        $posts = Post::query()
            ->where('topic_id', $tid)
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->limit(15)
            ->get();

        return view('forum/rss_posts', compact('topic', 'posts'));
    }

    /**
     * Последние темы
     */
    public function topThemes()
    {
        $total = Topic::query()->count();

        if ($total > 500) {
            $total = 500;
        }

        $page = paginate(setting('forumtem'), $total);

        $topics = Topic::query()
            ->where('closed', 0)
            ->orderBy('posts', 'desc')
            ->limit(setting('forumtem'))
            ->offset($page['offset'])
            ->with('forum', 'user', 'lastPost.user')
            ->get();

        return view('forum/top', compact('topics', 'page'));
    }

    /**
     * Последние сообщения
     */
    public function topPosts()
    {
        $total = Post::query()->count();

        if ($total > 500) {
            $total = 500;
        }

        $page = paginate(setting('forumpost'), $total);

        $posts = Post::query()
            ->orderBy('rating', 'desc')
            ->limit(setting('forumpost'))
            ->offset($page['offset'])
            ->with('topic', 'user')
            ->get();

        return view('forum/top_posts', compact('posts', 'page'));
    }
}
