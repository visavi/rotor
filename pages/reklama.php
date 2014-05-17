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

show_title('Реклама на сайте');

if (!empty($config['rekusershow'])) {
	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			if (is_user()) {
				if ($udata['users_point'] >= 50) {
					$total = DB::run() -> querySingle("SELECT COUNT(*) FROM `rekuser` WHERE `rek_time`>?;", array(SITETIME));
					if ($total < $config['rekusertotal']) {

						$rekuser = DB::run() -> querySingle("SELECT `rek_id` FROM `rekuser` WHERE `rek_user`=? AND `rek_time`>? LIMIT 1;", array($log, SITETIME));
						if (empty($rekuser)) {
							echo 'У вас в наличии: <b>'.moneys($udata['users_money']).'</b><br /><br />';

							echo '<div class="form">';
							echo '<form method="post" action="reklama.php?act=add&amp;uid='.$_SESSION['token'].'">';

							echo 'Адрес сайта:<br />';
							echo '<input name="site" type="text" value="http://" maxlength="50" /><br />';

							echo 'Название ссылки:<br />';
							echo '<input name="name" type="text" maxlength="35" /><br />';

							echo 'Код цвета:';

							if (file_exists(BASEDIR.'/services/colors.php')) {
								echo ' <a href="/services/colors.php">(?)</a>';
							}
							echo '<br />';
							echo '<input name="color" type="text" maxlength="7" /><br />';

							echo 'Жирность: ';
							echo '<input name="bold" type="checkbox" value="1" /><br />';

							echo '<br /><input value="Купить" type="submit" /></form></div><br />';

							echo 'Стоимость размещения ссылки '.moneys($config['rekuserprice']).' за '.$config['rekusertime'].' часов<br />';
							echo 'Цвет и жирность опционально, стоимость каждой опции '.moneys($config['rekuseroptprice']).'<br />';
							echo 'Ссылка прокручивается на всех страницах сайта с другими ссылками пользователей<br />';
							echo 'В названии ссылки запрещено использовать любые ненормативные и матные слова<br />';
							echo 'Адрес ссылки не должен направлять на прямое скачивание какого-либо контента<br />';
							echo 'Запрещены ссылки на сайты с алярмами и порно<br />';
							echo 'За нарушение правил предусмотрено наказание в виде строгого бана<br /><br />';

						} else {
							show_error('Ошибка! Вы уже разместили рекламу, запрещено добавлять несколько сайтов подряд!');
						}
					} else {
						show_error('В данный момент нет свободных мест для размещения рекламы!');
					}
				} else {
					show_error('Ошибка! Для покупки рекламы вам необходимо набрать '.points(50).'!');
				}
			} else {
				show_login('Вы не авторизованы, для покупки рекламы, необходимо');
			}

			echo '<img src="/images/img/history.gif" alt="image" /> <a href="reklama.php?act=all">Полный список</a><br />';
		break;

		############################################################################################
		##                                   Действие при оплате                                  ##
		############################################################################################
		case 'add':

			$config['newtitle'] = 'Оплата рекламы';

			if (is_user()) {
				if ($udata['users_point'] >= 50) {
					$uid = check($_GET['uid']);
					$site = check($_POST['site']);
					$name = check($_POST['name']);
					$color = check($_POST['color']);
					$bold = (empty($_POST['bold'])) ? 0 : 1;

					if ($uid == $_SESSION['token']) {
						if (preg_match('|^http://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/\-?_=#])+)+$|iu', $site)) {
							if (utf_strlen($site) >= 10 && utf_strlen($site) <= 50) {
								if (utf_strlen($name) >= 5 && utf_strlen($name) <= 35) {
									if (preg_match('|^#+[A-f0-9]{6}$|', $color) || empty($color)) {
										DB::run() -> query("DELETE FROM `rekuser` WHERE `rek_time`<?;", array(SITETIME));

										$total = DB::run() -> querySingle("SELECT COUNT(*) FROM `rekuser` WHERE `rek_time`>?;", array(SITETIME));
										if ($total < $config['rekusertotal']) {
											$rekuser = DB::run() -> querySingle("SELECT `rek_id` FROM `rekuser` WHERE `rek_user`=? LIMIT 1;", array($log));
											if (empty($rekuser)) {
												$price = $config['rekuserprice'];

												if (!empty($color)) {
													$price = $price + $config['rekuseroptprice'];
												}

												if (!empty($bold)) {
													$price = $price + $config['rekuseroptprice'];
												}

												if ($udata['users_money'] >= $price) {

													DB::run() -> query("INSERT INTO `rekuser` (`rek_site`, `rek_name`, `rek_color`, `rek_bold`, `rek_user`, `rek_time`) VALUES (?, ?, ?, ?, ?, ?);", array($site, $name, $color, $bold, $log, SITETIME + ($config['rekusertime'] * 3600)));
													DB::run() -> query("UPDATE `users` SET `users_money`=`users_money`-? WHERE `users_login`=?;", array($price, $log));
													save_advertuser();

													$_SESSION['note'] = 'Рекламная ссылка успешно размещена (Cписано: '.moneys($price).')';
													redirect("reklama.php?act=all");

												} else {
													show_error('Ошибка! Для покупки рекламы у вас недостаточно денег!');
												}
											} else {
												show_error('Ошибка! Вы уже разместили рекламу, запрещено добавлять несколько сайтов подряд!');
											}
										} else {
											show_error('Ошибка! В данный момент нет свободных мест для размещения рекламы!');
										}
									} else {
										show_error('Ошибка! Недопустимый формат цвета ссылки! (пример #ff0000)');
									}
								} else {
									show_error('Ошибка! Слишком длинное или короткое название ссылки! (от 5 до 35 символов)');
								}
							} else {
								show_error('Ошибка! Слишком длинный или короткий адрес ссылки! (от 5 до 50 символов)');
							}
						} else {
							show_error('Ошибка! Недопустимый адрес сайта!');
						}
					} else {
						show_error('Ошибка! Неверный идентификатор сессии, повторите действие!!');
					}
				} else {
					show_error('Ошибка! Для покупки рекламы вам необходимо набрать '.points(50).'!');
				}
			} else {
				show_login('Вы не авторизованы, для покупки рекламы, необходимо');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="reklama.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Просмотр всех ссылок                                 ##
		############################################################################################
		case 'all':

			$config['newtitle'] = 'Список всех ссылок';

			$total = DB::run() -> querySingle("SELECT count(*) FROM `rekuser` WHERE `rek_time`>?;", array(SITETIME));

			if ($total > 0) {
				if ($start >= $total) {
					$start = 0;
				}

				$queryrek = DB::run() -> query("SELECT * FROM `rekuser` WHERE `rek_time`>? ORDER BY `rek_time` DESC LIMIT ".$start.", ".$config['rekuserpost'].";", array(SITETIME));

				while ($data = $queryrek -> fetch()) {
					echo '<div class="b">';
					echo '<img src="/images/img/online.gif" alt="image" /> ';
					echo '<b><a href="'.$data['rek_site'].'">'.$data['rek_name'].'</a></b> ('.profile($data['rek_user']).')</div>';

					echo 'Истекает: '.date_fixed($data['rek_time']).'<br />';

					if (!empty($data['rek_color'])) {
						echo 'Цвет: <span style="color:'.$data['rek_color'].'">'.$data['rek_color'].'</span>, ';
					} else {
						echo 'Цвет: нет, ';
					}

					if (!empty($data['rek_bold'])) {
						echo 'Жирность: есть<br />';
					} else {
						echo 'Жирность: нет<br />';
					}
				}

				page_strnavigation('reklama.php?act=all&amp;', $config['rekuserpost'], $start, $total);

				echo 'Всего ссылок: <b>'.$total.'</b><br /><br />';
			} else {
				show_error('В данный момент рекламных ссылок еще нет!');
			}

			echo '<img src="/images/img/money.gif" alt="image" /> <a href="reklama.php">Купить рекламу</a><br />';
		break;

	default:
		redirect("reklama.php");
	endswitch;

} else {
	show_error('Показ и размещение рекламы запрещено администрацией сайта!');
}

include_once ('../themes/footer.php');
?>
