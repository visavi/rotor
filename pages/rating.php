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
if (isset($_GET['uz'])) {
	$uz = check(strval($_GET['uz']));
} else {
	$uz = '';
}

show_title('Изменение авторитета');

if (is_user()) {
	if ($log != $uz) {
		if ($udata['users_point'] >= $config['editratingpoint']) {
			$queryuser = DB::run() -> querySingle("SELECT `users_id` FROM `users` WHERE `users_login`=? LIMIT 1;", array($uz));
			if (!empty($queryuser)) {
				$querytime = DB::run() -> querySingle("SELECT MAX(`rating_time`) FROM `rating` WHERE `rating_user`=? LIMIT 1;", array($log));
				if ($querytime + 10800 < SITETIME) {
					$queryrat = DB::run() -> querySingle("SELECT `rating_id` FROM `rating` WHERE `rating_user`=? AND `rating_login`=? AND `rating_time`>? LIMIT 1;", array($log, $uz, SITETIME-86400 * 30));
					if (empty($queryrat)) {

						switch ($act):
						############################################################################################
						##                                    Главная страница                                    ##
						############################################################################################
							case 'index':
								$vote = (empty($_GET['vote'])) ? 0 : 1;

								echo '<div class="b">'.user_avatars($uz).' <b>'.nickname($uz).' </b> '.user_visit($uz).'</div>';

								echo '<div class="form">';
								echo '<form action="rating.php?act=change&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'" method="post">';

								echo 'Рейтинг:<br />';
								echo '<select name="vote">';
								$selected = ($vote == 1) ? ' selected="selected"' : '';
								echo '<option value="1"'.$selected.'>Плюс</option>';
								$selected = ($vote == 0) ? ' selected="selected"' : '';
								echo '<option value="0"'.$selected.'>Минус</option>';
								echo '</select><br />';

								echo 'Комментарий: <br /><textarea cols="25" rows="5" name="text"></textarea><br />';

								echo '<input type="submit" value="Продолжить" /></form></div><br />';
							break;

							############################################################################################
							##                                  Изменение авторитета                                  ##
							############################################################################################
							case 'change':
								$uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';
								$text = (isset($_POST['text'])) ? check($_POST['text']) : '';
								$vote = (empty($_POST['vote'])) ? 0 : 1;

								if ($uid == $_SESSION['token']) {
									if (utf_strlen($text) >= 3 && utf_strlen($text) <= 250) {
										############################################################################################
										##                                Увеличение авторитета                                   ##
										############################################################################################
										if ($vote == 1) {
											$text = no_br($text);
											$text = antimat($text);
											$text = smiles($text);

											DB::run() -> query("INSERT INTO `rating` (`rating_user`, `rating_login`, `rating_text`, `rating_vote`, `rating_time`) VALUES (?, ?, ?, ?, ?);", array($log, $uz, $text, 1, SITETIME));
											DB::run() -> query("DELETE FROM `rating` WHERE `rating_user`=? AND `rating_time` < (SELECT MIN(`rating_time`) FROM (SELECT `rating_time` FROM `rating` WHERE `rating_user`=? ORDER BY `rating_time` DESC LIMIT 20) AS del);", array($log, $log));

											DB::run() -> query("UPDATE `users` SET `users_newprivat`=`users_newprivat`+1, `users_rating`=CAST(`users_posrating`AS SIGNED)-CAST(`users_negrating`AS SIGNED)+1, `users_posrating`=`users_posrating`+1 WHERE `users_login`=? LIMIT 1;", array($uz));

											$uzdata = DB::run() -> queryFetch("SELECT `users_rating`, `users_posrating`, `users_negrating` FROM `users` WHERE `users_login`=? LIMIT 1;", array($uz));
											// ------------------------------Уведомление по привату------------------------//
											$textpriv = '<img src="/images/img/thumb-up.gif" alt="plus" /> Пользователь [b]'.nickname($log).'[/b] поставил вам плюс! (Ваш рейтинг: '.$uzdata['users_rating'].')<br />Комментарий: '.$text;

											DB::run() -> query("INSERT INTO `inbox` (`inbox_user`, `inbox_author`, `inbox_text`, `inbox_time`) VALUES (?, ?, ?, ?);", array($uz, $log, $textpriv, SITETIME));

											echo '<img src="/images/img/open.gif" alt="Плюс" /> Ваш положительный голос за пользователя <b>'.nickname($uz).'</b> успешно оставлен!<br />';
											echo 'В данный момент его авторитет: '.$uzdata['users_rating'].'<br />';
											echo 'Всего положительных голосов: '.$uzdata['users_posrating'].'<br />';
											echo 'Всего отрицательных голосов: '.$uzdata['users_negrating'].'<br /><br />';

											echo 'От общего числа положительных и отрицательных голосов строится рейтинг самых авторитетных<br />';
											echo 'Внимание, следующий голос вы сможете оставить не менее чем через 3 часа!<br /><br />';

											$error = 0;
										}
										############################################################################################
										##                                Уменьшение авторитета                                   ##
										############################################################################################
										if ($vote == 0) {
											if ($udata['users_rating'] >= 10) {

												/* Запрещаем ставить обратный минус */
												$revertRating = DB::run() -> querySingle("SELECT `rating_id` FROM `rating` WHERE `rating_user`=? AND `rating_login`=? AND `rating_vote`=? ORDER BY `rating_time` DESC LIMIT 1;", array($uz, $log, 0));
												if (empty($revertRating)) {

													$text = no_br($text);
													$text = antimat($text);
													$text = smiles($text);

													DB::run() -> query("INSERT INTO `rating` (`rating_user`, `rating_login`, `rating_text`, `rating_vote`, `rating_time`) VALUES (?, ?, ?, ?, ?);", array($log, $uz, $text, 0, SITETIME));
													DB::run() -> query("DELETE FROM `rating` WHERE `rating_user`=? AND `rating_time` < (SELECT MIN(`rating_time`) FROM (SELECT `rating_time` FROM `rating` WHERE `rating_user`=? ORDER BY `rating_time` DESC LIMIT 20) AS del);", array($log, $log));

													DB::run() -> query("UPDATE `users` SET `users_newprivat`=`users_newprivat`+1, `users_rating`=CAST(`users_posrating`AS SIGNED)-CAST(`users_negrating`AS SIGNED)-1, `users_negrating`=`users_negrating`+1 WHERE `users_login`=? LIMIT 1;", array($uz));

													$uzdata = DB::run() -> queryFetch("SELECT `users_rating`, `users_posrating`, `users_negrating` FROM `users` WHERE `users_login`=? LIMIT 1;", array($uz));
													// ------------------------------Уведомление по привату------------------------//
													$textpriv = '<img src="/images/img/thumb-down.gif" alt="minus" /> Пользователь [b]'.nickname($log).'[/b] поставил вам минус! (Ваш рейтинг: '.$uzdata['users_rating'].')<br />Комментарий: '.$text;

													DB::run() -> query("INSERT INTO `inbox` (`inbox_user`, `inbox_author`, `inbox_text`, `inbox_time`) VALUES (?, ?, ?, ?);", array($uz, $log, $textpriv, SITETIME));

													echo '<img src="/images/img/error.gif" alt="Минус" /> Ваш отрицательный голос за пользователя <b>'.nickname($uz).'</b> успешно оставлен!<br />';
													echo 'В данный момент его авторитет: '.$uzdata['users_rating'].'<br />';
													echo 'Всего положительных голосов: '.$uzdata['users_posrating'].'<br />';
													echo 'Всего отрицательных голосов: '.$uzdata['users_negrating'].'<br /><br />';

													echo 'От общего числа положительных и отрицательных голосов строится рейтинг самых авторитетных<br />';
													echo 'Внимание, следующий голос вы сможете оставить не менее чем через 3 часа!<br /><br />';

													$error = 0;

												} else {
													show_error('Ошибка! Запрещено ставить обратный минус пользователю!');
												}
											} else {
												show_error('Ошибка! Уменьшать авторитет могут только пользователи с рейтингом 10 или выше!');
											}
										}
									} else {
										show_error('Ошибка! Слишком длинный или короткий комментарий!');
									}
								} else {
									show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
								}

								if (!empty($error)) {
									echo '<img src="/images/img/back.gif" alt="image" /> <a href="rating.php?uz='.$uz.'&amp;vote='.$vote.'">Вернуться</a><br />';
								}
							break;

						default:
							redirect("rating.php");
						endswitch;
					} else {
						show_error('Ошибка! Вы уже изменяли авторитет этому пользователю!');
					}
				} else {
					show_error('Ошибка! Разрешается изменять авторитет раз в 3 часа!');
				}
			} else {
				show_error('Ошибка! Данного пользователя не существует!');
			}
		} else {
			show_error('Ошибка! Для изменения авторитета вам необходимо набрать '.points($config['editratingpoint']).'!');
		}
	} else {
		show_error('Ошибка! Нельзя изменять авторитет самому себе!');
	}
} else {
	show_login('Вы не авторизованы, чтобы изменять авторитет, необходимо');
}

echo '<img src="/images/img/luggage.gif" alt="image" /> <a href="rathist.php?uz='.$uz.'">История</a><br />';
echo '<img src="/images/img/reload.gif" alt="image" /> <a href="user.php?uz='.$uz.'">В анкету</a><br />';

include_once ('../themes/footer.php');
?>
