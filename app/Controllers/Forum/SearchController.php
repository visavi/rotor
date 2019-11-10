<?php

declare(strict_types=1);

namespace App\Controllers\Forum;

use App\Controllers\BaseController;
use App\Models\Forum;
use App\Models\Post;
use App\Models\Topic;
use Illuminate\Http\Request;

class SearchController extends BaseController
{
    /**
     * Поиск
     *
     * @param Request $request
     * @return string
     */
    public function index(Request $request): ?string
    {
        $fid     = int($request->input('fid'));
        $find    = check($request->input('find'));
        $type    = int($request->input('type'));
        $where   = int($request->input('where'));
        $period  = int($request->input('period'));
        $section = int($request->input('section'));

        if (! $find) {
            $forums = Forum::query()
                ->where('parent_id', 0)
                ->with('children')
                ->orderBy('sort')
                ->get();

            if ($forums->isEmpty()) {
                abort('default', __('forums.empty_forums'));
            }

            return view('forums/search', compact('forums', 'fid'));

        }

        $find = str_replace(['@', '+', '-', '*', '~', '<', '>', '(', ')', '"', "'"], '', $find);

        if (! isUtf($find)) {
            $find = winToUtf($find);
        }

        $strlen = utfStrlen($find);
        if ($strlen >= 3 && $strlen <= 50) {

            $findmewords = explode(' ', utfLower($find));

            $arrfind = [];
            foreach ($findmewords as $val) {
                if (utfStrlen($val) >= 3) {
                    $arrfind[] = empty($type) ? '+' . $val . '*' : $val . '*';
                }
            }

            $findme = implode(' ', $arrfind);

            if ($type === 2 && count($findmewords) > 1) {
                $findme = "\"$find\"";
            }

            $wheres = empty($where) ? 'topics' : 'posts';

            $forumfind = ($type . $wheres . $period . $section . $find);

            // Поиск в темах
            if ($wheres === 'topics') {
                if (empty($_SESSION['forumfindres']) || $forumfind !== $_SESSION['forumfind']) {
                    $searchsec = ($section > 0) ? 'forum_id = ' . $section . ' AND' : '';
                    $searchper = ($period > 0) ? 'updated_at > ' . strtotime('-' . $period . ' day', SITETIME) . ' AND' : '';

                    $result = Topic::query()
                        ->select('id')
                        ->whereRaw($searchsec . ' ' . $searchper . ' MATCH (`title`) AGAINST (? IN BOOLEAN MODE)', [$findme])
                        ->limit(100)
                        ->pluck('id')
                        ->all();

                    $_SESSION['forumfind'] = $forumfind;
                    $_SESSION['forumfindres'] = $result;
                }

                $total = count($_SESSION['forumfindres']);

                if ($total > 0) {
                    $topics = Topic::query()
                        ->whereIn('id', $_SESSION['forumfindres'])
                        ->orderByDesc('updated_at')
                        ->with('forum', 'lastPost.user')
                        ->paginate(setting('forumtem'))
                        ->appends([
                            'fid'     => $fid,
                            'find'    => $find,
                            'section' => $section,
                            'period'  => $period,
                            'where'   => $where,
                            'type'    => $type,
                        ]);

                    return view('forums/search_topics', compact('topics', 'find', 'type', 'where', 'section', 'period'));
                }

                setInput($request->all());
                setFlash('danger', __('main.empty_found'));
                redirect('/forums/search');
            }

            // Поиск в сообщениях
            if ($wheres === 'posts') {
                if (empty($_SESSION['forumfindres']) || $forumfind !== $_SESSION['forumfind']) {
                    $searchsec = ($section > 0) ? 'topics.forum_id = ' . $section . ' AND' : '';
                    $searchper = ($period > 0) ? 'posts.created_at > ' . strtotime('-' . $period . ' day', SITETIME) . ' AND' : '';

                    $result = Post::query()
                        ->select('posts.id')
                        ->leftJoin('topics', 'posts.topic_id', 'topics.id')
                        ->whereRaw($searchsec . ' ' . $searchper . ' MATCH (`text`) AGAINST (? IN BOOLEAN MODE)', [$findme])
                        ->limit(100)
                        ->pluck('id')
                        ->all();

                    $_SESSION['forumfind'] = $forumfind;
                    $_SESSION['forumfindres'] = $result;
                }

                $total = count($_SESSION['forumfindres']);

                if ($total > 0) {
                    $posts = Post::query()
                        ->whereIn('id', $_SESSION['forumfindres'])
                        ->with('user', 'topic.forum')
                        ->orderByDesc('created_at')
                        ->paginate(setting('forumpost'))
                        ->appends([
                            'fid'     => $fid,
                            'find'    => $find,
                            'section' => $section,
                            'period'  => $period,
                            'where'   => $where,
                            'type'    => $type,
                        ]);

                    return view('forums/search_posts', compact('posts', 'find', 'type', 'where', 'section', 'period'));
                }

                setInput($request->all());
                setFlash('danger', __('main.empty_found'));
                redirect('/forums/search');
            }

        } else {
            setInput($request->all());
            setFlash('danger', ['find' => __('main.request_requirements')]);
            redirect('/forums/search');
        }
    }
}

