<?php

$tid = param('tid');

if (! is_user()) {
    App::abort('default', 'Для управления закладками, необходимо авторизоваться!');
}

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    $total = Bookmark::where('user_id', App::getUserId())->count();
    $page = App::paginate(Setting::get('forumtem'), $total);

    $topics = Bookmark::select('bookmarks.posts as book_posts', 'bookmarks.topic_id', 'topics.*')
        ->where('bookmarks.user_id', App::getUserId())
        ->leftJoin('topics', 'bookmarks.topic_id', '=', 'topics.id')
        ->with('topic.user', 'topic.lastPost.user')
        ->orderBy('updated_at', 'desc')
        ->offset($page['offset'])
        ->limit(Setting::get('forumtem'))
        ->get();

    App::view('forum/bookmark', compact('topics', 'page'));
break;

############################################################################################
##                         Добавление / удаление закладок                                 ##
############################################################################################
case 'perform':

    if (! Request::ajax()) App::redirect('/');

    $token = check(Request::input('token'));
    $tid = abs(intval(Request::input('tid')));

    $validation = new Validation();
    $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!');

    $topic = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", [$tid]);
    $validation->addRule('custom', $topic, 'Ошибка! Данной темы не существует!');

    if ($validation->run()) {

        $bookmark = DB::run()->querySingle("SELECT `id` FROM `bookmarks` WHERE `topic_id`=? AND `user_id`=? LIMIT 1;", [$tid, App::getUserId()]);

        if ($bookmark) {
            DB::run() -> query("DELETE FROM `bookmarks` WHERE `topic_id`=? AND `user_id`=?;", [$tid, App::getUserId()]);
            exit(json_encode(['status' => 'deleted', 'message' => 'Тема успешно удалена из закладок!']));
        } else {
            DB::run()->query("INSERT INTO `bookmarks` (`user_id`, `topic_id`, `posts`) VALUES (?, ?, ?);", [App::getUserId(), $tid, $topic['posts']]);
            exit(json_encode(['status' => 'added', 'message' => 'Тема успешно добавлена в закладки!']));
        }

    } else {
        exit(json_encode(['status' => 'error', 'message' => current($validation->getErrors())]));
    }
break;

############################################################################################
##                                 Удаление закладок                                      ##
############################################################################################
case 'delete':

    $token = check(Request::input('token'));
    $topicIds = intar(Request::input('del'));
    $page = abs(intval(Request::input('page')));

    $validation = new Validation();
    $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        ->addRule('not_empty', $topicIds, 'Ошибка! Отсутствуют выбранные закладки!');

    if ($validation->run()) {
        $topicIds = implode(',', $topicIds);

        DB::run()->query("DELETE FROM `bookmarks` WHERE `topic_id` IN (".$topicIds.") AND `user_id`=?;", [App::getUserId()]);

        App::setFlash('success', 'Выбранные темы успешно удалены из закладок!');
    } else {
        App::setFlash('danger', $validation->getErrors());
    }

    App::redirect('/forum/bookmark?page='.$page);
break;

endswitch;
