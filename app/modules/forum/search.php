<?php

$start   = abs(intval(Request::input('start', 0)));
$fid     = isset($params['fid']) ? abs(intval($params['fid'])) : 0;
$find    = check(Request::input('find'));
$type    = abs(intval(Request::input('type')));
$where   = abs(intval(Request::input('where')));
$period  = abs(intval(Request::input('period')));
$section = abs(intval(Request::input('section')));

if (empty($find)) {

    $forums = DBM::run()->select('forums', null, null, null, ['order'=>'ASC']);

    if (empty(count($forums))) {
        App::abort('default', 'Разделы форума еще не созданы!');
    }

    $output = [];
    foreach ($forums as $row) {
        $i = $row['id'];
        $p = $row['parent'];
        $output[$p][$i] = $row;
    }

    App::view('forum/search', ['forums' => $output, 'fid' => $fid]);

} else {

    if (!is_utf($find)) {
        $find = win_to_utf($find);
    }

    if (utf_strlen($find) >= 3 && utf_strlen($find) <= 50) {

        $findmewords = explode(' ', utf_lower($find));

        $arrfind = array();
        foreach ($findmewords as $val) {
            if (utf_strlen($val) >= 3) {
                $arrfind[] = (empty($type)) ? '+' . $val . '*' : $val . '*';
            }
        }

        $findme = implode(" ", $arrfind);

        if ($type == 2 && count($findmewords) > 1) {
            $findme = "\"$find\"";
        }

        $config['newtitle'] = $find . ' - Результаты поиска';

        $wheres = (empty($where)) ? 'topics' : 'posts';

        $forumfind = ($type . $wheres . $period . $section . $find);

        // ----------------------------- Поиск в темах -------------------------------//
        if ($wheres == 'topics') {

            if (empty($_SESSION['forumfindres']) || $forumfind != $_SESSION['forumfind']) {

                $searchsec = ($section > 0) ? "`forums_id`=" . $section . " AND" : '';
                $searchper = ($period > 0) ? "`last_time`>" . (SITETIME - ($period * 24 * 60 * 60)) . " AND" : '';

                $querysearch = DB::run()->query("SELECT `id` FROM `topics` WHERE " . $searchsec . " " . $searchper . "  MATCH (`title`) AGAINST ('" . $findme . "' IN BOOLEAN MODE) LIMIT 100;");

                $result = $querysearch->fetchAll(PDO::FETCH_COLUMN);

                $_SESSION['forumfind'] = $forumfind;
                $_SESSION['forumfindres'] = $result;
            }

            $total = count($_SESSION['forumfindres']);

            if ($total > 0) {
                if ($start >= $total) {
                    $start = last_page($total, $config['forumtem']);
                }

                $result = implode(',', $_SESSION['forumfindres']);

                $querytopic = DB::run()->query("SELECT * FROM `topics` WHERE `id` IN (" . $result . ") ORDER BY `last_time` DESC LIMIT " . $start . ", " . $config['forumtem'] . ";");
                $topics = $querytopic->fetchAll();

                App::view('forum/search_topics', compact('topics', 'start', 'total', 'find', 'type', 'where', 'section', 'period'));

            } else {
                App::setInput(Request::all());
                App::setFlash('danger', 'По вашему запросу ничего не найдено!');
                App::redirect('/forum/search');
            }
        }

        // --------------------------- Поиск в сообщениях -------------------------------//
        if ($wheres == 'posts') {

            if (empty($_SESSION['forumfindres']) || $forumfind != $_SESSION['forumfind']) {

                $searchsec = ($section > 0) ? "`posts_forums_id`=" . $section . " AND" : '';
                $searchper = ($period > 0) ? "`posts_time`>" . (SITETIME - ($period * 24 * 60 * 60)) . " AND" : '';

                $querysearch = DB::run()->query("SELECT `posts_id` FROM `posts` WHERE " . $searchsec . " " . $searchper . "  MATCH (`posts_text`) AGAINST ('" . $findme . "' IN BOOLEAN MODE) LIMIT 100;");
                $result = $querysearch->fetchAll(PDO::FETCH_COLUMN);

                $_SESSION['forumfind'] = $forumfind;
                $_SESSION['forumfindres'] = $result;
            }

            $total = count($_SESSION['forumfindres']);

            if ($total > 0) {
                if ($start >= $total) {
                    $start = last_page($total, $config['forumpost']);
                }

                $result = implode(',', $_SESSION['forumfindres']);

                $querypost = DB::run()->query("SELECT `posts`.*, `title` FROM `posts` LEFT JOIN `topics` ON `posts`.`posts_topics_id`=`topics`.`id` WHERE `posts_id` IN (" . $result . ") ORDER BY `posts_time` DESC LIMIT " . $start . ", " . $config['forumpost'] . ";");
                $posts = $querypost->fetchAll();

                App::view('forum/search_posts', compact('posts', 'start', 'total', 'find', 'type', 'where', 'section', 'period'));

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

