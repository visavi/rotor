<?php
App::view(Setting::get('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

//show_title('Поиск в файлах');

if (is_user()) {
switch ($act):
############################################################################################
##                                    Главная поиска                                      ##
############################################################################################
case "index":

    //Setting::get('newtitle') = 'Поиск в файлах';

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

    $find = str_replace(['@', '+', '-', '*', '~', '<', '>', '(', ')', '"', "'"], '', $find);

    if (!is_utf($find)){
        $find = win_to_utf($find);
    }

    if (utf_strlen($find) >= 3 && utf_strlen($find) <= 50) {

        $findmewords = explode(" ", utf_lower($find));

        $arrfind = [];
        foreach ($findmewords as $val) {
            if (utf_strlen($val) >= 3) {
                $arrfind[] = (empty($type)) ? '+'.$val.'*' : $val.'*';
            }
        }

        $findme = implode(" ", $arrfind);

        if ($type == 2 && count($findmewords) > 1) {
            $findme = "\"$find\"";
        }

        //Setting::get('newtitle') = $find.' - Результаты поиска';

        $wheres = (empty($where)) ? 'title' : 'text';

        $loadfind = ($type.$wheres.$find);

        // ----------------------------- Поиск в названии -------------------------------//
        if ($wheres == 'title') {
            echo 'Поиск запроса <b>&quot;'.$find.'&quot;</b> в названии<br />';

            if (empty($_SESSION['loadfindres']) || $loadfind!=$_SESSION['loadfind']) {

                $querysearch = DB::run() -> query("SELECT `id` FROM `downs` WHERE `active`=? AND MATCH (`title`) AGAINST ('".$findme."' IN BOOLEAN MODE) LIMIT 100;", [1]);
                $result = $querysearch -> fetchAll(PDO::FETCH_COLUMN);

                $_SESSION['loadfind'] = $loadfind;
                $_SESSION['loadfindres'] = $result;
            }

            $total = count($_SESSION['loadfindres']);
            $page = App::paginate(Setting::get('downlist'), $total);

            if ($total > 0) {

                echo 'Найдено совпадений: <b>'.$total.'</b><br /><br />';

                $result = implode(',', $_SESSION['loadfindres']);

                $querydown = DB::run() -> query("SELECT `downs`.*, `name`, folder FROM `downs` LEFT JOIN `cats` ON `downs`.`category_id`=`cats`.`id` WHERE downs.`id` IN (".$result.") ORDER BY `time` DESC LIMIT ".$page['offset'].", ".Setting::get('downlist').";");

                while ($data = $querydown -> fetch()) {
                    $folder = $data['folder'] ? $data['folder'].'/' : '';

                    $filesize = (!empty($data['link'])) ? read_file(HOME.'/uploads/files/'.$folder.$data['link']) : 0;

                    echo '<div class="b"><i class="fa fa-file-o"></i> ';
                    echo '<b><a href="/load/down?act=view&amp;id='.$data['id'].'">'.$data['title'].'</a></b> ('.$filesize.')</div>';

                    echo '<div>Категория: <a href="/load/down?cid='.$data['id'].'">'.$data['name'].'</a><br />';
                    echo 'Скачиваний: '.$data['loads'].'<br />';
                    echo 'Добавил: '.profile($data['user']).' ('.date_fixed($data['time']).')</div>';
                }

                App::pagination($page);
            } else {
                show_error('По вашему запросу ничего не найдено!');
            }
        }
        // --------------------------- Поиск в описании -------------------------------//
        if ($wheres == 'text') {
            echo 'Поиск запроса <b>&quot;'.$find.'&quot;</b> в описании<br />';

            if (empty($_SESSION['loadfindres']) || $loadfind!=$_SESSION['loadfind']) {

                $querysearch = DB::run() -> query("SELECT `id` FROM `downs` WHERE `active`=? AND MATCH (`text`) AGAINST ('".$findme."' IN BOOLEAN MODE) LIMIT 100;", [1]);
                $result = $querysearch -> fetchAll(PDO::FETCH_COLUMN);

                $_SESSION['loadfind'] = $loadfind;
                $_SESSION['loadfindres'] = $result;
            }

            $total = count($_SESSION['loadfindres']);
            $page = App::paginate(Setting::get('downlist'), $total);

            if ($total > 0) {

                echo 'Найдено совпадений: <b>'.$total.'</b><br /><br />';

                $result = implode(',', $_SESSION['loadfindres']);

                $querydown = DB::run() -> query("SELECT `downs`.*, `name`, folder FROM `downs` LEFT JOIN `cats` ON `downs`.`category_id`=`cats`.`id` WHERE downs.`id` IN (".$result.") ORDER BY `time` DESC LIMIT ".$page['offset'].", ".Setting::get('downlist').";");

                while ($data = $querydown -> fetch()) {
                    $folder = $data['folder'] ? $data['folder'].'/' : '';

                    $filesize = (!empty($data['link'])) ? read_file(HOME.'/uploads/files/'.$folder.$data['link']) : 0;

                    echo '<div class="b"><i class="fa fa-file-o"></i> ';
                    echo '<b><a href="/load/down?act=view&amp;id='.$data['id'].'">'.$data['title'].'</a></b> ('.$filesize.')</div>';

                    if (utf_strlen($data['text']) > 300) {
                        $data['text'] = strip_tags(App::bbCode($data['text']), '<br>');
                        $data['text'] = utf_substr($data['text'], 0, 300).'...';
                    }

                    echo '<div>'.$data['text'].'<br />';

                    echo 'Категория: <a href="/load/down?cid='.$data['id'].'">'.$data['name'].'</a><br />';
                    echo 'Добавил: '.profile($data['user']).' ('.date_fixed($data['time']).')</div>';
                }

                App::pagination($page);
            } else {
                show_error('По вашему запросу ничего не найдено!');
            }
        }

    } else {
        show_error('Ошибка! Запрос должен содержать от 3 до 50 символов!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/search">Вернуться</a><br />';
break;

endswitch;

} else {
    show_login('Вы не авторизованы, чтобы использовать поиск, необходимо');
}

echo '<i class="fa fa-arrow-circle-up"></i> <a href="/load">Категории</a><br />';

App::view(Setting::get('themes').'/foot');
