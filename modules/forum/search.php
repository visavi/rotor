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
$fid = (isset($_GET['fid'])) ? abs(intval($_GET['fid'])) : 0;

show_title('Поиск по форуму');

if (is_user()) {
switch ($act):
############################################################################################
##                                    Главная поиска                                      ##
############################################################################################
case 'index':

	$config['newtitle'] = 'Поиск по форуму';

	$queryforum = DB::run() -> query("SELECT `forums_id`, `forums_parent`, `forums_title` FROM `forums` ORDER BY `forums_order` ASC;");
	$forums = $queryforum -> fetchAll();

	if (count($forums) > 0) {
		$output = array();
		foreach ($forums as $row) {
			$i = $row['forums_id'];
			$p = $row['forums_parent'];
			$output[$p][$i] = $row;
		}

		render('forum/search', array('forums' => $output, 'fid' => $fid));
	} else {
		show_error('Разделы форума еще не созданы!');
	}
break;

############################################################################################
##                                          Поиск                                         ##
############################################################################################
case 'search':

	$find = check(strval($_GET['find']));
	$type = abs(intval($_GET['type']));
	$where = abs(intval($_GET['where']));
	$period = abs(intval($_GET['period']));
	$section = abs(intval($_GET['section']));

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

		$wheres = (empty($where)) ? 'topics' : 'posts';

		$forumfind = ($type.$wheres.$period.$section.$find);

		// ----------------------------- Поиск в темах -------------------------------//
		if ($wheres == 'topics') {

			if (empty($_SESSION['forumfindres']) || $forumfind!=$_SESSION['forumfind']) {

				$searchsec = ($section > 0) ? "`topics_forums_id`=".$section." AND" : '';
				$searchper = ($period > 0) ? "`topics_last_time`>".(SITETIME - ($period * 24 * 60 * 60))." AND" : '';

				$querysearch = DB::run() -> query("SELECT `topics_id` FROM `topics` WHERE ".$searchsec." ".$searchper."  MATCH (`topics_title`) AGAINST ('".$findme."' IN BOOLEAN MODE) LIMIT 100;");

				$result = $querysearch -> fetchAll(PDO::FETCH_COLUMN);

				$_SESSION['forumfind'] = $forumfind;
				$_SESSION['forumfindres'] = $result;
			}

			$total = count($_SESSION['forumfindres']);

			if ($total > 0) {
				if ($start >= $total) {
					$start = last_page($total, $config['forumtem']);
				}

				$result = implode(',', $_SESSION['forumfindres']);

				$querytopic = DB::run() -> query("SELECT * FROM `topics` WHERE `topics_id` IN (".$result.") ORDER BY `topics_last_time` DESC LIMIT ".$start.", ".$config['forumtem'].";");
				$topics = $querytopic->fetchAll();

				render('forum/search_topics', array('topics' => $topics, 'find' => $find, 'total' => $total));

				page_strnavigation('search.php?act=search&amp;find='.urlencode($find).'&amp;type='.$type.'&amp;where='.$where.'&amp;period='.$period.'&amp;section='.$section.'&amp;', $config['forumtem'], $start, $total);
			} else {
				show_error('По вашему запросу ничего не найдено!');
			}
		}

		// --------------------------- Поиск в сообщениях -------------------------------//
		if ($wheres == 'posts') {

			if (empty($_SESSION['forumfindres']) || $forumfind!=$_SESSION['forumfind']) {

				$searchsec = ($section > 0) ? "`posts_forums_id`=".$section." AND" : '';
				$searchper = ($period > 0) ? "`posts_time`>".(SITETIME - ($period * 24 * 60 * 60))." AND" : '';

				$querysearch = DB::run() -> query("SELECT `posts_id` FROM `posts` WHERE ".$searchsec." ".$searchper."  MATCH (`posts_text`) AGAINST ('".$findme."' IN BOOLEAN MODE) LIMIT 100;");
				$result = $querysearch -> fetchAll(PDO::FETCH_COLUMN);

				$_SESSION['forumfind'] = $forumfind;
				$_SESSION['forumfindres'] = $result;
			}

			$total = count($_SESSION['forumfindres']);

			if ($total > 0) {
				if ($start >= $total) {
					$start = last_page($total, $config['forumpost']);
				}

				$result = implode(',', $_SESSION['forumfindres']);

				$querypost = DB::run() -> query("SELECT `posts`.*, `topics_title` FROM `posts` LEFT JOIN `topics` ON `posts`.`posts_topics_id`=`topics`.`topics_id` WHERE `posts_id` IN (".$result.") ORDER BY `posts_time` DESC LIMIT ".$start.", ".$config['forumpost'].";");
				$posts = $querypost->fetchAll();

				render('forum/search_posts', array('posts' => $posts, 'find' => $find, 'total' => $total));

				page_strnavigation('search.php?act=search&amp;find='.urlencode($find).'&amp;type='.$type.'&amp;where='.$where.'&amp;period='.$period.'&amp;section='.$section.'&amp;', $config['forumpost'], $start, $total);
			} else {
				show_error('По вашему запросу ничего не найдено!');
			}
		}

	} else {
		show_error('Ошибка! Запрос должен содержать от 3 до 50 символов!');
	}

	render('includes/back', array('link' => 'search.php', 'title' => 'Вернуться'));
break;

default:
	redirect("search.php");
endswitch;

} else {
	show_login('Вы не авторизованы, чтобы использовать поиск, необходимо');
}

render('includes/back', array('link' => 'index.php', 'title' => 'К форумам', 'icon' => 'reload.gif'));

include_once ('../themes/footer.php');
?>
