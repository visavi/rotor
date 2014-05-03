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
##                                       Топ тем                                          ##
############################################################################################
	case "index":
		show_title('Топ популярных тем');

		$total = DB::run() -> querySingle("SELECT count(*) FROM topics;");

		if ($total > 0) {
			if ($start >= $total) {
				$start = last_page($total, $config['forumtem']);
			}

			$querytopic = DB::run() -> query("SELECT * FROM `topics` WHERE `topics_closed`=? ORDER BY `topics_posts` DESC LIMIT ".$start.", ".$config['forumtem'].";", array(0));
			$topics = $querytopic->fetchAll();

			render('forum/top', array('topics' => $topics));

			page_strnavigation('top.php?', $config['forumtem'], $start, $total);
		} else {
			show_error('Созданных тем еще нет!');
		}
	break;

default:
	redirect("top.php");
endswitch;

render('includes/back', array('link' => 'index.php', 'title' => 'К форумам', 'icon' => 'reload.gif'));

include_once ('../themes/footer.php');
?>
