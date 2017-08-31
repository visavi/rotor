<?php
view(setting('themes').'/index');

$act = isset($_GET['act']) ? check($_GET['act']) : 'files';
$page = abs(intval(Request::input('page', 1)));

switch ($action):
############################################################################################
##                                        Вывод тем                                       ##
############################################################################################
case 'files':
    //show_title('Список новых файлов');

    $total = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `active`=?;", [1]);
    if ($total > 100) {
        $total = 100;
    }
    $page = paginate(setting('downlist'), $total);

    if ($total > 0) {
        $querydown = DB::run() -> query("SELECT `downs`.*, `name`, folder FROM `downs` LEFT JOIN `cats` ON `downs`.`category_id`=`cats`.`id` WHERE `active`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".setting('downlist').";", [1]);

        while ($data = $querydown -> fetch()) {
            $folder = $data['folder'] ? $data['folder'].'/' : '';

            $filesize = (!empty($data['link'])) ? formatFileSize(HOME.'/uploads/files/'.$folder.$data['link']) : 0;

            echo '<div class="b"><i class="fa fa-file-o"></i> ';
            echo '<b><a href="/load/down?act=view&amp;id='.$data['id'].'">'.$data['title'].'</a></b> ('.$filesize.')</div>';

            echo '<div>Категория: <a href="/load/down?cid='.$data['category_id'].'">'.$data['name'].'</a><br>';
            echo 'Скачиваний: '.$data['loads'].'<br>';
            echo 'Добавил: '.profile($data['user']).' ('.dateFixed($data['time']).')</div>';
        }

        pagination($page);
    } else {
        showError('Опубликованных файлов еще нет!');
    }
break;

############################################################################################
##                                     Вывод сообщений                                    ##
############################################################################################
case 'comments':
    //show_title('Список последних комментариев');

    $total = DB::run() -> querySingle("SELECT count(*) FROM `comments` WHERE relate_type=?;", ['down']);

    if ($total > 100) {
        $total = 100;
    }

    $page = paginate(setting('downlist'), $total);

    if ($total > 0) {
        $querydown = DB::run() -> query("SELECT `comments`.*, `title`, `comments` FROM `comments` LEFT JOIN `downs` ON `comments`.`relate_id`=`downs`.`id` WHERE relate_type='down' ORDER BY comments.`time` DESC LIMIT ".$page['offset'].", ".setting('downlist').";");

        while ($data = $querydown -> fetch()) {
            echo '<div class="b">';

            echo '<i class="fa fa-comment"></i> <b><a href="/load/new?act=viewcomm&amp;id='.$data['relate_id'].'&amp;cid='.$data['id'].'">'.$data['title'].'</a></b> ('.$data['comments'].')</div>';

            echo '<div>'.bbCode($data['text']).'<br>';

            echo 'Написал: '.profile($data['user']).' <small>('.dateFixed($data['time']).')</small><br>';

            if (is_admin()) {
                echo '<span class="data">('.$data['brow'].', '.$data['ip'].')</span>';
            }

            echo '</div>';
        }

        pagination($page);
    } else {
        showError('Комментарии не найдены!');
    }
break;

############################################################################################
##                                     Переход к сообщение                                ##
############################################################################################
case 'viewcomm':

    if (isset($_GET['id'])) {
        $id = abs(intval($_GET['id']));
    } else {
        $id = 0;
    }
    if (isset($_GET['cid'])) {
        $cid = abs(intval($_GET['cid']));
    } else {
        $cid = 0;
    }

    $querycomm = DB::run() -> querySingle("SELECT COUNT(*) FROM `comments` WHERE relate_type=? AND `id`<=? AND `relate_id`=? ORDER BY `time` ASC LIMIT 1;", ['down', $cid, $id]);

    if (!empty($querycomm)) {
        $end = ceil($querycomm / setting('downlist'));

        redirect("/load/down?act=comments&id=$id&page=$end");
    } else {
        showError('Ошибка! Комментарий к данному файлу не существует!');
    }
break;

endswitch;

echo '<i class="fa fa-arrow-circle-up"></i> <a href="/load">Категории</a><br>';

view(setting('themes').'/foot');
