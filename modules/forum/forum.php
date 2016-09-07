<?php

$start = abs(intval(Request::input('start', 0)));
$fid  = isset($params['fid']) ? abs(intval($params['fid'])) : 0;

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

	if (!empty($fid)) {
		$forums = DB::run() -> queryFetch("SELECT * FROM `forums` WHERE `forums_id`=? LIMIT 1;", array($fid));

		if (!empty($forums)) {

			$page = floor(1 + $start / $config['forumtem']);
			$config['header'] = $forums['forums_title'];
			$config['newtitle'] = $forums['forums_title'].' (Стр. '.$page.')';

			if (!empty($forums['forums_parent'])) {
				$forums['subparent'] = DB::run() -> queryFetch("SELECT `forums_id`, `forums_title` FROM `forums` WHERE `forums_id`=? LIMIT 1;", array($forums['forums_parent']));
			}

			$querysub = DB::run() -> query("SELECT * FROM `forums` WHERE `forums_parent`=? ORDER BY `forums_order` ASC;", array($fid));
			$forums['subforums'] = $querysub -> fetchAll();

			$total = DB::run() -> querySingle("SELECT count(*) FROM `topics` WHERE `topics_forums_id`=?;", array($fid));

			if ($total > 0 && $start >= $total) {
				$start = last_page($total, $config['forumtem']);
			}

			$querytopic = DB::run() -> query("SELECT * FROM `topics` WHERE `topics_forums_id`=? ORDER BY `topics_locked` DESC, `topics_last_time` DESC LIMIT ".$start.", ".$config['forumtem'].";", array($fid));
			$forums['topics'] = $querytopic->fetchAll();

            App::view('forum/forum', compact('forums', 'fid', 'start', 'total'));

		} else {
			show_error('Ошибка! Данного раздела не существует!');
		}
	} else {
		redirect("index.php");

	}
break;

############################################################################################
##                               Подготовка к созданию темы                               ##
############################################################################################
case 'create':

	$config['newtitle'] = 'Создание новой темы';

    $fid = abs(intval(Request::input('fid')));

    if (! is_user()) App::abort(403);

    $forums = DBM::run()->select('forums', null, null, null, ['forums_order'=>'ASC']);

    if (empty(count($forums))) {
        App::abort('default', 'Разделы форума еще не созданы!');
    }

    if (Request::isMethod('post')) {

        $title = check(Request::input('title'));
        $msg = check(Request::input('msg'));
        $token = check(Request::input('token'));

        $forum = DB::run() -> queryFetch("SELECT * FROM `forums` WHERE `forums_id`=? LIMIT 1;", array($fid));

        $validation = new Validation();
        $validation -> addRule('equal', array($token, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
            -> addRule('not_empty', $forum, 'Раздела для новой темы не существует!')
            -> addRule('empty', $forum['forums_closed'], 'В данном разделе запрещено создавать темы!')
            -> addRule('equal', [is_flood($log), true], 'Антифлуд! Разрешается cоздавать темы раз в '.flood_period().' сек!')
            -> addRule('string', $title, 'Слишком длинное или короткое название темы!', true, 5, 50)
            -> addRule('string', $msg, 'Слишком длинный или короткий текст сообщения!', true, 5, $config['forumtextlength']);

        /* Сделать проверку поиска похожей темы */

        if ($validation->run()) {

            $title = antimat($title);
            $msg = antimat($msg);

            DB::run() -> query("UPDATE `users` SET `users_allforum`=`users_allforum`+1, `users_point`=`users_point`+1, `users_money`=`users_money`+5 WHERE `users_login`=?", array($log));

            DB::run() -> query("INSERT INTO `topics` (`topics_forums_id`, `topics_title`, `topics_author`, `topics_posts`, `topics_last_user`, `topics_last_time`) VALUES (?, ?, ?, ?, ?, ?);", array($fid, $title, $log, 1, $log, SITETIME));

            $lastid = DB::run() -> lastInsertId();

            DB::run() -> query("INSERT INTO `posts` (`posts_topics_id`, `posts_forums_id`, `posts_user`, `posts_text`, `posts_time`, `posts_ip`, `posts_brow`) VALUES (?, ?, ?, ?, ?, ?, ?);", array($lastid, $fid, $log, $msg, SITETIME, App::getClientIp(), App::getUserAgent()));

            DB::run() -> query("UPDATE `forums` SET `forums_topics`=`forums_topics`+1, `forums_posts`=`forums_posts`+1, `forums_last_id`=?, `forums_last_themes`=?, `forums_last_user`=?, `forums_last_time`=? WHERE `forums_id`=?", array($lastid, $title, $log, SITETIME, $fid));
            // Обновление родительского форума
            if ($forum['forums_parent'] > 0) {
                DB::run() -> query("UPDATE `forums` SET `forums_last_id`=?, `forums_last_themes`=?, `forums_last_user`=?, `forums_last_time`=? WHERE `forums_id`=?", array($lastid, $title, $log, SITETIME, $forum['forums_parent']));
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
        $i = $row['forums_id'];
        $p = $row['forums_parent'];
        $output[$p][$i] = $row;
    }
    App::view('forum/forum_create', ['forums' => $output, 'fid' => $fid]);
    break;

endswitch;
