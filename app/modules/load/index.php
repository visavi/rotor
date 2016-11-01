<?php
App::view($config['themes'].'/index');

show_title('Загрузки');
$config['newtitle'] = 'Загрузки - Список разделов';

$querydown = DB::run() -> query("SELECT `c`.*, (SELECT SUM(`cats_count`) FROM `cats` WHERE `cats_parent`=`c`.`cats_id`) AS `subcnt`, (SELECT COUNT(*) FROM `downs` WHERE `downs_cats_id`=`cats_id` AND `downs_active`=? AND `downs_time` > ?) AS `new` FROM `cats` `c` ORDER BY `cats_order` ASC;", array(1, SITETIME-86400 * 5));

$downs = $querydown -> fetchAll();

if (count($downs) > 0) {
    $output = array();

    foreach ($downs as $row) {
        $id = $row['cats_id'];
        $fp = $row['cats_parent'];
        $output[$fp][$id] = $row;
    }

    if (is_user()) {
        echo 'Мои: <a href="/load/active?act=files">файлы</a>, <a href="/load/active?act=comments">комментарии</a> / ';
    }

    echo 'Новые: <a href="/load/new?act=files">файлы</a>, <a href="/load/new?act=comments">комментарии</a><hr />';

    $totalnew = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `downs_active`=? AND `downs_time`>?;", array (1, SITETIME-3600 * 120));

    echo '<i class="fa fa-folder-open"></i> <b><a href="/load/fresh">Свежие загрузки</a></b> ('.$totalnew.')<br />';

    foreach($output[0] as $key => $data) {
        echo '<i class="fa fa-folder-open"></i> ';
        echo '<b><a href="/load/down?cid='.$data['cats_id'].'">'.$data['cats_name'].'</a></b> ';

        $subcnt = (empty($data['subcnt'])) ? '' : '/'.$data['subcnt'];
        $new = (empty($data['new'])) ? '' : '/<span style="color:#ff0000">+'.$data['new'].'</span>';

        echo '('.$data['cats_count'] . $subcnt . $new.')<br />';
        // ---------------------- Старый вывод ------------------------------//
        /**
        * if (isset($output[$key])) {
        *
        * echo '<small><b>Подкатегории:</b> ';
        * $i = 0;
        * foreach($output[$key] as $datasub){
        * if ($i==0) {$comma = '';} else {$comma = ', ';}
        * echo $comma.'<a href="/load/down?cid='.$datasub['cats_id'].'">'.$datasub['cats_name'].'</a>';
        * ++$i;}
        * echo '</small><br />';
        * }
        */
        // ------------------------- Новый вывод ---------------------------//
        if (isset($output[$key])) {
            foreach($output[$key] as $data) {
                $subcnt = (empty($data['subcnt'])) ? '' : '/'.$data['subcnt'];
                $new = (empty($data['new'])) ? '' : '/<span style="color:#ff0000">+'.$data['new'].'</span>';

                echo '<img src="/assets/img/images/right.gif" alt="image" /> <b><a href="/load/down?cid='.$data['cats_id'].'">'.$data['cats_name'].'</a></b> ';
                echo '('.$data['cats_count'] . $subcnt . $new.')<br />';
            }
        }
        // ----------------------------------------------------//
    }

    echo '<br />';
    echo '<a href="/load/top">Топ файлов</a> / ';
    echo '<a href="/load/search">Поиск</a> / ';
    echo '<a href="/load/add">Добавить файл</a><br />';

} else {
    show_error('Разделы загрузок еще не созданы!');
}

App::view($config['themes'].'/foot');
