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

$config['usersearch'] = 30;

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

show_title('Поиск пользователей');

if (is_user()) {
switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
	case 'index':

		echo '<div class="form">';
		echo '<form method="post" action="searchuser.php?act=search">';
		echo 'Логин или ник юзера:<br /><input type="text" name="find" />';
		echo '<input value="Поиск" type="submit" /></form></div><br />';

		echo '<a href="searchuser.php?act=sort&amp;q=1">0-9</a> / <a href="searchuser.php?act=sort&amp;q=a">A</a> / <a href="searchuser.php?act=sort&amp;q=b">B</a> / <a href="searchuser.php?act=sort&amp;q=c">C</a> / <a href="searchuser.php?act=sort&amp;q=d">D</a> / <a href="searchuser.php?act=sort&amp;q=e">E</a> / <a href="searchuser.php?act=sort&amp;q=f">F</a> / <a href="searchuser.php?act=sort&amp;q=g">G</a> / <a href="searchuser.php?act=sort&amp;q=h">H</a> / <a href="searchuser.php?act=sort&amp;q=i">I</a> / <a href="searchuser.php?act=sort&amp;q=j">J</a> / <a href="searchuser.php?act=sort&amp;q=k">K</a> / <a href="searchuser.php?act=sort&amp;q=l">L</a> / <a href="searchuser.php?act=sort&amp;q=m">M</a> / <a href="searchuser.php?act=sort&amp;q=n">N</a> / <a href="searchuser.php?act=sort&amp;q=o">O</a> / <a href="searchuser.php?act=sort&amp;q=p">P</a> / <a href="searchuser.php?act=sort&amp;q=q">Q</a> / <a href="searchuser.php?act=sort&amp;q=r">R</a> / <a href="searchuser.php?act=sort&amp;q=s">S</a> / <a href="searchuser.php?act=sort&amp;q=t">T</a> / <a href="searchuser.php?act=sort&amp;q=u">U</a> / <a href="searchuser.php?act=sort&amp;q=v">V</a> / <a href="searchuser.php?act=sort&amp;q=w">W</a> / <a href="searchuser.php?act=sort&amp;q=x">X</a> / <a href="searchuser.php?act=sort&amp;q=y">Y</a> / <a href="searchuser.php?act=sort&amp;q=z">Z</a><br /><br />';

		echo 'Если результат поиска ничего не дал, тогда можно поискать по первым символам логина или ника<br />';
		echo 'В этом случае будет выдан результат похожий на введенный вами запрос<br /><br />';
	break;

	############################################################################################
	##                                  Сортировка профилей                                   ##
	############################################################################################
	case 'sort':
		if (isset($_POST['q'])) {
			$q = check(strtolower($_POST['q']));
		} else {
			$q = check(strtolower($_GET['q']));
		}

		if (!empty($q)) {
			if ($q == 1) {
				$search = "RLIKE '^[-0-9]'";
			} else {
				$search = "LIKE '$q%'";
			}

			$total = DB::run() -> querySingle("SELECT count(*) FROM `users` WHERE lower(`users_login`) ".$search.";");

			if ($total > 0) {
				if ($start >= $total) {
					$start = 0;
				}

				$queryuser = DB::run() -> query("SELECT `users_login`, `users_nickname`, `users_point` FROM `users` WHERE lower(`users_login`) ".$search." ORDER BY `users_point` DESC LIMIT ".$start.", ".$config['usersearch'].";");
				while ($data = $queryuser -> fetch()) {

					echo user_gender($data['users_login']).' <b>'.profile($data['users_login'], false, false).'</b> ';
					if (!empty($data['users_nickname'])) {
						echo '(Ник: '.$data['users_nickname'].') ';
					}
					echo user_online($data['users_login']).' ('.points($data['users_point']).')<br />';
				}

				page_strnavigation('searchuser.php?act=sort&amp;q='.$q.'&amp;', $config['usersearch'], $start, $total);

				echo 'Найдено совпадений: '.$total.'<br /><br />';
			} else {
				show_error('Совпадений не найдено!');
			}
		} else {
			show_error('Ошибка! Не выбраны критерии поиска пользователей!');
		}

		echo '<img src="/images/img/back.gif" alt="image" /> <a href="searchuser.php">Вернуться</a><br />';
	break;

	############################################################################################
	##                                    Поиск пользователя                                  ##
	############################################################################################
	case 'search':

		$find = check(strtolower($_POST['find']));

		if (utf_strlen($find)>=3 && utf_strlen($find)<=20) {
			$querysearch = DB::run() -> query("SELECT `users_login`, `users_point` FROM `users` WHERE lower(`users_login`) LIKE ? OR `users_nickname` LIKE ? ORDER BY `users_point` DESC LIMIT ".$config['usersearch'].";", array('%'.$find.'%', '%'.$find.'%'));

			$result = $querysearch -> fetchAll();
			$total = count($result);

			if ($total > 0) {
				foreach($result as $value) {
					echo user_gender($value['users_login']);

					if ($find == $value['users_login']) {
						echo '<b><big>'.profile($value['users_login'], '#ff0000').'</big></b> '.user_online($value['users_login']).' ('.points($value['users_point']).')<br />';
					} else {
						echo '<b>'.profile($value['users_login']).'</b> '.user_online($value['users_login']).' ('.points($value['users_point']).')<br />';
					}
				}

				echo '<br />Найдено совпадений: <b>'.$total.'</b><br /><br />';
			} else {
				show_error('По вашему запросу ничего не найдено');
			}
		} else {
			show_error('Ошибка! Слишком короткий или длинный запрос, от 3 до 20 символов!');
		}

		echo '<img src="/images/img/back.gif" alt="image" /> <a href="searchuser.php">Вернуться</a><br />';
	break;

default:
	redirect("searchuser.php");
endswitch;

} else {
	show_error('Ошибка! Для поиска пользователей необходимо авторизоваться!');
}

include_once ('../themes/footer.php');
?>
