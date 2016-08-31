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

show_title('Партнеры и друзья');

if (is_user()) {
	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case "index":

			include_once (BASEDIR."/includes/pyramid.php");

			echo '<br /><div class="form">';
			echo '<form action="pyramid.php?act=add" method="post">';
			echo 'Адрес сайта:<br />';
			echo '<input type="text" name="linkurl" value="http://" maxlength="50" /><br />';
			echo 'Название (max25):<br />';
			echo '<input type="text" name="linkname" maxlength="25" /><br />';
			echo '<input type="submit" value="Добавить" /></form></div><br />';

			echo 'В названии ссылки запрещено использовать любые ненормативные и матные слова<br />';
			echo 'За нарушение правил предусмотрено наказание в виде строгого бана<br /><br />';
		break;

		############################################################################################
		##                                  Добавление ссылки                                     ##
		############################################################################################
		case "add":

			$linkurl = check(utf_lower($_POST['linkurl']));
			$linkname = check($_POST['linkname']);

			if (utf_strlen($linkurl) >= 10 && utf_strlen($linkurl) <= 50) {
				if (utf_strlen($linkname) >= 5 && utf_strlen($linkname) <= 25) {
					if (preg_match('#^http://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', $linkurl)) {
						$pyrurl = DB::run() -> querySingle("SELECT `pyramid_id` FROM `pyramid` WHERE `pyramid_link`=? LIMIT 1;", array($linkurl));
						if (empty($pyrurl)) {
							$pyruser = DB::run() -> querySingle("SELECT `pyramid_id` FROM `pyramid` WHERE `pyramid_user`=? LIMIT 1;", array($log));
							if (empty($pyruser)) {
								$linkurl = antimat($linkurl);
								$linkname = antimat($linkname);

								DB::run() -> query("INSERT INTO `pyramid` (`pyramid_link`, `pyramid_name`, `pyramid_user`) VALUES (?, ?, ?);", array($linkurl, $linkname, $log));

								DB::run() -> query("DELETE FROM `pyramid` WHERE `pyramid_id` < (SELECT MIN(`pyramid_id`) FROM (SELECT `pyramid_id` FROM `pyramid` ORDER BY `pyramid_id` DESC LIMIT ".$config['showlink'].") AS del);");

								$_SESSION['note'] = 'Ваш сайт успешно добавлен в список партнеров и друзей!';
								redirect("pyramid.php");
							} else {
								show_error('Ошибка! Вы уже добавили сайт в базу, запрещено добавлять несколько сайтов подряд!');
							}
						} else {
							show_error('Ошибка! Данный сайт уже имеется в базе, запрещено добавлять несколько сайтов подряд!');
						}
					} else {
						show_error('Ошибка! Недопустимый адрес! Разрешается добавлять только адрес главной страницы!');
					}
				} else {
					show_error('Ошибка! Слишком длинное или короткое название. Не менее 5 и не более 25 символов!');
				}
			} else {
				show_error('Ошибка! Слишком длинный или короткий адрес ссылки. Не менее 10 и не более 50 символов!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="pyramid.php">Вернуться</a><br />';
		break;

	default:
		redirect("pyramid.php");
	endswitch;
} else {
	show_login('Вы не авторизованы, чтобы добавить новую ссылку, необходимо');
}

include_once ('../themes/footer.php');
?>
