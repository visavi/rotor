<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\File;
use App\Models\Forum;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use App\Models\Vote;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ForumController extends AdminController
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

        return view('admin/forums/index', compact('forums'));
    }

    /**
     * Создание раздела
     *
     * @param Request   $request
     * @param Validator $validator
     */
    public function create(Request $request, Validator $validator): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        $token = check($request->input('token'));
        $title = check($request->input('title'));

        $validator->equal($token, $_SESSION['token'], __('validator.token'))
            ->length($title, 5, 50, ['title' => __('validator.text')]);

        if ($validator->isValid()) {
            $max = Forum::query()->max('sort') + 1;

            /** @var Forum $forum */
            $forum = Forum::query()->create([
                'title' => $title,
                'sort'  => $max,
            ]);

            setFlash('success', __('forums.forum_success_created'));
            redirect('/admin/forums/edit/' . $forum->id);
        } else {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/forums');
    }

    /**
     * Редактирование форума
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function edit(int $id, Request $request, Validator $validator): string
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        /** @var Forum $forum */
        $forum = Forum::query()->with('children')->find($id);

        if (! $forum) {
            abort(404, __('forums.forum_not_exist'));
        }

        $forums = Forum::query()
            ->where('parent_id', 0)
            ->orderBy('sort')
            ->get();

        if ($request->isMethod('post')) {
            $token       = check($request->input('token'));
            $parent      = int($request->input('parent'));
            $title       = check($request->input('title'));
            $description = check($request->input('description'));
            $sort        = check($request->input('sort'));
            $closed      = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($title, 5, 50, ['title' => __('validator.text')])
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
                redirect('/admin/forums');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/forums/edit', compact('forums', 'forum'));
    }

    /**
     * Удаление раздела
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return void
     * @throws Exception
     */
    public function delete(int $id, Request $request, Validator $validator): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        /** @var Forum $forum */
        $forum = Forum::query()->with('children')->find($id);

        if (! $forum) {
            abort(404, __('forums.forum_not_exist'));
        }

        $token = check($request->input('token'));

        $validator->equal($token, $_SESSION['token'], __('validator.token'))
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

        redirect('/admin/forums');
    }

    /**
     * Пересчет данных
     *
     * @param Request $request
     */
    public function restatement(Request $request): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        $token = check($request->input('token'));

        if ($token === $_SESSION['token']) {
            restatement('forums');

            setFlash('success', __('main.success_recounted'));
        } else {
            setFlash('danger', __('validator.token'));
        }

        redirect('/admin/forums');
    }

    /**
     * Просмотр тем раздела
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

        return view('admin/forums/forum', compact('forum', 'topics'));
    }

    /**
     * Редактирование темы
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function editTopic(int $id, Request $request, Validator $validator): string
    {
        /** @var Topic $topic */
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        if ($request->isMethod('post')) {
            $token      = check($request->input('token'));
            $title      = check($request->input('title'));
            $note       = check($request->input('note'));
            $moderators = check($request->input('moderators'));
            $locked     = empty($request->input('locked')) ? 0 : 1;
            $closed     = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($title, 5, 50, ['title' => __('validator.text')])
                ->length($note, 0, 250, ['note' => __('validator.text_long')]);

            if ($validator->isValid()) {
                $moderators = implode(',', preg_split('/[\s]*[,][\s]*/', $moderators));

                $topic->update([
                    'title'      => $title,
                    'note'       => $note,
                    'moderators' => $moderators,
                    'locked'     => $locked,
                    'closed'     => $closed,
                ]);

                clearCache(['statForums', 'recentTopics']);
                setFlash('success', __('forums.topic_success_edited'));
                redirect('/admin/forums/' . $topic->forum_id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/forums/edit_topic', compact('topic'));
    }

    /**
     * Перенос темы
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function moveTopic(int $id, Request $request, Validator $validator): string
    {
        /** @var Topic $topic */
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        if ($request->isMethod('post')) {
            $token = check($request->input('token'));
            $fid   = int($request->input('fid'));

            /** @var Forum $forum */
            $forum = Forum::query()->find($fid);

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
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
                redirect('/admin/forums/' . $topic->forum_id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $forums = Forum::query()
            ->where('parent_id', 0)
            ->with('children')
            ->orderBy('sort')
            ->get();

        return view('admin/forums/move_topic', compact('forums', 'topic'));
    }

    /**
     * Закрытие и закрепление тем
     *
     * @param int     $id
     * @param Request $request
     * @return void
     */
    public function actionTopic(int $id, Request $request): void
    {
        $page  = int($request->input('page', 1));
        $token = check($request->input('token'));
        $type  = check($request->input('type'));

        /** @var Topic $topic */
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        if ($token === $_SESSION['token']) {
            switch ($type):
                case 'closed':
                    $topic->update(['closed' => 1]);

                    $vote = Vote::query()->where('topic_id', $topic->id)->first();
                    if ($vote) {
                        $vote->update(['closed' => 1]);
                        $vote->pollings()->delete();
                    }

                    setFlash('success', __('forums.topic_success_closed'));
                    break;

                case 'open':
                    $topic->update(['closed' => 0]);

                    $vote = Vote::query()->where('topic_id', $topic->id)->first();
                    if ($vote) {
                        $vote->update(['closed' => 0]);
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

        redirect('/admin/topics/' . $topic->id . '?page=' . $page);
    }

    /**
     * Удаление тем
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return void
     * @throws Exception
     */
    public function deleteTopic(int $id, Request $request, Validator $validator): void
    {
        $page  = int($request->input('page', 1));
        $token = check($request->input('token'));

        /** @var Topic $topic */
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        $validator->equal($token, $_SESSION['token'], __('validator.token'));

        if ($validator->isValid()) {
            // Удаление загруженных файлов
            $filtered = $topic->posts->load('files')->filter(static function ($post) {
                return $post->files->isNotEmpty();
            });

            $filtered->each(static function(Post $post) {
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

        redirect('/admin/forums/' . $topic->forum->id . '?page=' . $page);
    }

    /**
     * Просмотр темы
     *
     * @param int $id
     * @return string
     */
    public function topic(int $id): string
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
            $topic->curators = User::query()->whereIn('id', explode(',', $topic->moderators))->get();
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

        return view('admin/forums/topic', compact('topic', 'posts', 'vote'));
    }

    /**
     * Редактирование сообщения
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function editPost(int $id, Request $request, Validator $validator): string
    {
        $page = int($request->input('page', 1));

        /** @var Post $post */
        $post = Post::query()->find($id);

        if (! $post) {
            abort(404, __('forums.post_not_exist'));
        }

        if ($request->isMethod('post')) {
            $token   = check($request->input('token'));
            $msg     = check($request->input('msg'));
            $delfile = intar($request->input('delfile'));

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($msg, 5, setting('forumtextlength'), ['msg' => __('validator.text')]);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $post->update([
                    'text'         => $msg,
                    'edit_user_id' => getUser('id'),
                    'updated_at'   => SITETIME,
                ]);

                // ------ Удаление загруженных файлов -------//
                if ($delfile) {
                    $files = File::query()
                        ->where('relate_type', Post::class)
                        ->where('relate_id', $post->id)
                        ->whereIn('id', $delfile)
                        ->get();

                    if ($files->isNotEmpty()) {
                        foreach ($files as $file) {
                            deleteFile(HOME . $file->hash);
                            $file->delete();
                        }
                    }
                }

                setFlash('success', __('main.message_edited_success'));
                redirect('/admin/topics/' . $post->topic_id . '?page=' . $page);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/forums/edit_post', compact('post', 'page'));
    }

    /**
     * Удаление тем
     *
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function deletePosts(Request $request, Validator $validator): void
    {
        $tid   = int($request->input('tid'));
        $page  = int($request->input('page', 1));
        $token = check($request->input('token'));
        $del   = intar($request->input('del'));

        $topic = Topic::query()->where('id', $tid)->first();

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        $validator->equal($token, $_SESSION['token'], __('validator.token'))
            ->true($del, __('validator.deletion'));

        if ($validator->isValid()) {
            $posts = Post::query()
                ->whereIn('id', $del)
                ->get();

            $posts->each(static function(Post $post) {
                $post->delete();
            });

            // Обновление счетчиков
            $topic->restatement();

            setFlash('success', __('main.messages_deleted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/topics/' . $topic->id . '?page=' . $page);
    }

    /**
     * Переадресация к последнему сообщению
     *
     * @return void
     * @param $id
     */
    public function end(int $id): void
    {
        /** @var Topic $topic */
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        $end = ceil($topic->count_posts / setting('forumpost'));
        redirect('/admin/topics/' . $topic->id . '?page=' . $end);
    }
}
