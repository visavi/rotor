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

if (isset($_GET['act'])) {
	$act = check($_GET['act']);
} else {
	$act = 'index';
}
if (isset($_GET['start'])) {
	$start = abs(intval($_GET['start']));
} else {
	$start = 0;
}

show_title('Список последних комментариев');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
	case 'index':

		$total = DB::run() -> querySingle("SELECT count(*) FROM `commnews`;");

		if ($total > 0) {
			if ($total > 100) {
				$total = 100;
			}
			if ($start >= $total) {
				$start = last_page($total, $config['postnews']);
			}

			$querynews = DB::run() -> query("SELECT `commnews`.*, `news_title`, `news_comments` FROM `commnews` LEFT JOIN `news` ON `commnews`.`commnews_news_id`=`news`.`news_id` ORDER BY `commnews_time` DESC LIMIT ".$start.", ".$config['postnews'].";");

			while ($data = $querynews -> fetch()) {
				echo '<div class="b">';

				echo '<img src="/images/img/balloon.gif" alt="image" /> <b><a href="comments.php?act=viewcomm&amp;id='.$data['commnews_news_id'].'&amp;cid='.$data['commnews_id'].'">'.$data['news_title'].'</a></b> ('.$data['news_comments'].')</div>';

				echo '<div>'.bb_code($data['commnews_text']).'<br />';

				echo 'Написал: '.profile($data['commnews_author']).' <small>('.date_fixed($data['commnews_time']).')</small><br />';

				if (is_admin() || empty($config['anonymity'])) {
					echo '<span class="data">('.$data['commnews_brow'].', '.$data['commnews_ip'].')</span>';
				}

				echo '</div>';
			}

			page_strnavigation('comments.php?', $config['postnews'], $start, $total);
		} else {
			show_error('Комментарии не найдены!');
		}
	break;

	############################################################################################
	##                                     Переход к сообщение                                ##
	############################################################################################
	case 'viewcomm':

		if (isset($_GET['id'])) {
			$id = abs(intval($_GET['id']));
		} else {
			$id = 0;
		}
		if (isset($_GET['cid'])) {
			$cid = abs(intval($_GET['cid']));
		} else {
			$cid = 0;
		}

		$querycomm = DB::run() -> querySingle("SELECT COUNT(*) FROM `commnews` WHERE `commnews_id`<=? AND `commnews_news_id`=? ORDER BY `commnews_time` ASC LIMIT 1;", array($cid, $id));

		if (!empty($querycomm)) {
			$end = floor(($querycomm - 1) / $config['postnews']) * $config['postnews'];

			redirect("index.php?act=comments&id=$id&start=$end");

		} else {
			show_error('Ошибка! Комментарий к данной новости не существует!');
		}
	break;

default:
	redirect("comments.php");
endswitch;

echo '<img src="/images/img/reload.gif" alt="image" /> <a href="index.php">К новостям</a><br />';

include_once ('../themes/footer.php');
?>
