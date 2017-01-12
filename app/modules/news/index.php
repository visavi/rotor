<?php
App::view($config['themes'].'/index');

$id = param('id');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':
    show_title('Новости сайта');

    if (is_admin([101, 102])){
        echo '<div class="form"><a href="/admin/news">Управление новостями</a></div>';
    }

    $total = DB::run() -> querySingle("SELECT count(*) FROM `news`;");
    $page = App::paginate(App::setting('postnews'), $total);

    $config['description'] = 'Список новостей (Стр. '.$page['current'].')';

    if ($total > 0) {
        $querynews = DB::run() -> query("SELECT * FROM `news` ORDER BY `time` DESC LIMIT ".$page['offset'].", ".$config['postnews'].";");

        while ($data = $querynews -> fetch()) {
            echo '<div class="b">';
            echo $data['closed'] == 0 ? '<i class="fa fa-plus-square-o"></i> ' : '<i class="fa fa-minus-square-o"></i> ';
            echo '<b><a href="/news/'.$data['id'].'">'.$data['title'].'</a></b><small> ('.date_fixed($data['time']).')</small></div>';

            if (!empty($data['image'])) {
                echo '<div class="img"><a href="/uploads/news/'.$data['image'].'">'.resize_image('uploads/news/', $data['image'], 75, ['alt' => $data['title']]).'</a></div>';
            }

            if(stristr($data['text'], '[cut]')) {
                $data['text'] = current(explode('[cut]', $data['text'])).' <a href="/news/'.$data['id'].'">Читать далее &raquo;</a>';
            }

            echo '<div>'.App::bbCode($data['text']).'</div>';
            echo '<div style="clear:both;">Добавлено: '.profile($data['author']).'<br />';
            echo '<a href="/news/'.$data['id'].'/comments">Комментарии</a> ('.$data['comments'].') ';
            echo '<a href="/news/'.$data['id'].'/end">&raquo;</a></div>';
        }

        App::pagination($page);
    } else {
        show_error('Новостей еще нет!');
    }

    echo '<i class="fa fa-rss"></i> <a href="/news/rss">RSS подписка</a><br />';
    echo '<i class="fa fa-comment"></i> <a href="/news/allcomments">Комментарии</a><br />';
break;

