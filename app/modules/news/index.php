<?php
App::view($config['themes'].'/index');

$id  = isset($params['id']) ? abs(intval($params['id'])) : 0;
$start = abs(intval(Request::input('start', 0)));

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':
    show_title('Новости сайта');

    if (is_admin(array(101, 102))){
        echo '<div class="form"><a href="/admin/news">Управление новостями</a></div>';
    }

    $total = DB::run() -> querySingle("SELECT count(*) FROM `news`;");

    $page = floor(1 + $start / $config['postnews']);
    $config['description'] = 'Список новостей (Стр. '.$page.')';

    if ($total > 0) {
        if ($start >= $total) {
            $start = last_page($total, $config['postnews']);
        }

        $querynews = DB::run() -> query("SELECT * FROM `news` ORDER BY `news_time` DESC LIMIT ".$start.", ".$config['postnews'].";");

        while ($data = $querynews -> fetch()) {
            echo '<div class="b">';
            echo $data['news_closed'] == 0 ? '<i class="fa fa-plus-square-o"></i> ' : '<i class="fa fa-minus-square-o"></i> ';
            echo '<b><a href="/news/'.$data['news_id'].'">'.$data['news_title'].'</a></b><small> ('.date_fixed($data['news_time']).')</small></div>';

            if (!empty($data['news_image'])) {
                echo '<div class="img"><a href="/upload/news/'.$data['news_image'].'">'.resize_image('upload/news/', $data['news_image'], 75, array('alt' => $data['news_title'])).'</a></div>';
            }

            if(stristr($data['news_text'], '[cut]')) {
                $data['news_text'] = current(explode('[cut]', $data['news_text'])).' <a href="/news/'.$data['news_id'].'">Читать далее &raquo;</a>';
            }

            echo '<div>'.bb_code($data['news_text']).'</div>';
            echo '<div style="clear:both;">Добавлено: '.profile($data['news_author']).'<br />';
            echo '<a href="/news/'.$data['news_id'].'/comments">Комментарии</a> ('.$data['news_comments'].') ';
            echo '<a href="/news/'.$data['news_id'].'/end">&raquo;</a></div>';
        }

        page_strnavigation('/news?', $config['postnews'], $start, $total);
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

    $data = DB::run() -> queryFetch("SELECT * FROM `news` WHERE `news_id`=? LIMIT 1;", array($id));

    if (!empty($data)) {

        if (is_admin()){
            echo '<div class="form"><a href="/admin/news?act=edit&amp;id='.$id.'">Редактировать</a> / ';
            echo '<a href="/admin/news?act=del&amp;del='.$id.'&amp;token='.$_SESSION['token'].'" onclick="return confirm(\'Вы действительно хотите удалить данную новость?\')">Удалить</a></div>';
        }

        $config['newtitle'] = $data['news_title'];
        $config['description'] = strip_str($data['news_text']);

        echo '<div class="b"><i class="fa fa-file-o"></i> ';
        echo '<b>'.$data['news_title'].'</b><small> ('.date_fixed($data['news_time']).')</small></div>';

        if (!empty($data['news_image'])) {

            echo '<div class="img"><a href="/upload/news/'.$data['news_image'].'">'.resize_image('upload/news/', $data['news_image'], 75, array('alt' => $data['news_title'])).'</a></div>';
        }

        $data['news_text'] = str_replace('[cut]', '', $data['news_text']);
        echo '<div>'.bb_code($data['news_text']).'</div>';
        echo '<div style="clear:both;">Добавлено: '.profile($data['news_author']).'</div><br />';

        if ($data['news_comments'] > 0) {
            echo '<div class="act"><i class="fa fa-comment"></i> <b>Последние комментарии</b></div>';

            $querycomm = DB::run() -> query("SELECT * FROM `commnews` WHERE `commnews_news_id`=? ORDER BY `commnews_time` DESC LIMIT 5;", array($id));
            $comments = $querycomm -> fetchAll();
            $comments = array_reverse($comments);

            foreach ($comments as $comm) {
                echo '<div class="b">';
                echo '<div class="img">'.user_avatars($comm['commnews_author']).'</div>';

                echo '<b>'.profile($comm['commnews_author']).'</b>';
                echo '<small> ('.date_fixed($comm['commnews_time']).')</small><br />';
                echo user_title($comm['commnews_author']).' '.user_online($comm['commnews_author']).'</div>';

                echo '<div>'.bb_code($comm['commnews_text']).'<br />';

                if (is_admin() || empty($config['anonymity'])) {
                    echo '<span class="data">('.$comm['commnews_brow'].', '.$comm['commnews_ip'].')</span>';
                }

                echo '</div>';
            }

            if ($data['news_comments'] > 5) {
                echo '<div class="act"><b><a href="/news/'.$data['news_id'].'/comments">Все комментарии</a></b> ('.$data['news_comments'].') ';
                echo '<a href="/news/'.$data['news_id'].'/end">&raquo;</a></div><br />';
            }
        }

        if (empty($data['news_closed'])) {

            if (empty($data['news_comments'])){
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
    $datanews = DB::run() -> queryFetch("SELECT * FROM `news` WHERE `news_id`=? LIMIT 1;", array($id));

    if (!empty($datanews)) {
        $config['newtitle'] = 'Комментарии - '.$datanews['news_title'];

        $page = floor(1 + $start / $config['postnews']);
        $config['description'] = 'Комментарии - '.$datanews['news_title'].' (Стр. '.$page.')';

        echo '<h1><a href="/news/'.$datanews['news_id'].'">'.$datanews['news_title'].'</a></h1>';

        $total = DB::run() -> querySingle("SELECT count(*) FROM `commnews` WHERE `commnews_news_id`=?;", array($id));

        if ($total > 0) {
            if ($start >= $total) {
                $start = last_page($total, $config['postnews']);
            }

            $is_admin = is_admin();
            if ($is_admin) {
                echo '<form action="/news/'.$id.'/delete?start='.$start.'" method="post">';
                echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
            }

            $querycomm = DB::run() -> query("SELECT * FROM `commnews` WHERE `commnews_news_id`=? ORDER BY `commnews_time` ASC LIMIT ".$start.", ".$config['postnews'].";", array($id));

            while ($data = $querycomm -> fetch()) {

                echo '<div class="b" id="comment_'.$data['commnews_id'].'"">';
                echo '<div class="img">'.user_avatars($data['commnews_author']).'</div>';

                if ($is_admin) {
                    echo '<span class="imgright"><input type="checkbox" name="del[]" value="'.$data['commnews_id'].'" /></span>';
                }

                echo '<b>'.profile($data['commnews_author']).'</b>';
                echo '<small> ('.date_fixed($data['commnews_time']).')</small><br />';
                echo user_title($data['commnews_author']).' '.user_online($data['commnews_author']).'</div>';

                echo '<div>'.bb_code($data['commnews_text']).'<br />';

                if (is_admin() || empty($config['anonymity'])) {
                    echo '<span class="data">('.$data['commnews_brow'].', '.$data['commnews_ip'].')</span>';
                }

                echo '</div>';
            }

            if ($is_admin) {
                echo '<span class="imgright"><input type="submit" value="Удалить выбранное" /></span></form>';
            }

            page_strnavigation('/news/'.$id.'/comments?', $config['postnews'], $start, $total);
        }

        if (empty($datanews['news_closed'])) {

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

    if (is_user()) {

        $data = DB::run() -> queryFetch("SELECT * FROM `news` WHERE `news_id`=? LIMIT 1;", array($id));

        $validation = new Validation();

        $validation -> addRule('equal', array($token, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
            -> addRule('equal', array(is_flood($log), true), 'Антифлуд! Разрешается комментировать раз в '.flood_period().' сек!')
            -> addRule('not_empty', $data, 'Выбранной новости не существует, возможно она было удалена!')
            -> addRule('string', $msg, 'Слишком длинный или короткий комментарий!', true, 5, 1000)
            -> addRule('empty', $data['news_closed'], 'Комментирование данной новости запрещено!');

        if ($validation->run()) {

            $msg = antimat($msg);

            DB::run() -> query("INSERT INTO `commnews` (`commnews_news_id`, `commnews_text`, `commnews_author`, `commnews_time`, `commnews_ip`, `commnews_brow`) VALUES (?, ?, ?, ?, ?, ?);", array($id, $msg, $log, SITETIME, App::getClientIp(), App::getUserAgent()));

            DB::run() -> query("DELETE FROM `commnews` WHERE `commnews_news_id`=? AND `commnews_time` < (SELECT MIN(`commnews_time`) FROM (SELECT `commnews_time` FROM `commnews` WHERE `commnews_news_id`=? ORDER BY `commnews_time` DESC LIMIT ".$config['maxkommnews'].") AS del);", array($id, $id));

            DB::run() -> query("UPDATE `news` SET `news_comments`=`news_comments`+1 WHERE `news_id`=?;", array($id));
            DB::run() -> query("UPDATE `users` SET `users_allcomments`=`users_allcomments`+1, `users_point`=`users_point`+1, `users_money`=`users_money`+5 WHERE `users_login`=?", array($log));

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

    echo '<i class="fa fa-arrow-circle-up"></i> <a href="/news/'.$id.'/comments?start='.$start.'">Вернуться</a><br />';
    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/news">К новостям</a><br />';
break;

############################################################################################
##                                 Удаление комментариев                                  ##
############################################################################################
case 'delete':

    $token = check(Request::input('token'));
    $del   = intar(Request::input('del'));

    if (is_admin()) {
        if ($token == $_SESSION['token']) {
            if (!empty($del)) {

                $del = implode(',', $del);

                $delcomments = DB::run() -> exec("DELETE FROM `commnews` WHERE `commnews_id` IN (".$del.") AND `commnews_news_id`=".$id.";");
                DB::run() -> query("UPDATE `news` SET `news_comments`=`news_comments`-? WHERE `news_id`=?;", array($delcomments, $id));

                notice('Выбранные комментарии успешно удалены!');
                redirect('/news/'.$id.'/comments?start='.$start);

            } else {
                show_error('Ошибка! Отстутствуют выбранные комментарии для удаления!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_error('Ошибка! Удалять комментарии могут только модераторы!');
    }

    echo '<i class="fa fa-arrow-circle-up"></i> <a href="/news/'.$id.'/comments?start='.$start.'">Вернуться</a><br />';
    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/news">К новостям</a><br />';
break;

############################################################################################
##                             Переадресация на последнюю страницу                        ##
############################################################################################
case 'end':

    $query = DB::run() -> queryFetch("SELECT count(*) as `total_comments` FROM `commnews` WHERE `commnews_news_id`=? LIMIT 1;", array($id));

    if (!empty($query)) {
        $total_comments = (empty($query['total_comments'])) ? 1 : $query['total_comments'];
        $end = last_page($total_comments, $config['postnews']);

        redirect('/news/'.$id.'/comments?start='.$end);

    } else {
        show_error('Ошибка! Данной новости не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/news">К новостям</a><br />';
break;

endswitch;

App::view($config['themes'].'/foot');
