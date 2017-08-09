<?php

class ForumSearchController extends BaseController
{
    public function index()
    {
        $fid = check(Request::input('fid'));
        $find = check(Request::input('find'));
        $type = abs(intval(Request::input('type')));
        $where = abs(intval(Request::input('where')));
        $period = abs(intval(Request::input('period')));
        $section = abs(intval(Request::input('section')));

        if (empty($find)) {

            $forums = Forum::where('parent_id', 0)
                ->with('children')
                ->orderBy('sort')
                ->get();

            if (empty(count($forums))) {
                App::abort('default', 'Разделы форума еще не созданы!');
            }

            App::view('forum/search', compact('forums', 'fid'));

        } else {

            $find = str_replace(['@', '+', '-', '*', '~', '<', '>', '(', ')', '"', "'"], '', $find);

            if (!is_utf($find)) {
                $find = win_to_utf($find);
            }

            if (utf_strlen($find) >= 3 && utf_strlen($find) <= 50) {

                $findmewords = explode(' ', utf_lower($find));

                $arrfind = [];
                foreach ($findmewords as $val) {
                    if (utf_strlen($val) >= 3) {
                        $arrfind[] = (empty($type)) ? '+' . $val . '*' : $val . '*';
                    }
                }

                $findme = implode(" ", $arrfind);

                if ($type == 2 && count($findmewords) > 1) {
                    $findme = "\"$find\"";
                }

                //Setting::get('newtitle') = $find . ' - Результаты поиска';

                $wheres = (empty($where)) ? 'topics' : 'posts';

                $forumfind = ($type . $wheres . $period . $section . $find);

                // ----------------------------- Поиск в темах -------------------------------//
                if ($wheres == 'topics') {

                    if (empty($_SESSION['forumfindres']) || $forumfind != $_SESSION['forumfind']) {

                        $searchsec = ($section > 0) ? "forum_id = " . $section . " AND" : '';
                        $searchper = ($period > 0) ? "updated_at > " . (SITETIME - ($period * 24 * 60 * 60)) . " AND" : '';

                        $result = Topic::select('id')
                            ->whereRaw($searchsec . ' ' . $searchper . ' MATCH (`title`) AGAINST (? IN BOOLEAN MODE)', [$findme])
                            ->limit(100)
                            ->pluck('id')
                            ->all();

                        $_SESSION['forumfind'] = $forumfind;
                        $_SESSION['forumfindres'] = $result;
                    }

                    $total = count($_SESSION['forumfindres']);

                    if ($total > 0) {
                        $page = App::paginate(Setting::get('forumtem'), $total);

                        $topics = Topic::whereIn('id', $_SESSION['forumfindres'])
                            ->with('lastPost.user')
                            ->orderBy('updated_at', 'desc')
                            ->offset($page['offset'])
                            ->limit(Setting::get('forumtem'))
                            ->get();

                        App::view('forum/search_topics', compact('topics', 'page', 'find', 'type', 'where', 'section', 'period'));

                    } else {
                        App::setInput(Request::all());
                        App::setFlash('danger', 'По вашему запросу ничего не найдено!');
                        App::redirect('/forum/search');
                    }
                }

                // --------------------------- Поиск в сообщениях -------------------------------//
                if ($wheres == 'posts') {

                    if (empty($_SESSION['forumfindres']) || $forumfind != $_SESSION['forumfind']) {

                        $searchsec = ($section > 0) ? "forum_id = " . $section . " AND" : '';
                        $searchper = ($period > 0) ? "created_at > " . (SITETIME - ($period * 24 * 60 * 60)) . " AND" : '';

                        $result = Post::select('id')
                            ->whereRaw($searchsec . ' ' . $searchper . ' MATCH (`text`) AGAINST (? IN BOOLEAN MODE)', [$findme])
                            ->limit(100)
                            ->pluck('id')
                            ->all();

                        $_SESSION['forumfind'] = $forumfind;
                        $_SESSION['forumfindres'] = $result;
                    }

                    $total = count($_SESSION['forumfindres']);

                    if ($total > 0) {
                        $page = App::paginate(Setting::get('forumpost'), $total);

                        $posts = Post::whereIn('id', $_SESSION['forumfindres'])
                            ->with('user', 'topic')
                            ->orderBy('created_at', 'desc')
                            ->offset($page['offset'])
                            ->limit(Setting::get('forumpost'))
                            ->get();

                        App::view('forum/search_posts', compact('posts', 'page', 'find', 'type', 'where', 'section', 'period'));

                    } else {
                        App::setInput(Request::all());
                        App::setFlash('danger', 'По вашему запросу ничего не найдено!');
                        App::redirect('/forum/search');
                    }
                }

            } else {
                App::setInput(Request::all());
                App::setFlash('danger', ['find' => 'Запрос должен содержать от 3 до 50 символов!']);
                App::redirect('/forum/search');
            }
        }
    }
}
