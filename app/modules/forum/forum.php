<?php

$start = abs(intval(Request::input('start', 0)));
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

        $page = floor(1 + $start / $config['forumtem']);
        $config['header'] = $forums['title'];
        $config['newtitle'] = $forums['title'].' (Стр. '.$page.')';

        if (!empty($forums['parent'])) {
            $forums['subparent'] = DB::run() -> queryFetch("SELECT `id`, `title` FROM `forums` WHERE `id`=? LIMIT 1;", array($forums['parent']));
        }

        $querysub = DB::run() -> query("SELECT * FROM `forums` WHERE `parent`=? ORDER BY `order` ASC;", array($fid));
        $forums['subforums'] = $querysub -> fetchAll();

        $total = DB::run() -> querySingle("SELECT count(*) FROM `topics` WHERE `forums_id`=?;", array($fid));

        if ($total > 0 && $start >= $total) {
            $start = last_page($total, $config['forumtem']);
        }

        $querytopic = DB::run() -> query("SELECT * FROM `topics` WHERE `forums_id`=? ORDER BY `locked` DESC, `last_time` DESC LIMIT ".$start.", ".$config['forumtem'].";", array($fid));
        $forums['topics'] = $querytopic->fetchAll();

        App::view('forum/forum', compact('forums', 'fid', 'start', 'total'));

break;

############################################################################################
##                               Подготовка к созданию темы                               ##
############################################################################################
case 'create':

	$config['newtitle'] = 'Создание новой темы';

    $fid = abs(intval(Request::input('fid')));

    if (! is_user()) App::abort(403);

    $forums = DBM::run()->select('forums', null, null, null, ['order'=>'ASC']);

    if (empty(count($forums))) {
        App::abort('default', 'Разделы форума еще не созданы!');
    }

    if (Request::isMethod('post')) {

        $title = check(Request::input('title'));
        $msg = check(Request::input('msg'));
        $token = check(Request::input('token'));

        $forum = DB::run() -> queryFetch("SELECT * FROM `forums` WHERE `id`=? LIMIT 1;", array($fid));

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

            DB::run() -> query("UPDATE `users` SET `allforum`=`allforum`+1, `point`=`point`+1, `money`=`money`+5 WHERE `login`=?", array($log));

            DB::run() -> query("INSERT INTO `topics` (`forums_id`, `title`, `author`, `posts`, `last_user`, `last_time`) VALUES (?, ?, ?, ?, ?, ?);", array($fid, $title, $log, 1, $log, SITETIME));

            $lastid = DB::run() -> lastInsertId();

            DB::run() -> query("INSERT INTO `posts` (`topics_id`, `forums_id`, `user`, `text`, `time`, `ip`, `brow`) VALUES (?, ?, ?, ?, ?, ?, ?);", array($lastid, $fid, $log, $msg, SITETIME, App::getClientIp(), App::getUserAgent()));

            DB::run() -> query("UPDATE `forums` SET `topics`=`topics`+1, `posts`=`posts`+1, `last_id`=?, `last_themes`=?, `last_user`=?, `last_time`=? WHERE `id`=?", array($lastid, $title, $log, SITETIME, $fid));
            // Обновление родительского форума
            if ($forum['parent'] > 0) {
                DB::run() -> query("UPDATE `forums` SET `last_id`=?, `last_themes`=?, `last_user`=?, `last_time`=? WHERE `id`=?", array($lastid, $title, $log, SITETIME, $forum['parent']));
            }

            App::setFlash('success', 'Новая тема успешно создана!');
            App::redirect('/topic/'.$lastid);

        } else {
            App::setInput(Request::all());
            App::setFlash('danger', $validation->getErrors());
        }
    }

    $output = array();

    foreach ($forums as $row) {
        $i = $row['id'];
        $p = $row['parent'];
        $output[$p][$i] = $row;
    }
    App::view('forum/forum_create', ['forums' => $output, 'fid' => $fid]);
    break;

endswitch;
