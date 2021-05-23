<?php

declare(strict_types=1);

namespace App\Http\Controllers\Forum;

use App\Classes\Validator;
use App\Http\Controllers\Controller;
use App\Models\Flood;
use App\Models\Forum;
use App\Models\Post;
use App\Models\Topic;
use App\Models\Vote;
use App\Models\VoteAnswer;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForumController extends Controller
{
    /**
     * Главная страница
     *
     * @return View
     */
    public function index(): View
    {
        $forums = Forum::query()
            ->where('parent_id', 0)
            ->with('lastTopic.lastPost.user')
            ->with('children')
            ->orderBy('sort')
            ->get();

        if ($forums->isEmpty()) {
            abort('default', __('forums.empty_forums'));
        }

        return view('forums/index', compact('forums'));
    }

    /**
     * Страница списка тем
     *
     * @param int $id
     *
     * @return View
     */
    public function forum(int $id): View
    {
        /** @var Forum $forum */
        $forum = Forum::query()->with('parent', 'children.lastTopic.lastPost.user')->find($id);

        if (! $forum) {
            abort(404, __('forums.forum_not_exist'));
        }

        $user = getUser();

        $topics = Topic::query()
            ->where('forum_id', $forum->id)
            ->when($user, static function (Builder $query) use ($user) {
                $query->select('topics.*', 'bookmarks.count_posts as bookmark_posts')
                ->leftJoin('bookmarks', static function (JoinClause $join) use ($user) {
                    $join->on('topics.id', 'bookmarks.topic_id')
                        ->where('bookmarks.user_id', $user->id);
                });
            })
            ->orderByDesc('locked')
            ->orderByDesc('updated_at')
            ->with('lastPost.user')
            ->paginate(setting('forumtem'));

        return view('forums/forum', compact('forum', 'topics'));
    }

    /**
     * Создание новой темы
     *
     * @param Request   $request
     * @param Validator $validator
     * @param Flood     $flood
     *
     * @return View
     */
    public function create(Request $request, Validator $validator, Flood $flood): View
    {
        $fid = int($request->input('fid'));

        $forums = Forum::query()
            ->where('parent_id', 0)
            ->with('children')
            ->orderBy('sort')
            ->get();

        if ($forums->isEmpty()) {
            abort('default', __('forums.empty_forums'));
        }

        if (! $user = getUser()) {
            abort(403);
        }

        if ($request->isMethod('post')) {
            $title    = $request->input('title');
            $msg      = $request->input('msg');
            $vote     = empty($request->input('vote')) ? 0 : 1;
            $question = $request->input('question');
            $answers  = (array) $request->input('answers');

            /** @var Forum $forum */
            $forum = Forum::query()->find($fid);

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->notEmpty($forum, ['fid' => 'Форума для новой темы не существует!'])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
                ->length($title, 3, 50, ['title' => __('validator.text')])
                ->length($msg, 5, setting('forumtextlength'), ['msg' => __('validator.text')]);

            if ($forum) {
                $validator->empty($forum->closed, ['fid' => __('forums.forum_closed')]);
            }

            if ($vote) {
                $validator->length($question, 5, 100, ['question' => __('validator.text')]);
                $answers = array_unique(array_diff($answers, ['']));

                foreach ($answers as $answer) {
                    if (utfStrlen($answer) > 50) {
                        $validator->addError(['answers' => __('votes.answer_wrong_length')]);
                        break;
                    }
                }

                $validator->between(count($answers), 2, 10, ['answers' => __('votes.answer_not_enough')]);
            }

            /* TODO: Сделать проверку поиска похожей темы */

            if ($validator->isValid()) {
                $title = antimat($title);
                $msg   = antimat($msg);

                $user->increment('allforum');
                $user->increment('point');
                $user->increment('money', 5);

                /** @var Topic $topic */
                $topic = Topic::query()->create([
                    'forum_id'    => $forum->id,
                    'title'       => $title,
                    'user_id'     => getUser('id'),
                    'count_posts' => 1,
                    'created_at'  => SITETIME,
                    'updated_at'  => SITETIME,
                ]);

                /** @var Post $post */
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
                    'count_topics'  => DB::raw('count_topics + 1'),
                    'count_posts'   => DB::raw('count_posts + 1'),
                    'last_topic_id' => $topic->id,
                ]);

                // Обновление родительского форума
                if ($forum->parent->id) {
                    $forum->parent->update([
                        'last_topic_id' => $topic->id
                    ]);
                }

                // Создание голосования
                if ($vote) {
                    /** @var Vote $vote */
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

                clearCache(['statForums', 'recentTopics']);
                $flood->saveState();

                setFlash('success', __('forums.topic_success_created'));
                redirect('/topics/'.$topic->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('forums/topic_create', compact('forums', 'fid'));
    }

    /**
     * RSS всех топиков
     *
     * @return View
     */
    public function rss(): View
    {
        $topics = Topic::query()
            ->where('closed', 0)
            ->with('lastPost.user')
            ->orderByDesc('updated_at')
            ->limit(15)
            ->get();

        if ($topics->isEmpty()) {
            abort('default', __('forums.topics_not_created'));
        }

        return view('forums/rss', compact('topics'));
    }

    /**
     * RSS постов
     *
     * @param int $id
     *
     * @return View
     */
    public function rssPosts(int $id): View
    {
        /** @var Topic $topic */
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        $posts = Post::query()
            ->where('topic_id', $topic->id)
            ->orderByDesc('created_at')
            ->with('user')
            ->limit(15)
            ->get();

        return view('forums/rss_posts', compact('topic', 'posts'));
    }

    /**
     * Последние темы
     *
     * @return View
     */
    public function topTopics(): View
    {
        $topics = Topic::query()
            ->orderByDesc('count_posts')
            ->orderByDesc('updated_at')
            ->with('forum', 'user', 'lastPost.user')
            ->limit(100)
            ->get()
            ->all();

        $topics = paginate($topics, setting('forumtem'));

        return view('forums/top', compact('topics'));
    }

    /**
     * Последние сообщения
     *
     * @param Request $request
     *
     * @return View
     */
    public function topPosts(Request $request): View
    {
        $period = int($request->input('period'));

        $posts = Post::query()
            ->when($period, static function (Builder $query) use ($period) {
                return $query->where('created_at', '>', strtotime('-' . $period . ' day', SITETIME));
            })
            ->orderByDesc('rating')
            ->orderByDesc('created_at')
            ->with('topic', 'user')
            ->limit(100)
            ->get()
            ->all();

        $posts = paginate($posts, setting('forumpost'), ['period' => $period]);

        return view('forums/top_posts', compact('posts', 'period'));
    }
}
