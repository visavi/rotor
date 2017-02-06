<?php

$fid = param('fid');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    $forum = Forum::with('parent')->find_one($fid);

    if (!$forum) {
        App::abort('default', 'Данного раздела не существует!');
    }

    $forum->childrens = Forum::where('parent_id', $forum->id)
        ->with('countPost', 'countTopic', 'lastTopic.lastPost.user')
        ->find_many();


    $total = Topic::where('forum_id', $fid)->count();
    $page = App::paginate(App::setting('forumtem'), $total);

    $topics = Topic::where('forum_id', $fid)
        ->order_by_desc('locked')
        ->order_by_desc('time')
        ->limit(App::setting('forumtem'))
        ->offset($page['offset'])
        ->with('countPost', 'lastPost.user')
        ->find_many();

    App::view('forum/forum', compact('forum', 'topics', 'page'));
break;

############################################################################################
##                               Подготовка к созданию темы                               ##
############################################################################################
case 'create':

    $config['newtitle'] = 'Создание новой темы';

    $fid = abs(intval(Request::input('fid')));

    if (! is_user()) App::abort(403);

    $forums = Forum::order_by_asc('sort')->find_many();

    if (empty(count($forums))) {
        App::abort('default', 'Разделы форума еще не созданы!');
    }

    if (Request::isMethod('post')) {

        $title = check(Request::input('title'));
        $msg = check(Request::input('msg'));
        $token = check(Request::input('token'));
        $vote = Request::has('vote') ? 1 : 0;
        $question = check(Request::input('question'));
        $answers = check(Request::input('answer'));

        $forum = DB::run() -> queryFetch("SELECT * FROM `forums` WHERE `id`=? LIMIT 1;", [$fid]);

        $validation = new Validation();
        $validation -> addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            -> addRule('not_empty', $forum, ['fid' => 'Раздела для новой темы не существует!'])
            -> addRule('empty', $forum['closed'], ['fid' => 'В данном разделе запрещено создавать темы!'])
            -> addRule('equal', [is_flood($log), true], ['msg' => 'Антифлуд! Разрешается cоздавать темы раз в '.flood_period().' сек!'])
            -> addRule('string', $title, ['title' => 'Слишком длинное или короткое название темы!'], true, 5, 50)
            -> addRule('string', $msg, ['msg' => 'Слишком длинный или короткий текст сообщения!'], true, 5, $config['forumtextlength']);

        if ($vote) {
            $validation->addRule('string', $question, ['question' => 'Слишком длинный или короткий текст вопроса!'], true, 5, 100);
            $answers = array_unique(array_diff($answers, ['']));

            foreach ($answers as $answer) {
                if (utf_strlen($answer) > 50) {
                    $validation->addError(['answer' => 'Длина вариантов ответа не должна быть более 50 символов!']);
                    break;
                }
            }

            $validation->addRule('numeric', count($answers), ['answer' => 'Необходимо от 2 до 10 варианта ответов!'], true, 2, 10);
        }

        /* TODO: Сделать проверку поиска похожей темы */

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

            // Создание голосования
            if ($vote) {

                $vote = Vote::create();
                $vote->title = $question;
                $vote->topic_id = $lastid;
                $vote->time = SITETIME;
                $vote->save();


                $prepareAnswers = [];
                foreach ($answers as $answer) {
                    $prepareAnswers[] = [
                        'vote_id' => $vote->id,
                        'answer' => $answer
                    ];
                }

                VoteAnswer::insert($prepareAnswers);
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
