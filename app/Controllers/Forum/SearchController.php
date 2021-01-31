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
        $find  = $request->input('find');
        $type = $request->input('type') === 'title' ? 'title' : 'text';
        $data  = collect();

        if ($find) {
            $find = rawurldecode(trim(preg_replace('/[^\w\x7F-\xFF\s]/', ' ', $find)));

            $validator->length($find, 3, 64, ['find' => __('main.request_length')]);
            if ($validator->isValid()) {
                if (config('DB_DRIVER') === 'mysql') {
                    [$sql, $bindings] = ['MATCH (' . $type . ') AGAINST (? IN BOOLEAN MODE)', [$find . '*']];
                } else {
                    [$sql, $bindings] = [$type . ' ILIKE ?', ['%' . $find . '%']];
                }

                if ($type === 'title') {
                    $data = Topic::query()
                        ->whereRaw($sql, $bindings)
                        ->with('forum', 'lastPost.user')
                        ->paginate(setting('forumtem'))
                        ->appends([
                            'find' => $find,
                        ]);
                } else {
                    $data = Post::query()
                        ->whereRaw($sql, $bindings)
                        ->with('user', 'topic.forum')
                        ->paginate(setting('forumpost'))
                        ->appends([
                            'find' => $find,
                        ]);
                }

                if ($data->isEmpty()) {
                    setInput($request->all());
                    setFlash('danger', __('main.empty_found'));
                    redirect('/forums/search');
                }
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('forums/search', compact('data', 'type', 'find'));
    }
}