############################################################################################
##                                     Чтение новости                                     ##
############################################################################################
case 'view':

    $data = DB::run() -> queryFetch("SELECT * FROM `news` WHERE `id`=? LIMIT 1;", [$id]);

    if (!empty($data)) {

        if (is_admin()){
            echo '<div class="form"><a href="/admin/news?act=edit&amp;id='.$id.'">Редактировать</a> / ';
            echo '<a href="/admin/news?act=del&amp;del='.$id.'&amp;token='.$_SESSION['token'].'" onclick="return confirm(\'Вы действительно хотите удалить данную новость?\')">Удалить</a></div>';
        }

        $config['newtitle'] = $data['title'];
        $config['description'] = strip_str($data['text']);

        echo '<div class="b"><i class="fa fa-file-o"></i> ';
        echo '<b>'.$data['title'].'</b><small> ('.date_fixed($data['time']).')</small></div>';

        if (!empty($data['image'])) {

            echo '<div class="img"><a href="/uploads/news/'.$data['image'].'">'.resize_image('uploads/news/', $data['image'], 75, ['alt' => $data['title']]).'</a></div>';
        }

        $data['text'] = str_replace('[cut]', '', $data['text']);
        echo '<div>'.App::bbCode($data['text']).'</div>';
        echo '<div style="clear:both;">Добавлено: '.profile($data['author']).'</div><br />';

        if ($data['comments'] > 0) {
            echo '<div class="act"><i class="fa fa-comment"></i> <b>Последние комментарии</b></div>';

            $querycomm = DB::run() -> query("SELECT * FROM `comments` WHERE relate_type=? AND `relate_id`=? ORDER BY `time` DESC LIMIT 5;", ['news', $id]);
            $comments = $querycomm -> fetchAll();
            $comments = array_reverse($comments);

            foreach ($comments as $comm) {
                echo '<div class="b">';
                echo '<div class="img">'.user_avatars($comm['user']).'</div>';

                echo '<b>'.profile($comm['user']).'</b>';
                echo '<small> ('.date_fixed($comm['time']).')</small><br />';
                echo user_title($comm['user']).' '.user_online($comm['user']).'</div>';

                echo '<div>'.App::bbCode($comm['text']).'<br />';

                if (is_admin() || empty($config['anonymity'])) {
                    echo '<span class="data">('.$comm['brow'].', '.$comm['ip'].')</span>';
                }

                echo '</div>';
            }

            if ($data['comments'] > 5) {
                echo '<div class="act"><b><a href="/news/'.$data['id'].'/comments">Все комментарии</a></b> ('.$data['comments'].') ';
                echo '<a href="/news/'.$data['id'].'/end">&raquo;</a></div><br />';
            }
        }

        if (empty($data['closed'])) {

            if (empty($data['comments'])){
                show_error('Комментариев еще нет!');
            }

            if (is_user()) {
                echo '<div class="form"><form action="/news/'.$id.'/create?read=1" method="post">';
                echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
                echo '<textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br />';
                echo '<input type="submit" value="Написать" /></form></div>';

                echo '<br />';
                echo '<a href="/rules">Правила</a> / ';
                echo '<a href="/smiles">Смайлы</a> / ';
                echo '<a href="/tags">Теги</a><br /><br />';
            } else {
                show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
            }
        } else {
            show_error('Комментирование данной новости закрыто!');
        }
    } else {
        show_error('Ошибка! Выбранная вами новость не существует, возможно она была удалена!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/news">К новостям</a><br />';
break;

############################################################################################
##                                     Комментарии                                        ##
############################################################################################
case 'comments':
    $datanews = DB::run() -> queryFetch("SELECT * FROM `news` WHERE `id`=? LIMIT 1;", [$id]);

    if (!empty($datanews)) {

        $total = DB::run() -> querySingle("SELECT count(*) FROM `comments` WHERE relate_type=? AND `relate_id`=?;", ['news', $id]);
        $page = App::paginate(App::setting('postnews'), $total);

        $config['newtitle'] = 'Комментарии - '.$datanews['title'];
        $config['description'] = 'Комментарии - '.$datanews['title'].' (Стр. '.$page['current'].')';

        echo '<h1><a href="/news/'.$datanews['id'].'">'.$datanews['title'].'</a></h1>';

        if ($total > 0) {

            $is_admin = is_admin();
            if ($is_admin) {
                echo '<form action="/news/'.$id.'/delete?page='.$page['current'].'" method="post">';
                echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
            }

            $querycomm = DB::run() -> query("SELECT * FROM `comments` WHERE relate_type=? AND `relate_id`=? ORDER BY `time` ASC LIMIT ".$page['offset'].", ".$config['postnews'].";", ['news', $id]);

            while ($data = $querycomm -> fetch()) {

                echo '<div class="b" id="comment_'.$data['id'].'"">';
                echo '<div class="img">'.user_avatars($data['user']).'</div>';

                if ($is_admin) {
                    echo '<span class="imgright"><input type="checkbox" name="del[]" value="'.$data['id'].'" /></span>';
                }

                echo '<b>'.profile($data['user']).'</b>';
                echo '<small> ('.date_fixed($data['time']).')</small><br />';
                echo user_title($data['user']).' '.user_online($data['user']).'</div>';

                echo '<div>'.App::bbCode($data['text']).'<br />';

                if (is_admin() || empty($config['anonymity'])) {
                    echo '<span class="data">('.$data['brow'].', '.$data['ip'].')</span>';
                }

                echo '</div>';
            }

            if ($is_admin) {
                echo '<span class="imgright"><input type="submit" value="Удалить выбранное" /></span></form>';
            }

            App::pagination($page);
        }

        if (empty($datanews['closed'])) {

            if (!$total) {
                show_error('Комментариев еще нет!');
            }

            if (is_user()) {
                echo '<div class="form"><form action="/news/'.$id.'/create" method="post">';
                echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
                echo '<textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br />';
                echo '<input type="submit" value="Написать" /></form></div>';

                echo '<br />';
                echo '<a href="/rules">Правила</a> / ';
                echo '<a href="/smiles">Смайлы</a> / ';
                echo '<a href="/tags">Теги</a><br /><br />';
            } else {
                show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
            }
        } else {
            show_error('Комментирование данной новости закрыто!');
        }
    } else {
        show_error('Ошибка! Выбранная новость не существует, возможно она была удалена!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/news">К новостям</a><br />';
break;

############################################################################################
##                                Добавление комментариев                                 ##
############################################################################################
case 'create':

    $msg   = check(Request::input('msg'));
    $token = check(Request::input('token'));
    $page  = abs(intval(Request::input('page', 1)));

    if (is_user()) {

        $data = DB::run() -> queryFetch("SELECT * FROM `news` WHERE `id`=? LIMIT 1;", [$id]);

        $validation = new Validation();

        $validation -> addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            -> addRule('equal', [is_flood($log), true], 'Антифлуд! Разрешается комментировать раз в '.flood_period().' сек!')
            -> addRule('not_empty', $data, 'Выбранной новости не существует, возможно она было удалена!')
            -> addRule('string', $msg, 'Слишком длинный или короткий комментарий!', true, 5, 1000)
            -> addRule('empty', $data['closed'], 'Комментирование данной новости запрещено!');

        if ($validation->run()) {

            $msg = antimat($msg);

            DB::run() -> query("INSERT INTO `comments` (relate_type, relate_category_id, `relate_id`, `text`, `user`, `time`, `ip`, `brow`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", ['news', 0, $id, $msg, $log, SITETIME, App::getClientIp(), App::getUserAgent()]);

            DB::run() -> query("DELETE FROM `comments` WHERE relate_type=? AND `relate_id`=? AND `time` < (SELECT MIN(`time`) FROM (SELECT `time` FROM `comments` WHERE relate_type=? AND `relate_id`=? ORDER BY `time` DESC LIMIT ".$config['maxkommnews'].") AS del);", ['news', $id, 'news', $id]);

            DB::run() -> query("UPDATE `news` SET `comments`=`comments`+1 WHERE `id`=?;", [$id]);
            DB::run() -> query("UPDATE `users` SET `allcomments`=`allcomments`+1, `point`=`point`+1, `money`=`money`+5 WHERE `login`=?", [$log]);

            notice('Комментарий успешно добавлен!');

            if (isset($_GET['read'])) {
                redirect('/news/'.$id);
            }

            redirect('/news/'.$id.'/end');

        } else {
            show_error($validation->getErrors());
        }
    } else {
        show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-up"></i> <a href="/news/'.$id.'/comments?page='.$page.'">Вернуться</a><br />';
    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/news">К новостям</a><br />';
break;

############################################################################################
##                                 Удаление комментариев                                  ##
############################################################################################
case 'delete':

    $token = check(Request::input('token'));
    $del   = intar(Request::input('del'));
    $page  = abs(intval(Request::input('page', 1)));

    if (is_admin()) {
        if ($token == $_SESSION['token']) {
            if (!empty($del)) {

                $del = implode(',', $del);

                $delcomments = DB::run() -> exec("DELETE FROM `comments` WHERE relate_type='news' AND `relate_id`=".$id." AND `id` IN (".$del.");");

                DB::run() -> query("UPDATE `news` SET `comments`=`comments`-? WHERE `id`=?;", [$delcomments, $id]);

                notice('Выбранные комментарии успешно удалены!');
                redirect('/news/'.$id.'/comments?page='.$page);

            } else {
                show_error('Ошибка! Отстутствуют выбранные комментарии для удаления!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_error('Ошибка! Удалять комментарии могут только модераторы!');
    }

    echo '<i class="fa fa-arrow-circle-up"></i> <a href="/news/'.$id.'/comments?page='.$page.'">Вернуться</a><br />';
    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/news">К новостям</a><br />';
break;

############################################################################################
##                             Переадресация на последнюю страницу                        ##
############################################################################################
case 'end':

    $query = DB::run() -> queryFetch("SELECT count(*) as `total_comments` FROM `comments` WHERE relate_type=? AND `relate_id`=? LIMIT 1;", ['news', $id]);

    if (!empty($query)) {
        $total_comments = (empty($query['total_comments'])) ? 1 : $query['total_comments'];
        $end = ceil($total_comments / $config['postnews']);

        redirect('/news/'.$id.'/comments?page='.$end);

    } else {
        show_error('Ошибка! Данной новости не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/news">К новостям</a><br />';
break;

endswitch;

App::view($config['themes'].'/foot');
