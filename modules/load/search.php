<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

show_title('Поиск в файлах');

if (is_user()) {
switch ($act):
############################################################################################
##                                    Главная поиска                                      ##
############################################################################################
case "index":

    $config['newtitle'] = 'Поиск в файлах';

    echo '<div class="form"><form action="/load/search?act=search" method="get">';
    echo '<input type="hidden" name="act" value="search" />';

    echo 'Запрос:<br />';
    echo '<input type="text" name="find" /><br />';

    echo 'Искать:<br />';
    echo '<input name="where" type="radio" value="0" checked="checked" /> В названии<br />';
    echo '<input name="where" type="radio" value="1" /> В описании<br /><br />';

    echo 'Тип запроса:<br />';
    echo '<input name="type" type="radio" value="0" checked="checked" /> И<br />';
    echo '<input name="type" type="radio" value="1" /> Или<br />';
    echo '<input name="type" type="radio" value="2" /> Полный<br /><br />';

    echo '<input type="submit" value="Поиск" /></form></div><br />';

break;

############################################################################################
##                                          Поиск                                         ##
############################################################################################
case "search":

    $find = check(strval($_GET['find']));
    $type = abs(intval($_GET['type']));
    $where = abs(intval($_GET['where']));

    if (!is_utf($find)){
        $find = win_to_utf($find);
    }

    if (utf_strlen($find) >= 3 && utf_strlen($find) <= 50) {

        $findmewords = explode(" ", utf_lower($find));

        $arrfind = array();
        foreach ($findmewords as $val) {
            if (utf_strlen($val) >= 3) {
                $arrfind[] = (empty($type)) ? '+'.$val.'*' : $val.'*';
            }
        }

        $findme = implode(" ", $arrfind);

        if ($type == 2 && count($findmewords) > 1) {
            $findme = "\"$find\"";
        }

        $config['newtitle'] = $find.' - Результаты поиска';

        $wheres = (empty($where)) ? 'title' : 'text';

        $loadfind = ($type.$wheres.$find);

        // ----------------------------- Поиск в названии -------------------------------//
        if ($wheres == 'title') {
            echo 'Поиск запроса <b>&quot;'.$find.'&quot;</b> в названии<br />';

            if (empty($_SESSION['loadfindres']) || $loadfind!=$_SESSION['loadfind']) {

                $querysearch = DB::run() -> query("SELECT `downs_id` FROM `downs` WHERE `downs_active`=? AND MATCH (`downs_title`) AGAINST ('".$findme."' IN BOOLEAN MODE) LIMIT 100;", array(1));
                $result = $querysearch -> fetchAll(PDO::FETCH_COLUMN);

                $_SESSION['loadfind'] = $loadfind;
                $_SESSION['loadfindres'] = $result;
            }

            $total = count($_SESSION['loadfindres']);

            if ($total > 0) {
                if ($start >= $total) {
                    $start = 0;
                }

                echo 'Найдено совпадений: <b>'.$total.'</b><br /><br />';

                $result = implode(',', $_SESSION['loadfindres']);

                $querydown = DB::run() -> query("SELECT `downs`.*, `cats_name`, folder FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_id` IN (".$result.") ORDER BY `downs_time` DESC LIMIT ".$start.", ".$config['downlist'].";");

                while ($data = $querydown -> fetch()) {
                    $folder = $data['folder'] ? $data['folder'].'/' : '';

                    $filesize = (!empty($data['downs_link'])) ? read_file(BASEDIR.'/upload/files/'.$folder.$data['downs_link']) : 0;

                    echo '<div class="b"><img src="/images/img/zip.gif" alt="image" /> ';
                    echo '<b><a href="/load/down?act=view&amp;id='.$data['downs_id'].'">'.$data['downs_title'].'</a></b> ('.$filesize.')</div>';

                    echo '<div>Категория: <a href="/load/down?cid='.$data['downs_cats_id'].'">'.$data['cats_name'].'</a><br />';
                    echo 'Скачиваний: '.$data['downs_load'].'<br />';
                    echo 'Добавил: '.profile($data['downs_user']).' ('.date_fixed($data['downs_time']).')</div>';
                }

                page_strnavigation('/load/search?act=search&amp;find='.urlencode($find).'&amp;type='.$type.'&amp;where='.$where.'&amp;', $config['downlist'], $start, $total);
            } else {
                show_error('По вашему запросу ничего не найдено!');
            }
        }
        // --------------------------- Поиск в описании -------------------------------//
        if ($wheres == 'text') {
            echo 'Поиск запроса <b>&quot;'.$find.'&quot;</b> в описании<br />';

            if (empty($_SESSION['loadfindres']) || $loadfind!=$_SESSION['loadfind']) {

                $querysearch = DB::run() -> query("SELECT `downs_id` FROM `downs` WHERE `downs_active`=? AND MATCH (`downs_text`) AGAINST ('".$findme."' IN BOOLEAN MODE) LIMIT 100;", array(1));
                $result = $querysearch -> fetchAll(PDO::FETCH_COLUMN);

                $_SESSION['loadfind'] = $loadfind;
                $_SESSION['loadfindres'] = $result;
            }

            $total = count($_SESSION['loadfindres']);

            if ($total > 0) {
                if ($start >= $total) {
                    $start = 0;
                }

                echo 'Найдено совпадений: <b>'.$total.'</b><br /><br />';

                $result = implode(',', $_SESSION['loadfindres']);

                $querydown = DB::run() -> query("SELECT `downs`.*, `cats_name`, folder FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_id` IN (".$result.") ORDER BY `downs_time` DESC LIMIT ".$start.", ".$config['downlist'].";");

                while ($data = $querydown -> fetch()) {
                    $folder = $data['folder'] ? $data['folder'].'/' : '';

                    $filesize = (!empty($data['downs_link'])) ? read_file(BASEDIR.'/upload/files/'.$folder.$data['downs_link']) : 0;

                    echo '<div class="b"><img src="/images/img/zip.gif" alt="image" /> ';
                    echo '<b><a href="/load/down?act=view&amp;id='.$data['downs_id'].'">'.$data['downs_title'].'</a></b> ('.$filesize.')</div>';

                    if (utf_strlen($data['downs_text']) > 300) {
                        $data['downs_text'] = strip_tags(bb_code($data['downs_text']), '<br>');
                        $data['downs_text'] = utf_substr($data['downs_text'], 0, 300).'...';
                    }

                    echo '<div>'.$data['downs_text'].'<br />';

                    echo 'Категория: <a href="/load/down?cid='.$data['downs_cats_id'].'">'.$data['cats_name'].'</a><br />';
                    echo 'Добавил: '.profile($data['downs_user']).' ('.date_fixed($data['downs_time']).')</div>';
                }

                page_strnavigation('/load/search?act=search&amp;find='.urlencode($find).'&amp;type='.$type.'&amp;where='.$where.'&amp;', $config['downlist'], $start, $total);
            } else {
                show_error('По вашему запросу ничего не найдено!');
            }
        }

    } else {
        show_error('Ошибка! Запрос должен содержать от 3 до 50 символов!');
    }

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/load/search">Вернуться</a><br />';
break;

endswitch;

} else {
    show_login('Вы не авторизованы, чтобы использовать поиск, необходимо');
}

echo '<img src="/images/img/reload.gif" alt="image" /> <a href="/load">Категории</a><br />';

App::view($config['themes'].'/foot');
