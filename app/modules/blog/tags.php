<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    show_title('Облако тегов');
    $config['newtitle'] = 'Блоги - Облако тегов';

    if (@filemtime(STORAGE."/temp/tagcloud.dat") < time()-3600) {
        $querytag = DB::run() -> query("SELECT `tags` FROM `blogs`;");
        $tags = $querytag -> fetchAll(PDO::FETCH_COLUMN);

        $alltag = implode(',', $tags);
        $dumptags = preg_split('/[\s]*[,][\s]*/s', $alltag);
        $arraytags = array_count_values(array_map('utf_lower', $dumptags));

        arsort($arraytags);
        array_splice($arraytags, 50);
        shuffle_assoc($arraytags);

        file_put_contents(STORAGE."/temp/tagcloud.dat", serialize($arraytags), LOCK_EX);
    }

    $arraytags = unserialize(file_get_contents(STORAGE."/temp/tagcloud.dat"));

    $max = max($arraytags);
    $min = min($arraytags);

    render('blog/tags', ['tags' => $arraytags, 'max' => $max, 'min' => $min]);
break;

############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'search':

    show_title('Поиск по тегам');
    $config['newtitle'] = 'Блоги - Поиск по тегам';

    $tags = (isset($_GET['tags'])) ? check($_GET['tags']) : '';

    if (!is_utf($tags)){
        $tags = win_to_utf($tags);
    }

    if (utf_strlen($tags) >= 2) {

        if (empty($_SESSION['findresult']) || empty($_SESSION['blogfind']) || $tags!=$_SESSION['blogfind']) {
            $querysearch = DB::run() -> query("SELECT `id` FROM `blogs` WHERE `tags` LIKE '%".$tags."%' LIMIT 500;");
            $result = $querysearch -> fetchAll(PDO::FETCH_COLUMN);

            $_SESSION['blogfind'] = $tags;
            $_SESSION['findresult'] = $result;
        }

        $total = count($_SESSION['findresult']);

        if ($total > 0) {
            if ($start >= $total) {
                $start = last_page($total, $config['blogpost']);
            }

            $result = implode(',', $_SESSION['findresult']);

            $queryblog = DB::run() -> query("SELECT `blogs`.*, `id`, `name` FROM `blogs` LEFT JOIN `catsblog` ON `blogs`.`cats_id`=`catsblog`.`id` WHERE `id` IN (".$result.") ORDER BY `time` DESC LIMIT ".$start.", ".$config['blogpost'].";");
            $blogs = $queryblog -> fetchAll();

            render('blog/tags_search', ['blogs' => $blogs, 'tags' => $tags, 'total' => $total]);

            page_strnavigation('/blog/tags?act=search&amp;tags='.urlencode($tags).'&amp;', $config['blogpost'], $start, $total);
        } else {
            show_error('По вашему запросу ничего не найдено!');
        }
    } else {
        show_error('Ошибка! Необходимо не менее 2-х символов в запросе!');
    }

    render('includes/back', ['link' => '/blog/tags', 'title' => 'Облако', 'icon' => 'balloon.gif']);
break;

endswitch;

render('includes/back', ['link' => '/blog', 'title' => 'К блогам']);

App::view($config['themes'].'/foot');
