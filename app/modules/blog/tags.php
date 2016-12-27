<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

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
        $page = App::paginate(App::setting('blogpost'), $total);

        if ($total > 0) {

            $result = implode(',', $_SESSION['findresult']);

            $queryblog = DB::run() -> query("SELECT b.*, name FROM blogs b LEFT JOIN catsblog c ON b.category_id=c.id WHERE b.id IN (".$result.") ORDER BY time DESC LIMIT ".$page['offset'].", ".App::setting('blogpost').";");
            $blogs = $queryblog -> fetchAll();

            render('blog/tags_search', compact('blogs', 'tags', 'page'));

            App::pagination($page);
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
