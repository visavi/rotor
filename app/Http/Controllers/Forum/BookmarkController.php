<?php

declare(strict_types=1);

namespace App\Http\Controllers\Forum;

use App\Classes\Validator;
use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Topic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class BookmarkController extends Controller
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->middleware('check.user');
    }

    /**
     * Главная страница
     */
    public function index(): View
    {
        $topics = Bookmark::query()
            ->select('bookmarks.count_posts as bookmark_posts', 'bookmarks.topic_id', 'topics.*')
            ->where('bookmarks.user_id', getUser('id'))
            ->leftJoin('topics', 'bookmarks.topic_id', 'topics.id')
            ->with('topic.user', 'topic.lastPost.user')
            ->orderByDesc('updated_at')
            ->paginate(setting('forumtem'));

        return view('forums/bookmarks', compact('topics'));
    }

    /**
     * Добавление / удаление закладок
     */
    public function perform(Request $request, Validator $validator): Response
    {
        if (! $request->ajax()) {
            return redirect('/');
        }

        $tid = int($request->input('tid'));

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'));

        /** @var Topic $topic */
        $topic = Topic::query()->find($tid);
        $validator->true($topic, __('forums.topic_not_exist'));

        if ($validator->isValid()) {
            $bookmark = Bookmark::query()
                ->where('topic_id', $tid)
                ->where('user_id', getUser('id'))
                ->first();

            if ($bookmark) {
                $bookmark->delete();

                return response()->json([
                    'success' => true,
                    'type'    => 'deleted',
                    'message' => __('forums.bookmark_success_deleted'),
                ]);
            }

            Bookmark::query()->create([
                'user_id'     => getUser('id'),
                'topic_id'    => $tid,
                'count_posts' => $topic->count_posts,
            ]);

            return response()->json([
                'success' => true,
                'type'    => 'added',
                'message' => __('forums.bookmark_success_added'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => current($validator->getErrors()),
        ]);
    }

    /**
     * Удаление закладок
     */
    public function delete(Request $request, Validator $validator): RedirectResponse
    {
        $topicIds = intar($request->input('del'));

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->notEmpty($topicIds, __('forums.bookmarks_missing'));

        if ($validator->isValid()) {
            Bookmark::query()
                ->whereIn('topic_id', intar($request->input('del')))
                ->where('user_id', getUser('id'))
                ->delete();

            setFlash('success', __('forums.bookmarks_selected_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('forums/bookmarks?page=' . int($request->input('page')));
    }
}
