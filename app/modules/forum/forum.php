<?php

$fid = param('fid');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    $forum = Forum::with('parent')->find($fid);

    if (!$forum) {
        App::abort('default', 'Данного раздела не существует!');
    }

    $forum->children = Forum::where('parent_id', $forum->id)
        ->with('lastTopic.lastPost.user')
        ->get();

    $total = Topic::where('forum_id', $fid)->count();

    $page = App::paginate(Setting::get('forumtem'), $total);

    $topics = Topic::where('forum_id', $fid)
        ->orderBy('locked', 'desc')
        ->orderBy('updated_at', 'desc')
        ->limit(Setting::get('forumtem'))
        ->offset($page['offset'])
        ->with('lastPost.user')
        ->get();

    App::view('forum/forum', compact('forum', 'topics', 'page'));
break;

############################################################################################
##                               Подготовка к созданию темы                               ##
############################################################################################
case 'create':

    //Setting::get('newtitle') = 'Создание новой темы';

    $fid = abs(intval(Request::input('fid')));

    if (! is_user()) App::abort(403);

    $forums = Forum::where('parent_id', 0)
        ->with('children')
        ->orderBy('sort')
        ->get();

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

        $forum = Forum::find($fid);

        $validation = new Validation();
        $validation -> addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            -> addRule('not_empty', $forum, ['fid' => 'Раздела для новой темы не существует!'])
            -> addRule('empty', $forum['closed'], ['fid' => 'В данном разделе запрещено создавать темы!'])
            -> addRule('equal', [is_flood(App::getUsername()), true], ['msg' => 'Антифлуд! Разрешается cоздавать темы раз в '.flood_period().' сек!'])
            -> addRule('string', $title, ['title' => 'Слишком длинное или короткое название темы!'], true, 5, 50)
            -> addRule('string', $msg, ['msg' => 'Слишком длинный или короткий текст сообщения!'], true, 5, Setting::get('forumtextlength'));

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

            DB::run() -> query("UPDATE `users` SET `allforum`=`allforum`+1, `point`=`point`+1, `money`=`money`+5 WHERE `login`=?", [App::getUsername()]);

            DB::run() -> query("INSERT INTO `topics` (`forum_id`, `title`, `user_id`, `posts`, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?);", [$fid, $title, App::getUserId(), 1, SITETIME, SITETIME]);

            $lastid = DB::run() -> lastInsertId();

            DB::run() -> query("INSERT INTO `posts` (`topic_id`, `user_id`, `text`, `created_at`, `ip`, `brow`) VALUES (?, ?, ?, ?, ?, ?);", [$lastid, App::getUserId(), $msg, SITETIME, App::getClientIp(), App::getUserAgent()]);
            $lastPostId = DB::run() -> lastInsertId();

            Topic::where('id', $lastid)->update(['last_post_id' => $lastPostId]);

            DB::run() -> query("UPDATE `forums` SET `topics`=`topics`+1, `posts`=`posts`+1, `last_topic_id`=? WHERE `id`=?", [$lastid, $fid]);
            // Обновление родительского форума
            if ($forum->parent) {
                DB::run() -> query("UPDATE `forums` SET `last_topic_id`=? WHERE `id`=?", [$lastid, $forum->parent->id]);
            }

            // Создание голосования
            if ($vote) {

                $vote = new Vote();
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

    App::view('forum/forum_create', compact('forums', 'fid'));
    break;

endswitch;
