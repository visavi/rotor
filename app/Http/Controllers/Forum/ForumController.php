<?php

declare(strict_types=1);

namespace App\Http\Controllers\Forum;

use App\Classes\Validator;
use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Flood;
use App\Models\Forum;
use App\Models\Post;
use App\Models\Topic;
use App\Models\Vote;
use App\Models\VoteAnswer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ForumController extends Controller
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $forums = Forum::query()
            ->where('parent_id', 0)
            ->with('children', 'lastTopic.lastPost.user')
            ->orderBy('sort')
            ->get();

        if ($forums->isEmpty()) {
            abort(200, __('forums.empty_forums'));
        }

        return view('forums/index', compact('forums'));
    }

    /**
     * Страница списка тем
     */
    public function forum(int $id): View
    {
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
     */
    public function create(Request $request, Validator $validator, Flood $flood): View|RedirectResponse
    {
        $fid = int($request->input('fid'));

        $forums = (new Forum())->getChildren();

        if ($forums->isEmpty()) {
            abort(200, __('forums.empty_forums'));
        }

        if (! $user = getUser()) {
            abort(403);
        }

        $files = File::query()
            ->where('relate_type', Post::$morphName)
            ->where('relate_id', 0)
            ->where('user_id', $user->id);

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $msg = $request->input('msg');
            $vote = empty($request->input('vote')) ? 0 : 1;
            $question = $request->input('question');
            $answers = (array) $request->input('answers');

            $forum = Forum::query()->find($fid);

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->notEmpty($forum, ['fid' => 'Форума для новой темы не существует!'])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
                ->length($title, setting('forum_title_min'), setting('forum_title_max'), ['title' => __('validator.text')])
                ->length($msg, setting('forum_text_min'), setting('forum_text_max'), ['msg' => __('validator.text')]);

            if ($forum) {
                $validator->empty($forum->closed, ['fid' => __('forums.forum_closed')]);
            }

            if ($vote) {
                $validator->length($question, setting('vote_title_min'), setting('vote_title_max'), ['question' => __('validator.text')]);
                $answers = array_unique(array_diff($answers, ['']));

                foreach ($answers as $answer) {
                    $validator->length($answer, setting('vote_answer_min'), setting('vote_answer_max'), ['answers' => __('votes.answer_wrong_length')]);
                }

                $validator->between(count($answers), 2, 10, ['answers' => __('votes.answer_not_enough')]);
            }

            /* TODO: Сделать проверку поиска похожей темы */

            if ($validator->isValid()) {
                $title = antimat($title);
                $msg = antimat($msg);

                $user->increment('allforum');
                $user->increment('point', setting('forum_point'));
                $user->increment('money', setting('forum_money'));

                $topic = Topic::query()->create([
                    'forum_id'    => $forum->id,
                    'title'       => $title,
                    'user_id'     => getUser('id'),
                    'count_posts' => 1,
                    'created_at'  => SITETIME,
                    'updated_at'  => SITETIME,
                ]);

                $post = Post::query()->create([
                    'topic_id'   => $topic->id,
                    'user_id'    => getUser('id'),
                    'text'       => $msg,
                    'created_at' => SITETIME,
                    'ip'         => getIp(),
                    'brow'       => getBrowser(),
                ]);

                $files->update(['relate_id' => $post->id]);

                Topic::query()->where('id', $topic->id)->update(['last_post_id' => $post->id]);

                $forum->update([
                    'count_topics'  => DB::raw('count_topics + 1'),
                    'count_posts'   => DB::raw('count_posts + 1'),
                    'last_topic_id' => $topic->id,
                ]);

                // Обновление родительского форума
                if ($forum->parent->id) {
                    $forum->parent->update([
                        'last_topic_id' => $topic->id,
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
                            'answer'  => $answer,
                        ];
                    }

                    VoteAnswer::query()->insert($prepareAnswers);
                }

                clearCache(['statForums', 'recentTopics', 'TopicFeed']);
                $flood->saveState();

                setFlash('success', __('forums.topic_success_created'));

                return redirect()->route('topics.topic', ['id' => $topic->id]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $files = $files->get();

        return view('forums/topic_create', compact('forums', 'fid', 'files'));
    }

    /**
     * RSS всех топиков
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
            abort(200, __('forums.topics_not_created'));
        }

        return view('forums/rss', compact('topics'));
    }

    /**
     * RSS постов
     */
    public function rssPosts(int $id): View
    {
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
}
