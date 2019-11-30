<?php

declare(strict_types=1);

namespace App\Controllers\Forum;

use App\Classes\Validator;
use App\Controllers\BaseController;
use App\Models\Flood;
use App\Models\Forum;
use App\Models\Post;
use App\Models\Topic;
use App\Models\Vote;
use App\Models\VoteAnswer;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ForumController extends BaseController
{
    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
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
     * @return string
     */
    public function forum(int $id): string
    {
        /** @var Forum $forum */
        $forum = Forum::query()->with('parent', 'children.lastTopic.lastPost.user')->find($id);

        if (! $forum) {
            abort(404, __('forums.forum_not_exist'));
        }

        $topics = Topic::query()
            ->where('forum_id', $forum->id)
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
     * @return string
     */
    public function create(Request $request, Validator $validator, Flood $flood): string
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
            $title    = check($request->input('title'));
            $msg      = check($request->input('msg'));
            $token    = check($request->input('token'));
            $vote     = empty($request->input('vote')) ? 0 : 1;
            $question = check($request->input('question'));
            $answers  = check((array) $request->input('answers'));

            /** @var Forum $forum */
            $forum = Forum::query()->find($fid);

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
                ->notEmpty($forum, ['fid' => 'Форума для новой темы не существует!'])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
                ->length($title, 5, 50, ['title' => __('validator.text')])
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
                    'count_topics'  => DB::connection()->raw('count_topics + 1'),
                    'count_posts'   => DB::connection()->raw('count_posts + 1'),
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

        return view('forums/forum_create', compact('forums', 'fid'));
    }

    /**
     * RSS всех топиков
     *
     * @return string
     */
    public function rss(): string
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
     * @return string
     */
    public function rssPosts(int $id): string
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
     * @return string
     */
    public function topTopics(): string
    {
        $topics = Topic::query()
            ->where('closed', 0)
            ->orderByDesc('count_posts')
            ->with('forum', 'user', 'lastPost.user')
            ->paginate(setting('forumtem'));

        return view('forums/top', compact('topics'));
    }

    /**
     * Последние сообщения
     *
     * @param Request $request
     * @return string
     */
    public function topPosts(Request $request): string
    {
        $period = int($request->input('period'));

        $posts = Post::query()
            ->when($period, static function (Builder $query) use ($period) {
                return $query->where('created_at', '>', strtotime('-' . $period . ' day', SITETIME));
            })
            ->orderByDesc('rating')
            ->orderByDesc('created_at')
            ->with('topic', 'user')
            ->paginate(setting('forumpost'))
            ->appends(['period' => $period]);

        return view('forums/top_posts', compact('posts', 'period'));
    }
}
