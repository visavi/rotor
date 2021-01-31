<?php

declare(strict_types=1);

namespace App\Controllers\Forum;

use App\Classes\Validator;
use App\Controllers\BaseController;
use App\Models\Post;
use App\Models\Topic;
use Illuminate\Http\Request;

class SearchController extends BaseController
{
    /**
     * Поиск
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function index(Request $request, Validator $validator): string
    {
        $find = check($request->input('find'));
        $posts = collect();

        if ($find) {
            $validator->length($find, 3, 64, ['find' => __('main.request_requirements')]);

            if ($validator->isValid()) {
                if (empty($_SESSION['forumfindres']) || $find !== $_SESSION['forumfind']) {
                    $findPosts = Post::query()
                        ->selectRaw('id, MATCH (text) AGAINST (? IN BOOLEAN MODE) as score', [$find])
                        ->whereRaw('MATCH (text) AGAINST (? IN BOOLEAN MODE)', [$find]);

                    $result = Topic::query()
                        ->selectRaw('last_post_id as id, MATCH (title) AGAINST (? IN BOOLEAN MODE) as score', [$find])
                        ->whereRaw('MATCH (title) AGAINST (? IN BOOLEAN MODE)', [$find])
                        ->union($findPosts)
                        ->orderByDesc('score')
                        ->limit(100)
                        ->pluck('id')
                        ->all();

                    $_SESSION['forumfind'] = $find;
                    $_SESSION['forumfindres'] = $result;
                }

                $validator->notEmpty($_SESSION['forumfindres'], ['find' => __('main.empty_found')]);
            }

            if ($validator->isValid()) {
                $posts = Post::query()
                    ->whereIn('id', $_SESSION['forumfindres'])
                    ->with('user', 'topic.forum')
                    ->orderByDesc('created_at')
                    ->paginate(setting('forumpost'))
                    ->appends([
                        'find' => $find,
                    ]);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('forums/search', compact('posts', 'find'));
    }
}
