<?php
App::view($config['themes'].'/index');

show_title('Список последних комментариев');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
    case 'index':

        $total = DB::run() -> querySingle("SELECT count(*) FROM `comments` WHERE relate_type=?;", ['news']);

        if ($total > 0) {
            if ($total > 100) {
                $total = 100;
            }
            $page = App::paginate(App::setting('postnews'), $total);

            $querynews = DB::run() -> query("SELECT `comments`.*, `title`, `comments` FROM `comments` LEFT JOIN `news` ON `comments`.`relate_id`=`news`.`id` WHERE relate_type='news' ORDER BY comments.`time` DESC LIMIT ".$page['offset'].", ".$config['postnews'].";");

            while ($data = $querynews -> fetch()) {
                echo '<div class="b">';

                echo '<i class="fa fa-comment"></i> <b><a href="/news/allcomments/'.$data['relate_id'].'/'.$data['id'].'">'.$data['title'].'</a></b> ('.$data['comments'].')</div>';

                echo '<div>'.App::bbCode($data['text']).'<br />';

                echo 'Написал: '.profile($data['user']).' <small>('.date_fixed($data['time']).')</small><br />';

                if (is_admin() || empty($config['anonymity'])) {
                    echo '<span class="data">('.$data['brow'].', '.$data['ip'].')</span>';
                }

                echo '</div>';
            }

            App::pagination($page);
        } else {
            show_error('Комментарии не найдены!');
        }
    break;

    ############################################################################################
    ##                                     Переход к сообщение                                ##
    ############################################################################################
    case 'viewcomm':

        $id  = param('id');
        $nid = param('nid');

        $querycomm = DB::run() -> querySingle("SELECT COUNT(*) FROM `comments` WHERE relate_type=? AND `relate_id`=? AND `id`<=? ORDER BY `time` ASC LIMIT 1;", ['news', $nid, $id]);
        if (!empty($querycomm)) {

            $end = ceil($querycomm / $config['postnews']);

            redirect('/news/'.$nid.'/comments?page='.$end.'#comment_'.$id);

        } else {
            show_error('Ошибка! Комментариев к данной новости не существует!');
        }
    break;

endswitch;

echo '<i class="fa fa-arrow-circle-up"></i> <a href="/news">К новостям</a><br />';

App::view($config['themes'].'/foot');
