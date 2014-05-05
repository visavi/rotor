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
if (isset($_POST['uz'])) {
	$uz = check($_POST['uz']);
} elseif (isset($_GET['uz'])) {
	$uz = check($_GET['uz']);
} else {
	$uz = '';
}

if (is_admin(array(101, 102, 103))) {
	show_title('Бан/Разбан');

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			echo '<div class="form">';
			echo 'Логин или ник пользователя:<br />';
			echo '<form method="post" action="zaban.php?act=edit">';
			echo '<input type="text" name="uz" maxlength="20" />';
			echo '<input value="Редактировать" type="submit" /></form></div><br />';

			echo 'Введите логин пользователя который необходимо отредактировать<br /><br />';
		break;

		############################################################################################
		##                                   Редактирование                                       ##
		############################################################################################
		case 'edit':

			$user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE LOWER(`users_login`)=? OR LOWER(`users_nickname`)=? LIMIT 1;", array(strtolower($uz), utf_lower($uz)));

			if (!empty($user)) {
				$uz = $user['users_login'];

				echo user_gender($user['users_login']).' <b>Профиль '.profile($user['users_login']).'</b> '.user_visit($user['users_login']).'<br /><br />';

				if (!empty($user['users_timelastban']) && !empty($user['users_reasonban'])) {
					echo '<div class="form">';
					echo 'Последний бан: '.date_fixed($user['users_timelastban'], 'j F Y / H:i').'<br />';
					echo 'Последняя причина: '.bb_code($user['users_reasonban']).'<br />';
					echo 'Забанил: '.profile($user['users_loginsendban']).'</div><br />';
				}

				$total = DB::run() -> querySingle("SELECT COUNT(*) FROM `banhist` WHERE `ban_user`=?;", array($uz));

				echo 'Строгих нарушений: <b>'.$user['users_totalban'].'</b><br />';
				echo '<img src="/images/img/history.gif" alt="image" /> <b><a href="banhist.php?act=view&amp;uz='.$uz.'">История банов</a></b> ('.$total.')<br /><br />';

				if ($user['users_level'] < 101 || $user['users_level'] > 105) {
					if (empty($user['users_ban']) || $user['users_timeban'] < SITETIME) {
						if ($user['users_totalban'] < 5) {
							echo '<div class="form">';
							echo '<form method="post" action="zaban.php?act=zaban&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'">';
							echo '<b>Время бана:</b><br /><input name="bantime" /><br />';

							echo '<input name="bantype" type="radio" value="min" checked="checked" /> Минут<br />';
							echo '<input name="bantype" type="radio" value="chas" /> Часов<br />';
							echo '<input name="bantype" type="radio" value="sut" /> Суток<br />';

							echo '<b>Причина бана:</b><br />';
							echo '<textarea name="reasonban" cols="25" rows="5"></textarea><br />';

							$usernote = DB::run() -> queryFetch("SELECT * FROM `note` WHERE `note_user`=? LIMIT 1;", array($uz));

							echo '<b>Заметка:</b><br />';
							echo '<textarea cols="25" rows="5" name="note">'.yes_br($usernote['note_text']).'</textarea><br />';

							echo '<input value="Забанить" type="submit" /></form></div><br />';

							echo 'Подсчет нарушений производится при бане более чем на 12 часов<br />';
							echo 'При общем числе нарушений более пяти, профиль пользователя удаляется<br />';
							echo 'Максимальное время бана '.round($config['maxbantime'] / 1440).' суток<br />';
							echo 'Внимание! Постарайтесь как можно подробнее описать причину бана<br /><br />';
						} else {
							echo '<b><span style="color:#ff0000">Внимание! Пользователь превысил лимит банов</span></b><br />';
							echo 'Вы можете удалить этот профиль!<br /><br />';
							echo '<img src="/images/img/error.gif" alt="image" /> <b><a href="zaban.php?act=deluser&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'">Удалить профиль</a></b><br /><br />';
						}
					} else {
						echo '<b><span style="color:#ff0000">Внимание, данный аккаунт заблокирован!</span></b><br />';
						echo 'До окончания бана: '.formattime($user['users_timeban'] - SITETIME).'<br /><br />';

						echo '<img src="/images/img/edit.gif" alt="image" /> <a href="zaban.php?act=editban&amp;uz='.$uz.'">Изменить</a><br />';
						echo '<img src="/images/img/reload.gif" alt="image" /> <a href="zaban.php?act=razban&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'">Разбанить</a><hr />';
					}
				} else {
					show_error('Ошибка! Запрещено банить админов и модеров сайта!');
				}
			} else {
				show_error('Ошибка! Пользователя с данным логином не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="zaban.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Редактирование бана                                  ##
		############################################################################################
		case 'editban':

			$user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `users_login`=? LIMIT 1;", array($uz));
			if (!empty($user)) {
				echo user_gender($user['users_login']).' <b>Профиль '.profile($user['users_login']).'</b> '.user_visit($user['users_login']).'<br /><br />';

				if ($user['users_level'] < 101 || $user['users_level'] > 105) {
					if (!empty($user['users_ban']) && $user['users_timeban'] > SITETIME) {
						if (!empty($user['users_timelastban'])) {
							echo 'Последний бан: '.date_fixed($user['users_timelastban'], 'j F Y / H:i').'<br />';
							echo 'Забанил: '.profile($user['users_loginsendban']).'<br />';
						}
						echo 'Строгих нарушений: <b>'.$user['users_totalban'].'</b><br />';
						echo 'До окончания бана: '.formattime($user['users_timeban'] - SITETIME).'<br /><br />';

						if ($user['users_timeban'] - SITETIME >= 86400) {
							$type = 'sut';
							$file_time = round(((($user['users_timeban'] - SITETIME) / 60) / 60) / 24, 1);
						} elseif (
							$user['users_timeban'] - SITETIME >= 3600) {
							$type = 'chas';
							$file_time = round((($user['users_timeban'] - SITETIME) / 60) / 60, 1);
						} else {
							$type = 'min';
							$file_time = round(($user['users_timeban'] - SITETIME) / 60);
						}

						echo '<div class="form">';
						echo '<form method="post" action="zaban.php?act=changeban&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'">';
						echo 'Время бана:<br /><input name="bantime" value="'.$file_time.'" /><br />';

						$checked = ($type == 'min') ? ' checked="checked"' : '';
						echo '<input name="bantype" type="radio" value="min"'.$checked.' /> Минут<br />';
						$checked = ($type == 'chas') ? ' checked="checked"' : '';
						echo '<input name="bantype" type="radio" value="chas"'.$checked.' /> Часов<br />';
						$checked = ($type == 'sut') ? ' checked="checked"' : '';
						echo '<input name="bantype" type="radio" value="sut"'.$checked.' /> Суток<br />';

						echo 'Причина бана:<br />';
						echo '<textarea name="reasonban" cols="25" rows="5">'.yes_br($user['users_reasonban']).'</textarea><br />';

						echo '<input value="Изменить" type="submit" /></form></div><br />';
					} else {
						show_error('Ошибка! Данный пользователь не забанен!');
					}
				} else {
					show_error('Ошибка! Запрещено банить админов и модеров сайта!');
				}
			} else {
				show_error('Ошибка! Пользователя с данным логином не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="zaban.php?act=edit&amp;uz='.$uz.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                     Изменение бана                                     ##
		############################################################################################
		case 'changeban':

			$uid = check($_GET['uid']);
			$bantime = abs(round($_POST['bantime'], 1));
			$bantype = check($_POST['bantype']);
			$reasonban = check($_POST['reasonban']);
			$note = check($_POST['note']);

			if ($uid == $_SESSION['token']) {
				$user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `users_login`=? LIMIT 1;", array($uz));

				if (!empty($user)) {
					if (!empty($user['users_ban']) && $user['users_timeban'] > SITETIME) {
						if ($user['users_level'] < 101 || $user['users_level'] > 105) {
							if ($bantype == 'min') {
								$bantotaltime = $bantime;
							}
							if ($bantype == 'chas') {
								$bantotaltime = round($bantime * 60);
							}
							if ($bantype == 'sut') {
								$bantotaltime = round($bantime * 1440);
							}

							if ($bantotaltime > 0) {
								if ($bantotaltime <= $config['maxbantime']) {
									if (utf_strlen($reasonban) >= 5 && utf_strlen($reasonban) <= 1000) {
										if (utf_strlen($note) <= 1000) {
											$note = no_br($note);
											$reasonban = no_br($reasonban);

											DB::run() -> query("UPDATE `users` SET `users_ban`=?, `users_timeban`=?, `users_reasonban`=?, `users_loginsendban`=? WHERE `users_login`=? LIMIT 1;", array(1, SITETIME + ($bantotaltime * 60), $reasonban, $log, $uz));

											DB::run() -> query("INSERT INTO `banhist` (`ban_user`, `ban_send`, `ban_type`, `ban_reason`, `ban_term`, `ban_time`) VALUES (?, ?, ?, ?, ?, ?);", array($uz, $log, 2, $reasonban, $bantotaltime * 60, SITETIME));

											$_SESSION['note'] = 'Данные успешно изменены!';
											redirect("zaban.php?act=edit&uz=$uz");
										} else {
											show_error('Ошибка! Слишком большая заметка, не более 1000 символов!');
										}
									} else {
										show_error('Ошибка! Слишком длинная или короткая причина бана!');
									}
								} else {
									show_error('Ошибка! Максимальное время бана '.round($config['maxbantime'] / 1440).' суток!');
								}
							} else {
								show_error('Ошибка! Вы не указали время бана!');
							}
						} else {
							show_error('Ошибка! Запрещено банить админов и модеров сайта!');
						}
					} else {
						show_error('Ошибка! Данный пользователь не забанен!');
					}
				} else {
					show_error('Ошибка! Пользователя с данным логином не существует!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="zaban.php?act=editban&amp;uz='.$uz.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                     Бан пользователя                                   ##
		############################################################################################
		case 'zaban':

			$uid = check($_GET['uid']);
			$bantime = abs(round($_POST['bantime'], 1));
			$bantype = check($_POST['bantype']);
			$reasonban = check($_POST['reasonban']);
			$note = check($_POST['note']);

			if ($uid == $_SESSION['token']) {
				$user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `users_login`=? LIMIT 1;", array($uz));

				if (!empty($user)) {
					if (empty($user['users_ban']) || $user['users_timeban'] < SITETIME) {
						if ($user['users_level'] < 101 || $user['users_level'] > 105) {
							if ($bantype == 'min') {
								$bantotaltime = $bantime;
							}
							if ($bantype == 'chas') {
								$bantotaltime = round($bantime * 60);
							}
							if ($bantype == 'sut') {
								$bantotaltime = round($bantime * 1440);
							}

							if ($bantotaltime > 0) {
								if ($bantotaltime <= $config['maxbantime']) {
									if (utf_strlen($reasonban) >= 5 && utf_strlen($reasonban) <= 1000) {
										if (utf_strlen($note) <= 1000) {
											$note = no_br($note);
											$reasonban = no_br($reasonban);

											if ($bantotaltime > 720) {
												$bancount = 1;
											} else {
												$bancount = 0;
											}

											DB::run() -> query("UPDATE `users` SET `users_ban`=?, `users_timeban`=?, `users_timelastban`=?, `users_reasonban`=?, `users_loginsendban`=?, `users_totalban`=`users_totalban`+?, `users_explainban`=? WHERE `users_login`=? LIMIT 1;", array(1, SITETIME + ($bantotaltime * 60), SITETIME, $reasonban, $log, $bancount, 1, $uz));

											DB::run() -> query("INSERT INTO `banhist` (`ban_user`, `ban_send`, `ban_type`, `ban_reason`, `ban_term`, `ban_time`) VALUES (?, ?, ?, ?, ?, ?);", array($uz, $log, 1, $reasonban, $bantotaltime * 60, SITETIME));

											DB::run() -> query("INSERT INTO `note` (`note_user`, `note_text`, `note_edit`, `note_time`) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `note_text`=?, `note_edit`=?, `note_time`=?;", array($uz, $note, $log, SITETIME, $note, $log, SITETIME));

											$_SESSION['note'] = 'Аккаунт успешно заблокирован!';
											redirect("zaban.php?act=edit&uz=$uz");
										} else {
											show_error('Ошибка! Слишком большая заметка, не более 1000 символов!');
										}
									} else {
										show_error('Ошибка! Слишком длинная или короткая причина бана!');
									}
								} else {
									show_error('Ошибка! Максимальное время бана '.round($config['maxbantime'] / 1440).' суток!');
								}
							} else {
								show_error('Ошибка! Вы не указали время бана!');
							}
						} else {
							show_error('Ошибка! Запрещено банить админов и модеров сайта!');
						}
					} else {
						show_error('Ошибка! Данный аккаунт уже заблокирован!');
					}
				} else {
					show_error('Ошибка! Пользователя с данным логином не существует!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="zaban.php?act=edit&amp;uz='.$uz.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Разбан пользователя                                 ##
		############################################################################################
		case 'razban':

			$uid = check($_GET['uid']);

			if ($uid == $_SESSION['token']) {
				$user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `users_login`=? LIMIT 1;", array($uz));

				if (!empty($user)) {
					if ($user['users_ban'] == 1) {
						if ($user['users_totalban'] > 0 && $user['users_timeban'] > SITETIME + 43200) {
							$bancount = 1;
						} else {
							$bancount = 0;
						}

						DB::run() -> query("UPDATE `users` SET `users_ban`=?, `users_timeban`=?, `users_totalban`=`users_totalban`-?, `users_explainban`=? WHERE `users_login`=? LIMIT 1;", array(0, 0, $bancount, 0, $uz));

						DB::run() -> query("INSERT INTO `banhist` (`ban_user`, `ban_send`, `ban_time`) VALUES (?, ?, ?);", array($uz, $log, SITETIME));

						$_SESSION['note'] = 'Аккаунт успешно разблокирован!';
						redirect("zaban.php?act=edit&uz=$uz");
					} else {
						show_error('Ошибка! Данный аккаунт уже разблокирован!');
					}
				} else {
					show_error('Ошибка! Пользователя с данным логином не существует!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="zaban.php?act=edit&amp;uz='.$uz.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Удаление пользователя                                ##
		############################################################################################
		case 'deluser':

			$uid = check($_GET['uid']);

			if ($uid == $_SESSION['token']) {
				$user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `users_login`=? LIMIT 1;", array($uz));

				if (!empty($user)) {
					if ($user['users_totalban'] >= 5) {
						if ($user['users_level'] < 101 || $user['users_level'] > 105) {

							$blackmail = DB::run() -> querySingle("SELECT `black_id` FROM `blacklist` WHERE `black_type`=? AND `black_value`=? LIMIT 1;", array(1, $user['users_email']));
							if (empty($blackmail) && !empty($user['users_email'])) {
								DB::run() -> query("INSERT INTO `blacklist` (`black_type`, `black_value`, `black_user`, `black_time`) VALUES (?, ?, ?, ?);", array(1, $user['users_email'], $log, SITETIME));
							}

							$blacklogin = DB::run() -> querySingle("SELECT `black_id` FROM `blacklist` WHERE `black_type`=? AND `black_value`=? LIMIT 1;", array(2, strtolower($user['users_login'])));
							if (empty($blacklogin)) {
								DB::run() -> query("INSERT INTO `blacklist` (`black_type`, `black_value`, `black_user`, `black_time`) VALUES (?, ?, ?, ?);", array(2, $user['users_login'], $log, SITETIME));
							}

							delete_album($uz);
							delete_users($uz);

							echo 'Данные занесены в черный список!<br />';
							echo '<img src="/images/img/open.gif" alt="image" /> <b>Профиль пользователя успешно удален!</b><br /><br />';
						} else {
							show_error('Ошибка! Запрещено банить админов и модеров сайта!');
						}
					} else {
						show_error('Ошибка! У пользователя менее 5 нарушений, удаление невозможно!');
					}
				} else {
					show_error('Ошибка! Пользователя с данным логином не существует!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo'<img src="/images/img/back.gif" alt="image" /> <a href="zaban.php">Вернуться</a><br />';
		break;

	default:
		redirect("zaban.php");
	endswitch;

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
