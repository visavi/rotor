<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

show_title('Поиск в блогах');

if (is_user()) {
switch ($act):
############################################################################################
##                                    Главная поиска                                      ##
############################################################################################
case 'index':
    render('blog/search');
break;

############################################################################################
##                                          Поиск                                         ##
############################################################################################
case 'search':

    $find = check(strval($_GET['find']));
    $type = abs(intval($_GET['type']));
    $where = abs(intval($_GET['where']));

    if (!is_utf($find)){
        $find = win_to_utf($find);
    }

    if (utf_strlen($find) <= 50) {
        $findme = utf_lower($find);
        $findmewords = explode(" ", $findme);

        $arrfind = array();
        foreach ($findmewords as $valfind) {
            if (utf_strlen($valfind) >= 3) {
                $arrfind[] = $valfind;
            }
        }
        array_splice($arrfind, 3);

        if (count($arrfind) > 0) {
            $config['newtitle'] = $find.' - Результаты поиска';

            $types = (empty($type)) ? 'AND' : 'OR';
            $wheres = (empty($where)) ? 'title' : 'text';

            $blogfind = ($types.$wheres.$find);

            // ----------------------------- Поиск в названии -------------------------------//
            if ($wheres == 'title') {

                if ($type == 2) {
                    $arrfind[0] = $findme;
                }
                $search1 = (isset($arrfind[1]) && $type != 2) ? $types." `title` LIKE '%".$arrfind[1]."%'" : '';
                $search2 = (isset($arrfind[2]) && $type != 2) ? $types." `title` LIKE '%".$arrfind[2]."%'" : '';

                if (empty($_SESSION['blogfindres']) || $blogfind!=$_SESSION['blogfind']) {

                    $querysearch = DB::run() -> query("SELECT `id` FROM `blogs` WHERE `title` LIKE '%".$arrfind[0]."%' ".$search1." ".$search2." LIMIT 500;");
                    $result = $querysearch -> fetchAll(PDO::FETCH_COLUMN);

                    $_SESSION['blogfind'] = $blogfind;
                    $_SESSION['blogfindres'] = $result;
                }

                $total = count($_SESSION['blogfindres']);

                if ($total > 0) {
                    if ($start >= $total) {
                        $start = last_page($total, $config['blogpost']);
                    }

                    $result = implode(',', $_SESSION['blogfindres']);

                    $queryblog = DB::run() -> query("SELECT `blogs`.*, `cats_id`, `cats_name` FROM `blogs` LEFT JOIN `catsblog` ON `blogs`.`cats_id`=`catsblog`.`cats_id` WHERE `id` IN (".$result.") ORDER BY `time` DESC LIMIT ".$start.", ".$config['blogpost'].";");
                    $blogs = $queryblog -> fetchAll();

                    render('blog/search_title', array('blogs' => $blogs, 'find' => $find, 'total' => $total));

                    page_strnavigation('/blog/search?act=search&amp;find='.urlencode($find).'&amp;type='.$type.'&amp;where='.$where.'&amp;', $config['blogpost'], $start, $total);
                } else {
                    show_error('По вашему запросу ничего не найдено!');
                }
            }
            // --------------------------- Поиск в текте -------------------------------//
            if ($wheres == 'text') {

                if ($type == 2) {
                    $arrfind[0] = $findme;
                }
                $search1 = (isset($arrfind[1]) && $type != 2) ? $types." `text` LIKE '%".$arrfind[1]."%'" : '';
                $search2 = (isset($arrfind[2]) && $type != 2) ? $types." `text` LIKE '%".$arrfind[2]."%'" : '';

                if (empty($_SESSION['blogfindres']) || $blogfind!=$_SESSION['blogfind']) {

                    $querysearch = DB::run() -> query("SELECT `id` FROM `blogs` WHERE `text` LIKE '%".$arrfind[0]."%' ".$search1." ".$search2." LIMIT 500;");
                    $result = $querysearch -> fetchAll(PDO::FETCH_COLUMN);

                    $_SESSION['blogfind'] = $blogfind;
                    $_SESSION['blogfindres'] = $result;
                }

                $total = count($_SESSION['blogfindres']);

                if ($total > 0) {
                    if ($start >= $total) {
                        $start = last_page($total, $config['blogpost']);
                    }

                    $result = implode(',', $_SESSION['blogfindres']);

                    $queryblog = DB::run() -> query("SELECT `blogs`.*, `cats_id`, `cats_name` FROM `blogs` LEFT JOIN `catsblog` ON `blogs`.`cats_id`=`catsblog`.`cats_id` WHERE `id` IN (".$result.") ORDER BY `time` DESC LIMIT ".$start.", ".$config['blogpost'].";");
                    $blogs = $queryblog -> fetchAll();

                    render('blog/search_text', array('blogs' => $blogs, 'find' => $find, 'total' => $total));

                    page_strnavigation('/blog/search?act=search&amp;find='.urlencode($find).'&amp;type='.$type.'&amp;where='.$where.'&amp;', $config['blogpost'], $start, $total);
                } else {
                    show_error('По вашему запросу ничего не найдено!');
                }
            }
        } else {
            show_error('Ошибка! Необходимо не менее 3-х символов в слове!');
        }
    } else {
        show_error('Ошибка! Запрос должен содержать не более 50 символов!');
    }

    render('includes/back', array('link' => '/blog/search', 'title' => 'Вернуться'));
break;

endswitch;

} else {
    show_login('Вы не авторизованы, чтобы использовать поиск, необходимо');
}

render('includes/back', array('link' => '/blog', 'title' => 'К блогам', 'icon' => 'reload.gif'));

App::view($config['themes'].'/foot');
