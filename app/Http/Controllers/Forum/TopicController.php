<?php

declare(strict_types=1);

namespace App\Http\Controllers\Forum;

use App\Classes\Validator;
use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\File;
use App\Models\Flood;
use App\Models\Poll;
use App\Models\Post;
use App\Models\Reader;
use App\Models\Topic;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TopicController extends Controller
{
    /**
     * Main page
     */
    public function index(int $id, Request $request): View|RedirectResponse
    {
        $user = getUser();

        $topic = Topic::query()
            ->where('topics.id', $id)
            ->when($user, static function (Builder $query) use ($user) {
                $query->select('topics.*', 'bookmarks.count_posts as bookmark_posts')
                    ->leftJoin('bookmarks', static function (JoinClause $join) use ($user) {
                        $join->on('topics.id', 'bookmarks.topic_id')
                            ->where('bookmarks.user_id', $user->id);
                    });
            })
            ->with('forum.parent')
            ->first();

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        // Переход к сообщению
        $pid = int($request->input('pid'));
        if ($pid) {
            $countPosts = $topic->posts->where('id', '<=', $pid)->count();

            $page = ceil($countPosts / setting('forumpost'));
            $page = $page > 1 ? $page : null;

            return redirect()->route('topics.topic', ['id' => $topic->id, 'page' => $page])
                ->withFragment('post_' . $pid);
        }

        $posts = Post::query()
            ->where('topic_id', $topic->id)
            ->when($user, static function (Builder $query) use ($user) {
                $query->select('posts.*', 'polls.vote')
                    ->leftJoin('polls', static function (JoinClause $join) use ($user) {
                        $join->on('posts.id', 'polls.relate_id')
                            ->where('polls.relate_type', Post::$morphName)
                            ->where('polls.user_id', $user->id);
                    });
            })
            ->with('files', 'user', 'editUser')
            ->orderBy('created_at')
            ->paginate(setting('forumpost'));

        if ($posts->onFirstPage()) {
            $firstPost = $posts->first();
        } else {
            $firstPost = Post::query()->where('topic_id', $topic->id)->orderBy('created_at')->first();
        }

        if ($topic->bookmark_posts && $topic->count_posts > $topic->bookmark_posts) {
            Bookmark::query()
                ->where('topic_id', $topic->id)
                ->where('user_id', $user->id)
                ->update(['count_posts' => $topic->count_posts]);
        }

        // Curators
        if ($topic->moderators) {
            $topic->curators = User::query()->whereIn('login', explode(',', (string) $topic->moderators))->get();
            $topic->isModer = $user ? $topic->curators->where('id', $user->id)->isNotEmpty() : false;
        }

        // Visits
        Reader::countingStat($topic);

        // Votes
        $vote = Vote::query()->where('topic_id', $topic->id)->first();

        if ($vote) {
            $vote->poll = $vote->poll()->first();

            if ($vote->answers->isNotEmpty()) {
                $results = Arr::pluck($vote->answers, 'result', 'answer');
                $max = max($results);

                arsort($results);

                $vote->voted = $results;

                $vote->sum = ($vote->count > 0) ? $vote->count : 1;
                $vote->max = ($max > 0) ? $max : 1;
            }
        }

        $files = [];
        $description = $firstPost ? truncateDescription(bbCode($firstPost->text, false)) : $topic->title;

        if ($user) {
            $files = File::query()
                ->where('relate_type', Post::$morphName)
                ->where('relate_id', 0)
                ->where('user_id', $user->id)
                ->orderBy('created_at')
                ->get();
        }

        return view('forums/topic', compact('topic', 'posts', 'vote', 'description', 'files'));
    }

    /**
     * Message creation
     */
    public function create(int $id, Request $request, Validator $validator, Flood $flood): RedirectResponse
    {
        $msg = $request->input('msg');

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $topic = Topic::query()
            ->select('topics.*', 'forums.parent_id')
            ->where('topics.id', $id)
            ->leftJoin('forums', 'topics.forum_id', 'forums.id')
            ->first();

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        $validator
            ->empty($topic->closed, ['msg' => __('forums.topic_closed')])
            ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
            ->length($msg, setting('forum_text_min'), setting('forum_text_max'), ['msg' => __('validator.text')]);

        // Проверка сообщения на схожесть
        $post = Post::query()->where('topic_id', $topic->id)->orderByDesc('id')->first();
        $validator->notEqual($msg, $post->text ?? false, ['msg' => __('forums.post_repeat')]);

        if ($validator->isValid()) {
            $msg = antimat($msg);

            $countFiles = File::query()
                ->where('relate_type', Post::$morphName)
                ->where('relate_id', 0)
                ->where('user_id', $user->id)
                ->orderBy('created_at')
                ->count();

            if (
                $post
                && $post->created_at + 600 > SITETIME
                && $user->id === $post->user_id
                && setting('forum_merge_posts')
                && $countFiles + $post->files->count() <= setting('maxfiles')
                && (Str::length($msg) + Str::length($post->text) <= setting('forum_text_max'))
            ) {
                $post->update(['text' => $post->text . PHP_EOL . $msg]);
            } else {
                $post = Post::query()->create([
                    'topic_id'   => $topic->id,
                    'user_id'    => $user->id,
                    'text'       => $msg,
                    'created_at' => SITETIME,
                    'ip'         => getIp(),
                    'brow'       => getBrowser(),
                ]);

                $user->increment('allforum');
                $user->increment('point', setting('forum_point'));
                $user->increment('money', setting('forum_money'));

                $topic->increment('count_posts');
                $topic->update([
                    'last_post_id' => $post->id,
                    'updated_at'   => SITETIME,
                ]);

                $topic->forum->update([
                    'count_posts'   => DB::raw('count_posts + 1'),
                    'last_topic_id' => $topic->id,
                ]);

                // Обновление родительского форума
                if ($topic->forum->parent_id) {
                    $topic->forum->parent->update([
                        'last_topic_id' => $topic->id,
                    ]);
                }
            }

            File::query()
                ->where('relate_type', Post::$morphName)
                ->where('relate_id', 0)
                ->where('user_id', $user->id)
                ->update(['relate_id' => $post->id]);

            clearCache(['statForums', 'recentTopics', 'TopicFeed']);
            $flood->saveState();
            sendNotify($msg, route('topics.topic', ['id' => $topic->id, 'pid' => $post->id], false), $topic->title);

            setFlash('success', __('main.message_added_success'));
        } else {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $page = ceil($topic->count_posts / setting('forumpost'));
        $page = $page > 1 ? $page : null;

        return redirect()->route('topics.topic', ['id' => $topic->id, 'page' => $page]);
    }

    /**
     * Delete messages
     */
    public function delete(int $id, Request $request, Validator $validator): RedirectResponse
    {
        $del = intar($request->input('del'));
        $page = int($request->input('page'));

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        $isModer = in_array($user->login, explode(',', (string) $topic->moderators), true);

        $validator
            ->notEmpty($del, __('validator.deletion'))
            ->empty($topic->closed, __('forums.topic_closed'))
            ->equal($isModer, true, __('forums.posts_deleted_curators'));

        if ($validator->isValid()) {
            $posts = Post::query()->whereIn('id', $del)->get();

            $posts->each(static function (Post $post) {
                $post->delete();
            });

            $topic->decrement('count_posts', $posts->count());
            $topic->forum->decrement('count_posts', $posts->count());

            setFlash('success', __('main.messages_deleted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect()->route('topics.topic', ['id' => $topic->id, 'page' => $page]);
    }

    /**
     * Close topic
     */
    public function close(int $id, Request $request, Validator $validator): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $topic = Topic::query()->find($id);

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->gte($user->point, setting('editforumpoint'), __('forums.topic_edited_points', ['point' => plural(setting('editforumpoint'), setting('scorename'))]))
            ->notEmpty($topic, __('forums.topic_not_exist'))
            ->equal($topic->user_id, $user->id, __('forums.topic_not_author'))
            ->empty($topic->closed, __('forums.topic_closed'));

        if ($validator->isValid()) {
            $topic->update([
                'closed'        => 1,
                'close_user_id' => getUser('id'),
            ]);

            if ($topic->vote) {
                $topic->vote->update(['closed' => 1]);
                $topic->vote->polls()->delete();
            }

            setFlash('success', __('forums.topic_success_closed'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect()->route('topics.topic', ['id' => $topic->id]);
    }

    /**
     * Open topic
     */
    public function open(int $id, Request $request, Validator $validator): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $topic = Topic::query()->find($id);

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->notEmpty($topic, __('forums.topic_not_exist'))
            ->equal($topic->user_id, $user->id, __('forums.topic_not_author'))
            ->equal($topic->close_user_id, $user->id, __('forums.topic_opened_author'))
            ->notEmpty($topic->closed, __('forums.topic_already_open'));

        if ($validator->isValid()) {
            $topic->update([
                'closed'        => 0,
                'close_user_id' => null,
            ]);

            if ($topic->vote) {
                $topic->vote->update(['closed' => 0]);
            }

            setFlash('success', __('forums.topic_success_opened'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect()->route('topics.topic', ['id' => $topic->id]);
    }

    /**
     * Topic editing
     */
    public function edit(int $id, Request $request, Validator $validator): View|RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        if ($user->point < setting('editforumpoint')) {
            abort(200, __('forums.topic_edited_points', ['point' => plural(setting('editforumpoint'), setting('scorename'))]));
        }

        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        if ($topic->user_id !== $user->id) {
            abort(200, __('forums.topic_not_author'));
        }

        if ($topic->closed) {
            abort(200, __('forums.topic_closed'));
        }

        $post = Post::query()->where('topic_id', $topic->id)
            ->orderBy('id')
            ->first();

        $vote = Vote::query()->where('topic_id', $id)->first();

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $msg = $request->input('msg');
            $question = $request->input('question');
            $answers = (array) $request->input('answers');

            $validator
                ->length($title, setting('forum_title_min'), setting('forum_title_max'), ['title' => __('validator.text')]);

            if ($post) {
                $validator->length($msg, setting('forum_text_min'), setting('forum_text_max'), ['msg' => __('validator.text')]);
            }

            if ($vote) {
                $validator->length($question, 5, 100, ['question' => __('validator.text')]);

                if ($answers) {
                    $validator->empty($vote->count, ['question' => __('votes.answer_changed_impossible')]);

                    $answers = array_unique(array_diff($answers, ['']));

                    foreach ($answers as $answer) {
                        $validator->length($answer, setting('vote_answer_min'), setting('vote_answer_max'), ['answers' => __('votes.answer_wrong_length')]);
                    }

                    $validator->between(count($answers), 2, 10, ['answers' => __('votes.answer_not_enough')]);
                }
            }

            if ($validator->isValid()) {
                $title = antimat($title);
                $msg = antimat($msg);

                $topic->update(['title' => $title]);

                if ($post) {
                    $post->update([
                        'text'         => $msg,
                        'edit_user_id' => $user->id,
                        'updated_at'   => SITETIME,
                    ]);
                }

                if ($vote) {
                    $vote->update([
                        'title' => $question,
                    ]);

                    if ($answers) {
                        $existingAnswerIds = $vote->answers()->pluck('id')->toArray();
                        $answerIdsToDelete = array_diff($existingAnswerIds, array_keys($answers));

                        if ($answerIdsToDelete !== []) {
                            $vote->answers()->whereIn('id', $answerIdsToDelete)->delete();
                        }

                        $countAnswers = $vote->answers()->count();

                        foreach ($answers as $answerId => $answer) {
                            $ans = $vote->answers()->find($answerId);

                            if ($ans && $ans->exists) {
                                $ans->update(['answer' => $answer]);
                            } elseif ($countAnswers < 10) {
                                $vote->answers()->create(['answer' => $answer]);
                                $countAnswers++;
                            }
                        }
                    }
                }

                setFlash('success', __('forums.topic_success_changed'));

                return redirect()->route('topics.topic', ['id' => $topic->id]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        if ($vote) {
            $vote->getAnswers = $vote->answers->pluck('answer', 'id')->all();
        }

        return view('forums/topic_edit', compact('post', 'topic', 'vote'));
    }

    /**
     * Post editing
     */
    public function editPost(int $id, Request $request, Validator $validator): View|RedirectResponse
    {
        $page = int($request->input('page'));

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $post = Post::query()
            ->select('posts.*', 'moderators', 'closed')
            ->leftJoin('topics', 'posts.topic_id', 'topics.id')
            ->where('posts.id', $id)
            ->first();

        if (! $post) {
            abort(404, __('forums.post_not_exist'));
        }

        if ($post->closed) {
            abort(200, __('forums.topic_closed'));
        }

        $isModer = in_array($user->login, explode(',', (string) $post->moderators), true);

        if (! $isModer && $post->user_id !== $user->id) {
            abort(200, __('forums.posts_edited_curators'));
        }

        if (! $isModer && $post->created_at + 600 < SITETIME) {
            abort(200, __('main.editing_impossible'));
        }

        if ($request->isMethod('post')) {
            $msg = $request->input('msg');

            $validator
                ->length($msg, setting('forum_text_min'), setting('forum_text_max'), ['msg' => __('validator.text')]);

            if ($validator->isValid()) {
                $post->update([
                    'text'         => antimat($msg),
                    'edit_user_id' => $user->id,
                    'updated_at'   => SITETIME,
                ]);

                setFlash('success', __('main.message_edited_success'));

                return redirect()->route('topics.topic', ['id' => $post->topic_id, 'page' => $page]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('forums/topic_edit_post', compact('post', 'page'));
    }

    /**
     * Voting
     */
    public function vote(int $id, Request $request, Validator $validator): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $vote = Vote::query()->where('topic_id', $id)->first();

        if (! $vote) {
            abort(404, __('votes.voting_not_found'));
        }

        $poll = int($request->input('poll'));
        $page = int($request->input('page'));

        $validator
            ->notEmpty($poll, __('votes.answer_not_chosen'))
            ->empty($vote->closed, __('votes.voting_closed'));

        if ($validator->isValid()) {
            $votePoll = $vote->poll()->first();
            $validator->empty($votePoll, __('votes.voting_passed'));
        }

        if ($validator->isValid()) {
            $answer = $vote->answers()
                ->where('id', $poll)
                ->where('vote_id', $vote->id)
                ->first();

            $validator->notEmpty($answer, __('votes.answer_not_found'));
        }

        if ($validator->isValid()) {
            $vote->increment('count');
            $answer->increment('result');

            Poll::query()->create([
                'relate_type' => Vote::$morphName,
                'relate_id'   => $vote->id,
                'user_id'     => $user->id,
                'vote'        => $answer->answer,
                'created_at'  => SITETIME,
            ]);

            setFlash('success', __('votes.voting_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect()->route('topics.topic', ['id' => $vote->topic_id, 'page' => $page]);
    }

    /**
     * Print topic
     */
    public function print(int $id): View
    {
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        $posts = Post::query()
            ->where('topic_id', $topic->id)
            ->with('user')
            ->orderBy('created_at')
            ->get();

        $description = $posts->first() ? truncateDescription(bbCode($posts->first()->text, false)) : $topic->title;

        return view('forums/print', compact('topic', 'posts', 'description'));
    }
}
