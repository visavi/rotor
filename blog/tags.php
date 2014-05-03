<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
require_once ('../includes/start.php');
require_once ('../includes/functions.php');
require_once ('../includes/header.php');
include_once ('../themes/header.php');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

	show_title('Облако тегов');
	$config['newtitle'] = 'Блоги - Облако тегов';

	if (@filemtime(DATADIR."/temp/tagcloud.dat") < time()-3600) {
		$querytag = DB::run() -> query("SELECT `blogs_tags` FROM `blogs`;");
		$tags = $querytag -> fetchAll(PDO::FETCH_COLUMN);

		$alltag = implode(',', $tags);
		$dumptags = preg_split('/[\s]*[,][\s]*/s', $alltag);
		$arraytags = array_count_values(array_map('utf_lower', $dumptags));

		arsort($arraytags);
		array_splice($arraytags, 50);
		shuffle_assoc($arraytags);

		file_put_contents(DATADIR."/temp/tagcloud.dat", serialize($arraytags), LOCK_EX);
	}

	$arraytags = unserialize(file_get_contents(DATADIR."/temp/tagcloud.dat"));

	$max = max($arraytags);
	$min = min($arraytags);

	render('blog/tags', array('tags' => $arraytags, 'max' => $max, 'min' => $min));
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
			$querysearch = DB::run() -> query("SELECT `blogs_id` FROM `blogs` WHERE `blogs_tags` LIKE '%".$tags."%' LIMIT 500;");
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

			$queryblog = DB::run() -> query("SELECT `blogs`.*, `cats_id`, `cats_name` FROM `blogs` LEFT JOIN `catsblog` ON `blogs`.`blogs_cats_id`=`catsblog`.`cats_id` WHERE `blogs_id` IN (".$result.") ORDER BY `blogs_time` DESC LIMIT ".$start.", ".$config['blogpost'].";");
			$blogs = $queryblog -> fetchAll();

			render('blog/tags_search', array('blogs' => $blogs, 'tags' => $tags, 'total' => $total));

			page_strnavigation('tags.php?act=search&amp;tags='.urlencode($tags).'&amp;', $config['blogpost'], $start, $total);
		} else {
			show_error('По вашему запросу ничего не найдено!');
		}
	} else {
		show_error('Ошибка! Необходимо не менее 2-х символов в запросе!');
	}

	render('includes/back', array('link' => 'tags.php', 'title' => 'Облако', 'icon' => 'balloon.gif'));
break;

default:
	redirect("tags.php");
endswitch;

render('includes/back', array('link' => 'index.php', 'title' => 'К блогам'));

include_once ('../themes/footer.php');
?>
