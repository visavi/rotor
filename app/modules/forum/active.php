<?php

$login = check(Request::input('user', App::getUsername()));

$user = User::where('login', $login)->first();

if (! $user) {
    App::abort('default', 'Пользователь не найден!');
}

switch ($act):
############################################################################################
##                                        Вывод тем                                       ##
############################################################################################
case 'themes':
    $total = Topic::where('user_id', $user->id)->count();

    if (! $total) {
        App::abort('default', 'Созданных тем еще нет!');
    }

    $page = App::paginate(App::setting('forumtem'), $total);

    $topics = Topic::where('user_id', $user->id)
        ->orderBy('updated_at', 'desc')
        ->limit(App::setting('forumtem'))
        ->offset($page['offset'])
        ->with('forum', 'user', 'lastPost.user')
        ->get();

    App::view('forum/active_themes', compact('topics', 'user', 'page'));
break;

############################################################################################
##                                     Вывод сообщений                                    ##
############################################################################################
case 'posts':
    $total = Post::where('user_id', $user->id)->count();

    if (! $total) {
        App::abort('default', 'Созданных сообщений еще нет!');
    }

    $page = App::paginate(App::setting('forumpost'), $total);

    $posts = Post::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->limit(App::setting('forumpost'))
        ->offset($page['offset'])
        ->with('topic', 'user')
        ->get();

    App::view('forum/active_posts', compact('posts', 'user', 'page'));
break;

############################################################################################
##                                    Удаление сообщений                                  ##
############################################################################################
case 'delete':

    if (! Request::ajax()) App::redirect('/');
    if (! is_admin()) App::abort(403, 'Удалять сообщения могут только модераторы!');

    $token = check(Request::input('token'));
    $tid = abs(intval(Request::input('tid')));

    $validation = new Validation();
    $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!');

    $post = Post::where('id', $tid)
        ->with('topic.forum')
        ->first();

    $validation->addRule('custom', $post, 'Ошибка! Данного сообщения не существует!');

    if ($validation->run()) {

        DB::run() -> query("DELETE FROM `posts` WHERE `id`=? AND `topic_id`=?;", [$tid, $post['topic_id']]);
        DB::run() -> query("UPDATE `topics` SET `posts`=`posts`-? WHERE `id`=?;", [1, $post['topic_id']]);
        DB::run() -> query("UPDATE `forums` SET `posts`=`posts`-? WHERE `id`=?;", [1, $post->getTopic()->getForum()->id]);

        exit(json_encode(['status' => 'success']));
    } else {
        exit(json_encode(['status' => 'error', 'message' => current($validation->getErrors())]));
    }
break;

endswitch;

