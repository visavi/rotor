<?php

declare(strict_types=1);

namespace Modules\Forum\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ForumResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\TopicResource;
use App\Models\Flood;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Forum\Models\Forum;
use Modules\Forum\Models\Post;
use Modules\Forum\Models\Topic;
use Modules\Forum\Models\Vote;
use Modules\Forum\Models\VoteAnswer;

class ForumApiController extends Controller
{
    public function categoryForums(): JsonResource
    {
        $forums = Forum::query()
            ->where('parent_id', 0)
            ->with('children', 'lastTopic.lastPost.user')
            ->orderBy('sort')
            ->get();

        if ($forums->isEmpty()) {
            abort(200, __('forums.empty_forums'));
        }

        return ForumResource::collection($forums);
    }

    public function forums(int $id, Request $request): JsonResource
    {
        $forum = Forum::query()->find($id);

        if (! $forum) {
            abort(404, __('forums.forum_not_exist'));
        }

        $topics = Topic::query()
            ->where('forum_id', $id)
            ->with('user', 'lastPost.user')
            ->orderByDesc('locked')
            ->orderBy('updated_at', $this->getOrder($request))
            ->paginate($this->getPerPage($request));

        return TopicResource::collection($topics)
            ->additional(['forum' => ForumResource::make($forum)]);
    }

    public function topics(int $id, Request $request): JsonResource
    {
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        $posts = Post::query()
            ->where('topic_id', $id)
            ->with('user', 'files')
            ->orderBy('created_at', $this->getOrder($request, 'asc'))
            ->paginate($this->getPerPage($request));

        return PostResource::collection($posts)
            ->additional(['topic' => TopicResource::make($topic)]);
    }

    public function createPost(int $id, Request $request, Flood $flood): JsonResponse
    {
        $user = getUser();

        $topic = Topic::query()
            ->where('topics.id', $id)
            ->with('forum.parent')
            ->first();

        if (! $topic) {
            abort(404, __('forums.topic_not_exist'));
        }

        $lastPost = Post::query()->where('topic_id', $topic->id)->orderByDesc('id')->value('text');

        $validated = $request->validate([
            'text' => [
                'required',
                'string',
                'min:' . setting('forum_text_min'),
                'max:' . setting('forum_text_max'),
                function (string $attribute, mixed $value, Closure $fail) use ($topic, $flood, $lastPost) {
                    if ($topic->closed) {
                        $fail(__('forums.topic_closed'));
                    }

                    if ($flood->isFlood()) {
                        $fail(__('validator.flood', ['sec' => $flood->getPeriod()]));
                    }

                    if ($lastPost === $value) {
                        $fail(__('forums.post_repeat'));
                    }
                },
            ],
            'files'   => ['nullable', 'array', 'max:' . setting('maxfiles')],
            'files.*' => ['file', 'max:' . setting('filesize'), 'mimes:' . setting('file_extensions')],
        ]);

        $msg = antimat($validated['text']);

        $uploadedFiles = $request->file('files', []);

        $post = Post::query()->create([
            'topic_id'   => $topic->id,
            'user_id'    => $user->id,
            'text'       => $msg,
            'created_at' => SITETIME,
            'ip'         => getIp(),
            'brow'       => getBrowser(),
        ]);

        foreach ($uploadedFiles as $file) {
            $post->uploadFile($file);
        }

        $flood->saveState();
        sendNotify($msg, route('topics.topic', ['id' => $topic->id, 'pid' => $post->id], false), $topic->title);

        $post->load('user', 'files');

        return response()->json([
            'message' => __('main.message_added_success'),
            'post'    => PostResource::make($post),
        ], 201);
    }

    public function createTopic(int $id, Request $request, Flood $flood): JsonResponse
    {
        $user = getUser();

        $forum = Forum::query()->find($id);

        if (! $forum) {
            abort(404, __('forums.forum_not_exist'));
        }

        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'min:' . setting('forum_title_min'),
                'max:' . setting('forum_title_max'),
                function (string $attribute, mixed $value, Closure $fail) use ($forum, $flood) {
                    if ($forum->closed) {
                        $fail(__('forums.forum_closed'));
                    }

                    if ($flood->isFlood()) {
                        $fail(__('validator.flood', ['sec' => $flood->getPeriod()]));
                    }
                },
            ],
            'text'      => ['required', 'string', 'min:' . setting('forum_text_min'), 'max:' . setting('forum_text_max')],
            'question'  => ['nullable', 'string', 'min:' . setting('vote_title_min'), 'max:' . setting('vote_title_max')],
            'answers'   => ['required_with:question', 'array', 'min:2', 'max:10'],
            'answers.*' => ['string', 'min:' . setting('vote_answer_min'), 'max:' . setting('vote_answer_max')],
            'files'     => ['nullable', 'array', 'max:' . setting('maxfiles')],
            'files.*'   => ['file', 'max:' . setting('filesize'), 'mimes:' . setting('file_extensions')],
        ]);

        $topic = Topic::query()->create([
            'forum_id'   => $forum->id,
            'title'      => antimat($validated['title']),
            'user_id'    => $user->id,
            'created_at' => SITETIME,
            'updated_at' => SITETIME,
        ]);

        $post = Post::query()->create([
            'topic_id'   => $topic->id,
            'user_id'    => $user->id,
            'text'       => antimat($validated['text']),
            'created_at' => SITETIME,
            'ip'         => getIp(),
            'brow'       => getBrowser(),
        ]);

        foreach ($request->file('files', []) as $file) {
            $post->uploadFile($file);
        }

        $flood->saveState();

        if (! empty($validated['question']) && ! empty($validated['answers'])) {
            $answers = array_unique(array_diff($validated['answers'], ['']));
            $poll = Vote::query()->create([
                'title'      => $validated['question'],
                'topic_id'   => $topic->id,
                'created_at' => SITETIME,
            ]);

            $prepareAnswers = array_map(static fn ($answer) => ['vote_id' => $poll->id, 'answer' => $answer], $answers);
            VoteAnswer::query()->insert($prepareAnswers);
        }

        $topic->refresh()->load('user', 'lastPost.user');

        return response()->json([
            'message' => __('forums.topic_success_created'),
            'topic'   => TopicResource::make($topic),
        ], 201);
    }

    private function getOrder(Request $request, string $default = 'desc'): string
    {
        $order = $request->input('order', $default);

        return in_array($order, ['asc', 'desc']) ? $order : $default;
    }

    private function getPerPage(Request $request): int
    {
        $perPage = $request->integer('per_page', 10);

        return max(1, min($perPage, 100));
    }
}
