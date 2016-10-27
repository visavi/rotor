<?php
App::view($config['themes'].'/index');

$start = abs(intval(Request::input('start', 0)));

show_title('Список последних комментариев');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
    case 'index':

        $total = DB::run() -> querySingle("SELECT count(*) FROM `commnews`;");

        if ($total > 0) {
            if ($total > 100) {
                $total = 100;
            }
            if ($start >= $total) {
                $start = last_page($total, $config['postnews']);
            }

            $querynews = DB::run() -> query("SELECT `commnews`.*, `news_title`, `news_comments` FROM `commnews` LEFT JOIN `news` ON `commnews`.`commnews_news_id`=`news`.`news_id` ORDER BY `commnews_time` DESC LIMIT ".$start.", ".$config['postnews'].";");

            while ($data = $querynews -> fetch()) {
                echo '<div class="b">';

                echo '<img src="/images/img/balloon.gif" alt="image" /> <b><a href="/news/allcomments/'.$data['commnews_news_id'].'/'.$data['commnews_id'].'">'.$data['news_title'].'</a></b> ('.$data['news_comments'].')</div>';

                echo '<div>'.bb_code($data['commnews_text']).'<br />';

                echo 'Написал: '.profile($data['commnews_author']).' <small>('.date_fixed($data['commnews_time']).')</small><br />';

                if (is_admin() || empty($config['anonymity'])) {
                    echo '<span class="data">('.$data['commnews_brow'].', '.$data['commnews_ip'].')</span>';
                }

                echo '</div>';
            }

            page_strnavigation('/news/allcomments?', $config['postnews'], $start, $total);
        } else {
            show_error('Комментарии не найдены!');
        }
    break;

    ############################################################################################
    ##                                     Переход к сообщение                                ##
    ############################################################################################
    case 'viewcomm':

		$id  = isset($params['id']) ? abs(intval($params['id'])) : 0;
        $nid  = isset($params['nid']) ? abs(intval($params['nid'])) : 0;

        $querycomm = DB::run() -> querySingle("SELECT COUNT(*) FROM `commnews` WHERE `commnews_id`<=? AND `commnews_news_id`=? ORDER BY `commnews_time` ASC LIMIT 1;", [$id, $nid]);

        if (!empty($querycomm)) {
            $end = floor(($querycomm - 1) / $config['postnews']) * $config['postnews'];

            redirect('/news/'.$nid.'/comments?start='.$end.'#comment_'.$id);

        } else {
            show_error('Ошибка! Комментариев к данной новости не существует!');
        }
    break;

endswitch;

echo '<i class="fa fa-arrow-circle-up"></i> <a href="/news">К новостям</a><br />';

App::view($config['themes'].'/foot');
