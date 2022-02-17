<?php

declare(strict_types=1);

namespace App\Http\Controllers\Forum;

use App\Classes\Validator;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Topic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Поиск
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function index(Request $request, Validator $validator)
    {
        $find  = $request->input('find');
        $type = $request->input('type') === 'title' ? 'title' : 'text';
        $data  = collect();

        if ($find) {
            $find = rawurldecode(trim(preg_replace('/[^\w\x7F-\xFF\s]/', ' ', $find)));

            $validator->length($find, 3, 64, ['find' => __('main.request_length')]);
            if ($validator->isValid()) {
                if ($type === 'title') {
                    $data = Topic::query()
                        ->whereFullText($type, $find . '*', ['mode' => 'boolean'])
                        ->with('forum', 'lastPost.user')
                        ->paginate(setting('forumtem'))
                        ->appends(compact('find', 'type'));
                } else {
                    $data = Post::query()
                        ->whereFullText($type, $find . '*', ['mode' => 'boolean'])
                        ->with('user', 'topic.forum')
                        ->paginate(setting('forumpost'))
                        ->appends(compact('find', 'type'));
                }

                if ($data->isEmpty()) {
                    setInput($request->all());
                    setFlash('danger', __('main.empty_found'));
                    return redirect('forums/search');
                }
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('forums/search', compact('data', 'type', 'find'));
    }
}
