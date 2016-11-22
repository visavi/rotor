<?php

$fid  = isset($params['fid']) ? abs(intval($params['fid'])) : 0;

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

		$forums = DBM::run()->selectFirst('forums', ['id' => $fid]);

		if (!$forums) {
            App::abort('default', 'Данного раздела не существует!');
        }

        if (!empty($forums['parent'])) {
            $forums['subparent'] = DB::run() -> queryFetch("SELECT `id`, `title` FROM `forums` WHERE `id`=? LIMIT 1;", [$forums['parent']]);
        }

        $querysub = DB::run() -> query("SELECT * FROM `forums` WHERE `parent`=? ORDER BY sort ASC;", [$fid]);
        $forums['subforums'] = $querysub -> fetchAll();

        $total = DB::run() -> querySingle("SELECT count(*) FROM `topics` WHERE `forum_id`=?;", [$fid]);
        $page = App::paginate(App::setting('forumtem'), $total);

        $querytopic = DB::run() -> query("SELECT * FROM `topics` WHERE `forum_id`=? ORDER BY `locked` DESC, `last_time` DESC LIMIT ".$page['offset'].", ".App::setting('forumtem').";", [$fid]);
        $forums['topics'] = $querytopic->fetchAll();

        App::view('forum/forum', compact('forums', 'fid', 'page'));

break;

############################################################################################
##                               Подготовка к созданию темы                               ##
############################################################################################
case 'create':

	$config['newtitle'] = 'Создание новой темы';

    $fid = abs(intval(Request::input('fid')));

    if (! is_user()) App::abort(403);

    $forums = DBM::run()->select('forums', null, null, null, ['sort'=>'ASC']);

    if (empty(count($forums))) {
        App::abort('default', 'Разделы форума еще не созданы!');
    }

    if (Request::isMethod('post')) {

        $title = check(Request::input('title'));
        $msg = check(Request::input('msg'));
        $token = check(Request::input('token'));

        $forum = DB::run() -> queryFetch("SELECT * FROM `forums` WHERE `id`=? LIMIT 1;", [$fid]);

        $validation = new Validation();
        $validation -> addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            -> addRule('not_empty', $forum, ['fid' => 'Раздела для новой темы не существует!'])
            -> addRule('empty', $forum['closed'], ['fid' => 'В данном разделе запрещено создавать темы!'])
            -> addRule('equal', [is_flood($log), true], ['msg' => 'Антифлуд! Разрешается cоздавать темы раз в '.flood_period().' сек!'])
            -> addRule('string', $title, ['title' => 'Слишком длинное или короткое название темы!'], true, 5, 50)
            -> addRule('string', $msg, ['msg' => 'Слишком длинный или короткий текст сообщения!'], true, 5, $config['forumtextlength']);

        /* Сделать проверку поиска похожей темы */

        if ($validation->run()) {

            $title = antimat($title);
            $msg = antimat($msg);

            DB::run() -> query("UPDATE `users` SET `allforum`=`allforum`+1, `point`=`point`+1, `money`=`money`+5 WHERE `login`=?", [$log]);

            DB::run() -> query("INSERT INTO `topics` (`forum_id`, `title`, `author`, `posts`, `last_user`, `last_time`) VALUES (?, ?, ?, ?, ?, ?);", [$fid, $title, $log, 1, $log, SITETIME]);

            $lastid = DB::run() -> lastInsertId();

            DB::run() -> query("INSERT INTO `posts` (`topic_id`, `forum_id`, `user`, `text`, `time`, `ip`, `brow`) VALUES (?, ?, ?, ?, ?, ?, ?);", [$lastid, $fid, $log, $msg, SITETIME, App::getClientIp(), App::getUserAgent()]);

            DB::run() -> query("UPDATE `forums` SET `topics`=`topics`+1, `posts`=`posts`+1, `last_id`=?, `last_themes`=?, `last_user`=?, `last_time`=? WHERE `id`=?", [$lastid, $title, $log, SITETIME, $fid]);
            // Обновление родительского форума
            if ($forum['parent'] > 0) {
                DB::run() -> query("UPDATE `forums` SET `last_id`=?, `last_themes`=?, `last_user`=?, `last_time`=? WHERE `id`=?", [$lastid, $title, $log, SITETIME, $forum['parent']]);
            }

            App::setFlash('success', 'Новая тема успешно создана!');
            App::redirect('/topic/'.$lastid);

        } else {
            App::setInput(Request::all());
            App::setFlash('danger', $validation->getErrors());
        }
    }

    $output = [];

    foreach ($forums as $row) {
        $i = $row['id'];
        $p = $row['parent'];
        $output[$p][$i] = $row;
    }
    App::view('forum/forum_create', ['forums' => $output, 'fid' => $fid]);
    break;

endswitch;
