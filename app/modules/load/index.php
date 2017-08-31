<?php
view(setting('themes').'/index');

//show_title('Загрузки');
//setting('newtitle') = 'Загрузки - Список разделов';

$querydown = DB::run() -> query("SELECT `c`.*, (SELECT SUM(`count`) FROM `cats` WHERE `parent`=`c`.`id`) AS `subcnt`, (SELECT COUNT(*) FROM `downs` WHERE `category_id`=`id` AND `active`=? AND `time` > ?) AS `new` FROM `cats` `c` ORDER BY sort ASC;", [1, SITETIME-86400 * 5]);

$downs = $querydown -> fetchAll();

if (count($downs) > 0) {
    $output = [];

    foreach ($downs as $row) {
        $id = $row['id'];
        $fp = $row['parent'];
        $output[$fp][$id] = $row;
    }

    if (is_user()) {
        echo 'Мои: <a href="/load/active?act=files">файлы</a>, <a href="/load/active?act=comments">комментарии</a> / ';
    }

    echo 'Новые: <a href="/load/new?act=files">файлы</a>, <a href="/load/new?act=comments">комментарии</a><hr>';

    $totalnew = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `active`=? AND `time`>?;", [1, SITETIME-3600 * 120]);

    echo '<i class="fa fa-folder-open"></i> <b><a href="/load/fresh">Свежие загрузки</a></b> ('.$totalnew.')<br>';

    foreach($output[0] as $key => $data) {
        echo '<i class="fa fa-folder-open"></i> ';
        echo '<b><a href="/load/down?cid='.$data['id'].'">'.$data['name'].'</a></b> ';

        $subcnt = (empty($data['subcnt'])) ? '' : '/'.$data['subcnt'];
        $new = (empty($data['new'])) ? '' : '/<span style="color:#ff0000">+'.$data['new'].'</span>';

        echo '('.$data['count'] . $subcnt . $new.')<br>';
        // ---------------------- Старый вывод ------------------------------//
        /**
        * if (isset($output[$key])) {
        *
        * echo '<small><b>Подкатегории:</b> ';
        * $i = 0;
        * foreach($output[$key] as $datasub){
        * if ($i==0) {$comma = '';} else {$comma = ', ';}
        * echo $comma.'<a href="/load/down?cid='.$datasub['id'].'">'.$datasub['name'].'</a>';
        * ++$i;}
        * echo '</small><br>';
        * }
        */
        // ------------------------- Новый вывод ---------------------------//
        if (isset($output[$key])) {
            foreach($output[$key] as $odata) {
                $subcnt = (empty($odata['subcnt'])) ? '' : '/'.$odata['subcnt'];
                $new = (empty($odata['new'])) ? '' : '/<span style="color:#ff0000">+'.$odata['new'].'</span>';

                echo '<i class="fa fa-angle-right"></i> <b><a href="/load/down?cid='.$odata['id'].'">'.$odata['name'].'</a></b> ';
                echo '('.$odata['count'] . $subcnt . $new.')<br>';
            }
        }
        // ----------------------------------------------------//
    }

    echo '<br>';
    echo '<a href="/load/top">Топ файлов</a> / ';
    echo '<a href="/load/search">Поиск</a> / ';
    echo '<a href="/load/add">Добавить файл</a><br>';

} else {
    showError('Разделы загрузок еще не созданы!');
}

view(setting('themes').'/foot');
