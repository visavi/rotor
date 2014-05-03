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

$config['listbanhist'] = 10;

if (empty($_GET['uz'])) {
	$uz = check($log);
} else {
	$uz = check(strval($_GET['uz']));
}
if (isset($_GET['start'])) {
	$start = abs(intval($_GET['start']));
} else {
	$start = 0;
}

if (is_user()) {
	show_title('История банов '.nickname($uz));

	$total = DB::run() -> querySingle("SELECT COUNT(*) FROM `banhist` WHERE `ban_user`=?;", array($uz));

	if ($total > 0) {
		if ($start >= $total) {
			$start = 0;
		}

		$queryhist = DB::run() -> query("SELECT * FROM `banhist` WHERE `ban_user`=? ORDER BY `ban_time` DESC LIMIT ".$start.", ".$config['listbanhist'].";", array($uz));

		while ($data = $queryhist -> fetch()) {
			echo '<div class="b">';
			echo '<img src="/images/img/history.gif" alt="image" /> ';
			echo '<b>'.profile($data['ban_user']).'</b> ('.date_fixed($data['ban_time']).')</div>';

			echo '<div>';
			if (!empty($data['ban_type'])) {
				echo 'Причина: '.bb_code($data['ban_reason']).'<br />';
				echo 'Срок: '.formattime($data['ban_term']).'<br />';
			}

			switch ($data['ban_type']) {
				case '1': $stat = '<span style="color:#ff0000">Забанил</span>:';
					break;
				case '2': $stat = '<span style="color:#ffa500">Изменил</span>:';
					break;
				default: $stat = '<span style="color:#00cc00">Разбанил</span>:';
			}

			echo $stat.' '.profile($data['ban_send']).'<br />';

			echo '</div>';
		}

		page_strnavigation('banhist.php?uz='.$uz.'&amp;', $config['listbanhist'], $start, $total);

		echo 'Всего действий: <b>'.$total.'</b><br /><br />';
	} else {
		show_error('В истории еще ничего нет!');
	}
} else {
	show_login('Вы не авторизованы, чтобы просматривать историю, необходимо');
}

echo '<img src="/images/img/back.gif" alt="image" /> <a href="user.php?uz='.$uz.'">В анкету</a><br />';

include_once ('../themes/footer.php');
?>
