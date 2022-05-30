<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\File;
use App\Models\Forum;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class ForumController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $forums = Forum::query()
            ->where('parent_id', 0)
            ->with('lastTopic.lastPost.user')
            ->with('children')
            ->orderBy('sort')
            ->get();

        return view('admin/forums/index', compact('forums'));
    }

    /**
     * Создание раздела
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     */
    public function create(Request $request, Validator $validator): RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        $title = $request->input('title');

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->length($title, 3, 50, ['title' => __('validator.text')]);

        if ($validator->isValid()) {
            $max = Forum::query()->max('sort') + 1;

            /** @var Forum $forum */
            $forum = Forum::query()->create([
                'title' => $title,
                'sort'  => $max,
            ]);

            setFlash('success', __('forums.forum_success_created'));

            return redirect('admin/forums/edit/' . $forum->id);
        }

        setInput($request->all());
        setFlash('danger', $validator->getErrors());

        return redirect('admin/forums');
    }

    /**
     * Редактирование форума
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id, Request $request, Validator $validator)
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        /** @var Forum $forum */
        $forum = Forum::query()->with('children')->find($id);

        if (! $forum) {
            abort(404, __('forums.forum_not_exist'));
        }

        if ($request->isMethod('post')) {
            $parent      = int($request->input('parent'));
            $title       = $request->input('title');
            $description = $request->input('description');
            $sort        = int($request->input('sort'));
            $closed      = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($title, 3, 50, ['title' => __('validator.text')])
                ->length($description, 0, 100, ['description' => __('validator.text')])
                ->notEqual($parent, $forum->id, ['parent' => __('forums.forum_invalid')]);

            if (! empty($parent) && $forum->children->isNotEmpty()) {
                $validator->addError(['parent' => __('forums.forum_has_subforums')]);
            }

            if ($validator->isValid()) {
                $forum->update([
                    'parent_id'   => $parent,
                    'title'       => $title,
                    'description' => $description,
                    'sort'        => $sort,
                    'closed'      => $closed,
                ]);

                setFlash('success', __('forums.forum_success_edited'));

                return redirect('admin/forums');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $forums = $forum->getChildren();

        return view('admin/forums/edit', compact('forums', 'forum'));
    }

    /**
     * Удаление раздела
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     */
    public function delete(int $id, Request $request, Validator $validator): RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        /** @var Forum $forum */
        $forum = Forum::query()->with('children')->find($id);

        if (! $forum) {
            abort(404, __('forums.forum_not_exist'));
        }

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true($forum->children->isEmpty(), __('forums.forum_has_subforums'));

        $topic = Topic::query()->where('forum_id', $forum->id)->first();
        if ($topic) {
            $validator->addError(__('forums.forum_has_topics'));
        }

        if ($validator->isValid()) {
            $forum->delete();

            setFlash('success', __('forums.forum_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('admin/forums');
    }

    /**
     * Пересчет данных
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function restatement(Request $request): RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        if ($request->input('_token') === csrf_token()) {
            restatement('forums');

            setFlash('success', __('main.success_recounted'));
        } else {
            setFlash('danger', __('validator.token'));
        }

        return redirect('admin/forums');
    }

    /**
     * Просмотр тем раздела
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

        $topics = Topic::query()
            ->select('topics.*', 'bookmarks.count_posts as bookmark_posts')
            ->where('forum_id', $forum->id)
            ->leftJoin('bookmarks', static function (JoinClause $join) {
                $join->on('topics.id', 'bookmarks.topic_id')
                    ->where('bookmarks.user_id', getUser('id'));
            })
            ->orderByDesc('locked')
            ->orderByDesc('updated_at')
            ->with('lastPost.user')
            ->paginate(setting('forumtem'));

        return view('admin/forums/forum', compact('forum', 'topics'));
    }

    /**
     * Редактирование темы
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function editTopic(int $id, Request $request, Validator $validator)
    {
        /** @var Topic $topic */
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        if ($request->isMethod('post')) {
            $title       = $request->input('title');
            $note        = $request->input('note');
            $moderators  = (string) $request->input('moderators');
            $locked      = empty($request->input('locked')) ? 0 : 1;
            $closed      = empty($request->input('closed')) ? 0 : 1;
            $closeUserId = $closed ? getUser('id') : null;

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($title, 3, 50, ['title' => __('validator.text')])
                ->length($note, 0, 250, ['note' => __('validator.text_long')]);

            $moderators = preg_split('/[\s]*[,][\s]*/', trim($moderators, ','), -1, PREG_SPLIT_NO_EMPTY);

            foreach ($moderators as $moderator) {
                if (! getUserByLogin($moderator)) {
                    $validator->addError(['moderator' => __('validator.user_login', ['login' => $moderator])]);
                    break;
                }
            }

            if ($validator->isValid()) {
                $topic->update([
                    'title'         => $title,
                    'note'          => $note,
                    'moderators'    => implode(',', $moderators),
                    'locked'        => $locked,
                    'closed'        => $closed,
                    'close_user_id' => $closeUserId,
                ]);

                clearCache(['statForums', 'recentTopics']);
                setFlash('success', __('forums.topic_success_edited'));

                return redirect('admin/forums/' . $topic->forum_id);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/forums/edit_topic', compact('topic'));
    }

    /**
     * Перенос темы
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function moveTopic(int $id, Request $request, Validator $validator)
    {
        /** @var Topic $topic */
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        if ($request->isMethod('post')) {
            $fid = int($request->input('fid'));

            /** @var Forum $forum */
            $forum = Forum::query()->find($fid);

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->notEmpty($forum, ['forum' => __('forums.forum_not_exist')]);

            if ($forum) {
                $validator->empty($forum->closed, ['forum' => __('forums.forum_closed')]);
                $validator->notEqual($topic->forum_id, $forum->id, ['forum' => __('forums.forum_invalid')]);
            }

            if ($validator->isValid()) {
                $oldTopic = $topic->replicate();

                $topic->update([
                    'forum_id' => $forum->id,
                ]);

                // Обновление счетчиков
                $topic->forum->restatement();
                $oldTopic->forum->restatement();

                setFlash('success', __('forums.topic_success_moved'));

                return redirect('admin/forums/' . $topic->forum_id);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $forums = $topic->forum->getChildren();

        return view('admin/forums/move_topic', compact('forums', 'topic'));
    }

    /**
     * Закрытие и закрепление тем
     *
     * @param int     $id
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function actionTopic(int $id, Request $request): RedirectResponse
    {
        $page = int($request->input('page', 1));

        /** @var Topic $topic */
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        if ($request->input('_token') === csrf_token()) {
            switch ($request->input('type')) :
                case 'closed':
                    $topic->update([
                        'closed'        => 1,
                        'close_user_id' => getUser('id'),
                    ]);

                    if ($topic->vote) {
                        $topic->vote->update(['closed' => 1]);
                        $topic->vote->pollings()->delete();
                    }

                    setFlash('success', __('forums.topic_success_closed'));
                    break;

                case 'open':
                    $topic->update([
                        'closed' => 0,
                        'close_user_id' => null,
                    ]);

                    if ($topic->vote) {
                        $topic->vote->update(['closed' => 0]);
                    }

                    setFlash('success', __('forums.topic_success_opened'));
                    break;

                case 'locked':
                    $topic->update(['locked' => 1]);
                    setFlash('success', __('forums.topic_success_pinned'));
                    break;

                case 'unlocked':
                    $topic->update(['locked' => 0]);
                    setFlash('success', __('forums.topic_success_unpinned'));
                    break;

                default:
                    setFlash('danger', __('main.action_not_selected'));
            endswitch;
        } else {
            setFlash('danger', __('validator.token'));
        }

        return redirect('admin/topics/' . $topic->id . '?page=' . $page);
    }

    /**
     * Удаление тем
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     */
    public function deleteTopic(int $id, Request $request, Validator $validator): RedirectResponse
    {
        $page = int($request->input('page', 1));

        /** @var Topic $topic */
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'));

        if ($validator->isValid()) {
            // Удаление загруженных файлов
            $filtered = $topic->posts->load('files')->filter(static function ($post) {
                return $post->files->isNotEmpty();
            });

            $filtered->each(static function (Post $post) {
                $post->delete();
            });

            // Удаление голосований
            $topic->vote->answers()->delete();
            $topic->vote->pollings()->delete();
            $topic->vote->delete();

            // Удаление закладок
            $topic->bookmarks()->delete();

            $topic->posts()->delete();
            $topic->delete();

            // Обновление счетчиков
            $topic->forum->restatement();

            clearCache(['statForums', 'recentTopics']);
            setFlash('success', __('forums.topic_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('admin/forums/' . $topic->forum->id . '?page=' . $page);
    }

    /**
     * Просмотр темы
     *
     * @param int $id
     *
     * @return View
     */
    public function topic(int $id): View
    {
        $topic = Topic::query()->where('id', $id)->with('forum.parent')->first();

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        $posts = Post::query()
            ->select('posts.*', 'pollings.vote')
            ->where('topic_id', $topic->id)
            ->leftJoin('pollings', static function (JoinClause $join) {
                $join->on('posts.id', 'pollings.relate_id')
                    ->where('pollings.relate_type', Post::$morphName)
                    ->where('pollings.user_id', getUser('id'));
            })
            ->with('files', 'user', 'editUser')
            ->orderBy('created_at')
            ->paginate(setting('forumpost'));

        // Кураторы
        if ($topic->moderators) {
            $topic->curators = User::query()->whereIn('login', explode(',', (string) $topic->moderators))->get();
        }

        // Голосование
        $vote = Vote::query()->where('topic_id', $topic->id)->first();

        if ($vote) {
            $vote->poll = $vote->pollings()
                ->where('user_id', getUser('id'))
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

        $files = File::query()
            ->where('relate_type', Post::$morphName)
            ->where('relate_id', 0)
            ->where('user_id', getUser('id'))
            ->orderBy('created_at')
            ->get();

        return view('admin/forums/topic', compact('topic', 'posts', 'vote', 'files'));
    }

    /**
     * Редактирование сообщения
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function editPost(int $id, Request $request, Validator $validator)
    {
        $page = int($request->input('page', 1));

        /** @var Post $post */
        $post = Post::query()->find($id);

        if (! $post) {
            abort(404, __('forums.post_not_exist'));
        }

        if ($request->isMethod('post')) {
            $msg = $request->input('msg');

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($msg, 5, setting('forumtextlength'), ['msg' => __('validator.text')]);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $post->update([
                    'text'         => $msg,
                    'edit_user_id' => getUser('id'),
                    'updated_at'   => SITETIME,
                ]);

                setFlash('success', __('main.message_edited_success'));

                return redirect('admin/topics/' . $post->topic_id . '?page=' . $page);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/forums/edit_post', compact('post', 'page'));
    }

    /**
     * Удаление тем
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     */
    public function deletePosts(Request $request, Validator $validator): RedirectResponse
    {
        $tid  = int($request->input('tid'));
        $page = int($request->input('page', 1));
        $del  = intar($request->input('del'));

        $topic = Topic::query()->where('id', $tid)->first();

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true($del, __('validator.deletion'));

        if ($validator->isValid()) {
            $posts = Post::query()
                ->whereIn('id', $del)
                ->get();

            $posts->each(static function (Post $post) {
                $post->delete();
            });

            // Обновление счетчиков
            $topic->restatement();

            setFlash('success', __('main.messages_deleted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('admin/topics/' . $topic->id . '?page=' . $page);
    }

    /**
     * Переадресация к последнему сообщению
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

        return redirect('admin/topics/' . $topic->id . '?page=' . $end);
    }
}
