<?php

declare(strict_types=1);

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Polling;
use App\Models\Post;
use App\Models\Bookmark;
use App\Models\Reader;
use App\Models\Topic;
use App\Models\User;
use App\Models\Vote;
use App\Models\Flood;
use App\Classes\Validator;
use App\Models\VoteAnswer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class TopicController extends Controller
{
    /**
     * Main page
     *
     * @param int $id
     *
     * @return View
     */
    public function index(int $id): View
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

        $posts = Post::query()
            ->where('topic_id', $topic->id)
            ->when($user, static function (Builder $query) use ($user) {
                $query->select('posts.*', 'pollings.vote')
                ->leftJoin('pollings', static function (JoinClause $join) use ($user) {
                    $join->on('posts.id', 'pollings.relate_id')
                        ->where('pollings.relate_type', Post::$morphName)
                        ->where('pollings.user_id', $user->id);
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

        if (isset($topic->bookmark_posts) && $topic->count_posts > $topic->bookmark_posts) {
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
            $vote->poll = $vote->pollings()
                ->where('user_id', $user->id ?? null)
                ->first();

            if ($vote->answers->isNotEmpty()) {
                $results = Arr::pluck($vote->answers, 'result', 'answer');
                $max = max($results);

                arsort($results);

                $vote->voted = $results;

                $vote->sum = ($vote->count > 0) ? $vote->count : 1;
                $vote->max = ($max > 0) ? $max : 1;
            }
        }

        $description = $firstPost ? truncateDescription(bbCode($firstPost->text, false)) : $topic->title;

        return view('forums/topic', compact('topic', 'posts', 'vote', 'description'));
    }

    /**
     * Message creation
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @param Flood     $flood
     *
     * @return RedirectResponse
     */
    public function create(int $id, Request $request, Validator $validator, Flood $flood): RedirectResponse
    {
        $msg   = $request->input('msg');
        $files = (array) $request->file('files');

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

        $validator->equal($request->input('_token'), csrf_token(), ['msg' => __('validator.token')])
            ->empty($topic->closed, ['msg' => __('forums.topic_closed')])
            ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
            ->length($msg, 5, setting('forumtextlength'), ['msg' => __('validator.text')]);

        // Проверка сообщения на схожесть
        /** @var Post $post */
        $post = Post::query()->where('topic_id', $topic->id)->orderByDesc('id')->first();
        $validator->notEqual($msg, $post->text, ['msg' => __('forums.post_repeat')]);

        if ($files && $validator->isValid()) {
            $validator
                ->lte(count($files), setting('maxfiles'), ['files' => __('validator.files_max', ['max' => setting('maxfiles')])]);

            $rules = [
                'maxsize'    => setting('filesize'),
                'extensions' => explode(',', setting('file_extensions')),
            ];

            foreach ($files as $file) {
                $validator->file($file, $rules, ['files' => __('validator.file_upload_failed')]);
            }
        }

        if ($validator->isValid()) {
            $msg = antimat($msg);

            if ($post
                && $post->created_at + 600 > SITETIME
                && $user->id === $post->user_id
                && (utfStrlen($msg) + utfStrlen($post->text) <= setting('forumtextlength'))
                && count($files) + $post->files->count() <= setting('maxfiles')
            ) {
                $newpost = $post->text . PHP_EOL . PHP_EOL . '[i][size=1]' . __('forums.post_added_after', ['sec' => makeTime(SITETIME - $post->created_at)]) . '[/size][/i]' . PHP_EOL . $msg;

                $post->update(['text' => $newpost]);
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
                $user->increment('point');
                $user->increment('money', 5);

                $topic->update([
                    'count_posts'  => DB::raw('count_posts + 1'),
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

            clearCache(['statForums', 'recentTopics', 'statWidget']);
            $flood->saveState();
            sendNotify($msg, '/topics/' . $topic->id . '/' . $post->id, $topic->title);

            if ($files) {
                foreach ($files as $file) {
                    $post->uploadFile($file);
                }
            }

            setFlash('success', __('main.message_added_success'));
        } else {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return redirect('topics/end/' . $topic->id);
    }

    /**
     * Delete messages
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     */
    public function delete(int $id, Request $request, Validator $validator): RedirectResponse
    {
        $del   = intar($request->input('del'));
        $page  = int($request->input('page'));

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        /** @var Topic $topic */
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        $isModer = in_array($user->login, explode(',', (string) $topic->moderators), true);

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->notEmpty($del, __('validator.deletion'))
            ->empty($topic->closed, __('forums.topic_closed'))
            ->equal($isModer, true, __('forums.posts_deleted_curators'));

        if ($validator->isValid()) {
            // ------ Удаление загруженных файлов -------//
            $files = File::query()
                ->where('relate_type', Post::$morphName)
                ->whereIn('relate_id', $del)
                ->get();

            if ($files->isNotEmpty()) {
                foreach ($files as $file) {
                    $file->delete();
                }
            }

            $delPosts = Post::query()->whereIn('id', $del)->delete();

            $topic->decrement('count_posts', $delPosts);
            $topic->forum->decrement('count_posts', $delPosts);

            setFlash('success', __('main.messages_deleted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('topics/' . $topic->id . '?page=' . $page);
    }

    /**
     * Close topic
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     */
    public function close(int $id, Request $request, Validator $validator): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        /** @var Topic $topic */
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
                $topic->vote->pollings()->delete();
            }

            setFlash('success', __('forums.topic_success_closed'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('topics/' . $topic->id);
    }

    /**
     * Open topic
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     */
    public function open(int $id, Request $request, Validator $validator): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        /** @var Topic $topic */
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

        return redirect('topics/' . $topic->id);
    }

    /**
     * Topic editing
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id, Request $request, Validator $validator)
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        if ($user->point < setting('editforumpoint')) {
            abort(200, __('forums.topic_edited_points', ['point' => plural(setting('editforumpoint'), setting('scorename'))]));
        }

        /** @var Topic $topic */
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

        /** @var Vote $vote */
        $vote = Vote::query()->where('topic_id', $id)->first();

        if ($request->isMethod('post')) {
            $title    = $request->input('title');
            $msg      = $request->input('msg');
            $question = $request->input('question');
            $answers  = (array) $request->input('answers');

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($title, 3, 50, ['title' => __('validator.text')]);

            if ($post) {
                $validator->length($msg, 5, setting('forumtextlength'), ['msg' => __('validator.text')]);
            }

            if ($vote) {
                $validator->length($question, 5, 100, ['question' => __('validator.text')]);

                if ($answers) {
                    $validator->empty($vote->count, ['question' => __('votes.answer_changed_impossible')]);

                    $answers = array_unique(array_diff($answers, ['']));

                    foreach ($answers as $answer) {
                        if (utfStrlen($answer) > 50) {
                            $validator->addError(['answers' => __('votes.answer_wrong_length')]);
                            break;
                        }
                    }

                    $validator->between(count($answers), 2, 10, ['answers' => __('votes.answer_not_enough')]);
                }
            }

            if ($validator->isValid()) {
                $title = antimat($title);
                $msg   = antimat($msg);

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
                        $countAnswers = $vote->answers()->count();

                        foreach ($answers as $answerId => $answer) {
                            /** @var VoteAnswer $ans */
                            $ans = $vote->answers()->firstOrNew(['id' => $answerId]);

                            if ($ans->exists) {
                                $ans->update(['answer' => $answer]);
                            } elseif ($countAnswers < 10) {
                                $ans->fill(['answer' => $answer])->save();
                                $countAnswers++;
                            }
                        }
                    }
                }

                setFlash('success', __('forums.topic_success_changed'));

                return redirect('topics/' . $topic->id);
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
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function editPost(int $id, Request $request, Validator $validator)
    {
        $page = int($request->input('page'));

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        /** @var Post $post */
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
            $msg     = $request->input('msg');
            $delfile = intar($request->input('delfile'));

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($msg, 5, setting('forumtextlength'), ['msg' => __('validator.text')]);

            if ($validator->isValid()) {
                $post->update([
                    'text'         => antimat($msg),
                    'edit_user_id' => $user->id,
                    'updated_at'   => SITETIME,
                ]);

                // ------ Удаление загруженных файлов -------//
                if ($delfile) {
                    $files = $post->files()
                        ->whereIn('id', $delfile)
                        ->get();

                    if ($files->isNotEmpty()) {
                        foreach ($files as $file) {
                            $file->delete();
                        }
                    }
                }

                setFlash('success', __('main.message_edited_success'));

                return redirect('topics/' . $post->topic_id . '?page=' . $page);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('forums/topic_edit_post', compact('post', 'page'));
    }

    /**
     * Voting
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     */
    public function vote(int $id, Request $request, Validator $validator): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        /** @var Vote $vote */
        $vote = Vote::query()->where('topic_id', $id)->first();

        if (! $vote) {
            abort(404, __('votes.voting_not_found'));
        }

        $poll = int($request->input('poll'));
        $page = int($request->input('page'));

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->notEmpty($poll, __('votes.answer_not_chosen'))
            ->empty($vote->closed, __('votes.voting_closed'));

        if ($validator->isValid()) {
            $polling = $vote->pollings()
                ->where('user_id', $user->id)
                ->first();
            $validator->empty($polling, __('votes.voting_passed'));
        }

        if ($validator->isValid()) {
            /** @var VoteAnswer $answer */
            $answer = $vote->answers()
                ->where('id', $poll)
                ->where('vote_id', $vote->id)
                ->first();
            $validator->notEmpty($answer, __('votes.answer_not_found'));
        }

        if ($validator->isValid()) {
            $vote->increment('count');
            $answer->increment('result');

            Polling::query()->create([
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

        return redirect('topics/' . $vote->topic_id . '?page=' . $page);
    }

    /**
     * Print topic
     *
     * @param int $id
     *
     * @return View
     */
    public function print(int $id): View
    {
        /** @var Topic $topic */
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

    /**
     * Forward to message
     *
     * @param int $id
     * @param int $pid
     *
     * @return RedirectResponse
     */
    public function viewpost(int $id, int $pid): RedirectResponse
    {
        $countTopics = Post::query()
            ->where('id', '<=', $pid)
            ->where('topic_id', $id)
            ->count();

        if (! $countTopics) {
            abort(404, __('forums.topic_not_exist'));
        }

        $end = ceil($countTopics / setting('forumpost'));

        return redirect('topics/' . $id . '?page=' . $end . '#post_' . $pid);
    }

    /**
     * Forward to the last message
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function end(int $id): RedirectResponse
    {
        /** @var Topic $topic */
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        $end = ceil($topic->count_posts / setting('forumpost'));

        return redirect('topics/' . $topic->id . '?page=' . $end);
    }
}
