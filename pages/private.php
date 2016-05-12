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
$uz = (isset($_REQUEST['uz'])) ? check($_REQUEST['uz']) : '';

show_title('Приватные сообщения');

if (is_user()) {
	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			$total = DB::run() -> querySingle("SELECT count(*) FROM `inbox` WHERE `inbox_user`=?;", array($log));

			$intotal = DB::run() -> query("SELECT count(*) FROM `outbox` WHERE `outbox_author`=? UNION ALL SELECT count(*) FROM `trash` WHERE `trash_user`=?;", array($log, $log));
			$intotal = $intotal -> fetchAll(PDO::FETCH_COLUMN);

			echo '<img src="/images/img/mail.gif" alt="image" /> <b>Входящие ('.$total.')</b> / ';
			echo '<a href="private.php?act=output">Отправленные ('.$intotal[0].')</a> / ';
			echo '<a href="private.php?act=trash">Корзина ('.$intotal[1].')</a><hr />';

			if ($udata['users_newprivat'] > 0) {
				echo '<div style="text-align:center"><b><span style="color:#ff0000">Получено новых писем: '.(int)$udata['users_newprivat'].'</span></b></div>';
				DB::run() -> query("UPDATE `users` SET `users_newprivat`=?, `users_sendprivatmail`=? WHERE `users_login`=? LIMIT 1;", array(0, 0, $log));
			}

			if ($total >= ($config['limitmail'] - ($config['limitmail'] / 10)) && $total < $config['limitmail']) {
				echo '<div style="text-align:center"><b><span style="color:#ff0000">Ваш ящик почти заполнен, необходимо очистить или удалить старые сообщения!</span></b></div>';
			}

			if ($total >= $config['limitmail']) {
				echo '<div style="text-align:center"><b><span style="color:#ff0000">Ваш ящик переполнен, вы не сможете получать письма пока не очистите его!</span></b></div>';
			}

			if ($total > 0) {
				if ($start >= $total) {
					$start = last_page($total, $config['privatpost']);
				}

				$querypriv = DB::run() -> query("SELECT * FROM `inbox` WHERE `inbox_user`=? ORDER BY `inbox_time` DESC LIMIT ".$start.", ".$config['privatpost'].";", array($log));

				echo '<form action="private.php?act=del&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
				echo '<div class="form">';
				echo '<input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked" /> <b><label for="all">Отметить все</label></b>';
				echo '</div>';
				while ($data = $querypriv -> fetch()) {
					echo '<div class="b">';
					echo '<div class="img">'.user_avatars($data['inbox_author']).'</div>';
					echo '<b>'.profile($data['inbox_author']).'</b>  ('.date_fixed($data['inbox_time']).')<br />';
					echo user_title($data['inbox_author']).' '.user_online($data['inbox_author']).'</div>';

					echo '<div>'.bb_code($data['inbox_text']).'<br />';

					echo '<input type="checkbox" name="del[]" value="'.$data['inbox_id'].'" /> ';
					echo '<a href="private.php?act=submit&amp;uz='.$data['inbox_author'].'">Ответить</a> / ';
					echo '<a href="private.php?act=history&amp;uz='.$data['inbox_author'].'">История</a> / ';
					echo '<a href="contact.php?act=add&amp;uz='.$data['inbox_author'].'&amp;uid='.$_SESSION['token'].'">В контакт</a> / ';
					echo '<a href="ignore.php?act=add&amp;uz='.$data['inbox_author'].'&amp;uid='.$_SESSION['token'].'">Игнор</a> / ';
					echo '<noindex><a href="private.php?act=spam&amp;id='.$data['inbox_id'].'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы подтверждаете факт спама?\')" rel="nofollow">Спам</a></noindex></div>';
				}

				echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

				page_strnavigation('private.php?', $config['privatpost'], $start, $total);

				echo 'Всего писем: <b>'.(int)$total.'</b><br />';
				echo 'Объем ящика: <b>'.$config['limitmail'].'</b><br /><br />';

				echo '<img src="/images/img/error.gif" alt="image" /> <a href="private.php?act=alldel&amp;uid='.$_SESSION['token'].'">Очистить ящик</a><br />';
				echo '<img src="/images/img/reload.gif" alt="image" /> <a href="private.php?rand='.mt_rand(100, 999).'">Обновить список</a><br />';
			} else {
				show_error('Входящих писем еще нет!');
			}
		break;

		############################################################################################
		##                                 Исходящие сообщения                                    ##
		############################################################################################
		case 'output':

			$total = DB::run() -> querySingle("SELECT count(*) FROM `outbox` WHERE `outbox_author`=?;", array($log));

			$intotal = DB::run() -> query("SELECT count(*) FROM `inbox` WHERE `inbox_user`=? UNION ALL SELECT count(*) FROM `trash` WHERE `trash_user`=?;", array($log, $log));
			$intotal = $intotal -> fetchAll(PDO::FETCH_COLUMN);

			echo '<img src="/images/img/mail.gif" alt="image" /> <a href="private.php">Входящие ('.$intotal[0].')</a> / ';
			echo '<b>Отправленные ('.$total.')</b> / ';
			echo '<a href="private.php?act=trash">Корзина ('.$intotal[1].')</a><hr />';

			if ($total > 0) {
				if ($start >= $total) {
					$start = last_page($total, $config['privatpost']);
				}

				$querypriv = DB::run() -> query("SELECT * FROM `outbox` WHERE `outbox_author`=? ORDER BY `outbox_time` DESC LIMIT ".$start.", ".$config['privatpost'].";", array($log));

				echo '<form action="private.php?act=outdel&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
				echo '<div class="form">';
				echo '<input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked" /> <b><label for="all">Отметить все</label></b>';
				echo '</div>';
				while ($data = $querypriv -> fetch()) {
					echo '<div class="b">';
					echo '<div class="img">'.user_avatars($data['outbox_user']).'</div>';
					echo '<b>'.profile($data['outbox_user']).'</b>  ('.date_fixed($data['outbox_time']).')<br />';
					echo user_title($data['outbox_user']).' '.user_online($data['outbox_user']).'</div>';

					echo '<div>'.bb_code($data['outbox_text']).'<br />';

					echo '<input type="checkbox" name="del[]" value="'.$data['outbox_id'].'" /> ';
					echo '<a href="private.php?act=submit&amp;uz='.$data['outbox_user'].'">Написать еще</a> / ';
					echo '<a href="private.php?act=history&amp;uz='.$data['outbox_user'].'">История</a></div>';
				}

				echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

				page_strnavigation('private.php?act=output&amp;', $config['privatpost'], $start, $total);

				echo 'Всего писем: <b>'.(int)$total.'</b><br />';
				echo 'Объем ящика: <b>'.$config['limitoutmail'].'</b><br /><br />';

				echo '<img src="/images/img/error.gif" alt="image" /> <a href="private.php?act=alloutdel&amp;uid='.$_SESSION['token'].'">Очистить ящик</a><br />';
			} else {
				show_error('Отправленных писем еще нет!');
			}
		break;

		############################################################################################
		##                                       Корзина                                          ##
		############################################################################################
		case 'trash':

			$total = DB::run() -> querySingle("SELECT count(*) FROM `trash` WHERE `trash_user`=?;", array($log));

			$intotal = DB::run() -> query("SELECT count(*) FROM `inbox` WHERE `inbox_user`=? UNION ALL SELECT count(*) FROM `outbox` WHERE `outbox_author`=?;", array($log, $log));
			$intotal = $intotal -> fetchAll(PDO::FETCH_COLUMN);

			echo '<img src="/images/img/mail.gif" alt="image" /> <a href="private.php">Входящие ('.$intotal[0].')</a> / ';
			echo '<a href="private.php?act=output">Отправленные ('.$intotal[1].')</a> / ';

			echo '<b>Корзина ('.$total.')</b><hr />';
			if ($total > 0) {
				if ($start >= $total) {
					$start = last_page($total, $config['privatpost']);
				}

				$querypriv = DB::run() -> query("SELECT * FROM `trash` WHERE `trash_user`=? ORDER BY `trash_time` DESC LIMIT ".$start.", ".$config['privatpost'].";", array($log));

				while ($data = $querypriv -> fetch()) {
					echo '<div class="b">';
					echo '<div class="img">'.user_avatars($data['trash_author']).'</div>';
					echo '<b>'.profile($data['trash_author']).'</b>  ('.date_fixed($data['trash_time']).')<br />';
					echo user_title($data['trash_author']).' '.user_online($data['trash_author']).'</div>';

					echo '<div>'.bb_code($data['trash_text']).'<br />';

					echo '<a href="private.php?act=submit&amp;uz='.$data['trash_author'].'">Ответить</a> / ';
					echo '<a href="contact.php?act=add&amp;uz='.$data['trash_author'].'&amp;uid='.$_SESSION['token'].'">В контакт</a> / ';
					echo '<a href="ignore.php?act=add&amp;uz='.$data['trash_author'].'&amp;uid='.$_SESSION['token'].'">Игнор</a></div>';
				}

				page_strnavigation('private.php?act=trash&amp;', $config['privatpost'], $start, $total);

				echo 'Всего писем: <b>'.(int)$total.'</b><br />';
				echo 'Срок хранения: <b>'.$config['expiresmail'].'</b><br /><br />';

				echo '<img src="/images/img/error.gif" alt="image" /> <a href="private.php?act=alltrashdel&amp;uid='.$_SESSION['token'].'">Очистить ящик</a><br />';
			} else {
				show_error('Удаленных писем еще нет!');
			}
		break;

		############################################################################################
		##                                   Отправка привата                                     ##
		############################################################################################
		case 'submit':

			if (empty($uz)) {

				echo '<div class="form">';
				echo '<form action="private.php?act=send&amp;uid='.$_SESSION['token'].'" method="post">';

				echo 'Введите логин:<br />';
				echo '<input type="text" name="uz" maxlength="20" /><br />';

				$querycontact = DB::run() -> query("SELECT `contact_name` FROM `contact` WHERE `contact_user`=? ORDER BY `contact_name` DESC;", array($log));
				$contact = $querycontact -> fetchAll();

				if (count($contact) > 0) {
					echo 'Или выберите из списка:<br />';
					echo '<select name="uzcon">';
					echo '<option value="0">Список контактов</option>';

					foreach($contact as $data) {
						echo '<option value="'.$data['contact_name'].'">'.nickname($data['contact_name']).'</option>';
					}
					echo '</select><br />';
				}

				echo 'Сообщение:<br />';
				echo '<textarea cols="25" rows="5" name="msg"></textarea><br />';

				if ($udata['users_point'] < $config['privatprotect']) {
					echo 'Проверочный код:<br />';
					echo '<img src="/gallery/protect.php" alt="" /><br />';
					echo '<input name="provkod" size="6" maxlength="6" /><br />';
				}

				echo '<input value="Отправить" type="submit" /></form></div><br />';

				echo 'Введите логин или выберите пользователя из своего контакт-листа<br />';

			} else {
				if (!user_privacy($uz) || is_admin() || is_contact($uz, $log)){

					echo '<img src="/images/img/mail.gif" alt="image" /> Сообщение для <b>'.profile($uz).'</b> '.user_visit($uz).':<br />';
					echo '<img src="/images/img/history.gif" alt="image" /> <a href="private.php?act=history&amp;uz='.$uz.'">История переписки</a><br /><br />';

					$ignorstr = DB::run() -> querySingle("SELECT `ignore_id` FROM `ignore` WHERE `ignore_user`=? AND `ignore_name`=? LIMIT 1;", array($log, $uz));
					if (!empty($ignorstr)) {
						echo '<b>Внимание! Данный пользователь внесен в ваш игнор-лист!</b><br />';
					}

					echo '<div class="form">';
					echo '<form action="private.php?act=send&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'" method="post">';
					echo 'Сообщение:<br />';
					echo '<textarea cols="25" rows="5" name="msg"></textarea><br />';

					if ($udata['users_point'] < $config['privatprotect']) {
						echo 'Проверочный код:<br />';
						echo '<img src="/gallery/protect.php" alt="" /><br />';
						echo '<input name="provkod" size="6" maxlength="6" /><br />';
					}

					echo '<input value="Отправить" type="submit" /></form></div><br />';

				} else {
					show_error('Включен режим приватности, писать могут только пользователи из контактов!');
				}
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="private.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Отправка сообщений                                   ##
		############################################################################################
		case 'send':

			$uid = !empty($_GET['uid']) ? check($_GET['uid']) : 0;
			$msg = isset($_POST['msg']) ? check($_POST['msg']) : '';
			$uz = isset($_POST['uzcon']) ? check($_POST['uzcon']) : $uz;
			$provkod = isset($_POST['provkod']) ? check(strtolower($_POST['provkod'])) : '';

			if ($uid == $_SESSION['token']) {
				if (!empty($uz)) {
					if ($uz != $log) {
						if (!user_privacy($uz) || is_admin() || is_contact($uz, $log)){
							if ($udata['users_point'] >= $config['privatprotect'] || $provkod == $_SESSION['protect']) {
								if (utf_strlen($msg) >= 5 && utf_strlen($msg) < 1000) {
									$queryuser = DB::run() -> querySingle("SELECT `users_id` FROM `users` WHERE `users_login`=? LIMIT 1;", array($uz));
									if (!empty($queryuser)) {
										$uztotal = DB::run() -> querySingle("SELECT count(*) FROM `inbox` WHERE `inbox_user`=?;", array($uz));
										if ($uztotal < $config['limitmail']) {
											// ----------------------------- Проверка на игнор ----------------------------//
											$ignorstr = DB::run() -> querySingle("SELECT `ignore_id` FROM `ignore` WHERE `ignore_user`=? AND `ignore_name`=? LIMIT 1;", array($uz, $log));
											if (empty($ignorstr)) {
												if (is_flood($log)) {

													$msg = antimat($msg);

													DB::run() -> query("UPDATE `users` SET `users_newprivat`=`users_newprivat`+1 WHERE `users_login`=? LIMIT 1;", array($uz));
													DB::run() -> query("INSERT INTO `inbox` (`inbox_user`, `inbox_author`, `inbox_text`, `inbox_time`) VALUES (?, ?, ?, ?);", array($uz, $log, $msg, SITETIME));

													DB::run() -> query("INSERT INTO `outbox` (`outbox_user`, `outbox_author`, `outbox_text`, `outbox_time`) VALUES (?, ?, ?, ?);", array($uz, $log, $msg, SITETIME));

													DB::run() -> query("DELETE FROM `outbox` WHERE `outbox_author`=? AND `outbox_time` < (SELECT MIN(`outbox_time`) FROM (SELECT `outbox_time` FROM `outbox` WHERE `outbox_author`=? ORDER BY `outbox_time` DESC LIMIT ".$config['limitoutmail'].") AS del);", array($log, $log));
													save_usermail(60);

													$deliveryUsers = DBM::run()->select('users', array(
															'users_newprivat' => array('>', 0),
															'users_sendprivatmail' => 0,
															'users_timelastlogin' => array('<', SITETIME - 86400 * $config['sendprivatmailday']),
													), $config['sendmailpacket'], null, array('users_timelastlogin'=>'ASC'));

													foreach ($deliveryUsers as $user) {
														addmail($user['users_email'], $user['users_newprivat']." непрочитанных сообщений (".$config['title'].")", "Здравствуйте ".nickname($user['users_login'])."! \nУ вас имеются непрочитанные сообщения (".$user['users_newprivat']." шт.) на сайте ".$config['title']." \nПрочитать свои сообщения вы можете по адресу ".$config['home']."/pages/private.php");

														$user = DBM::run()->update('users', array(
															'users_sendprivatmail' => 1,
														), array(
															'users_login' => $user['users_login'],
														));
													}
													notice('Ваше письмо успешно отправлено!');
													redirect("private.php");

												} else {
													show_error('Антифлуд! Разрешается отправлять сообщения раз в '.flood_period().' секунд!');
												}
											} else {
												show_error('Ошибка! Вы внесены в игнор-лист получателя!');
											}
										} else {
											show_error('Ошибка! Ящик получателя переполнен!');
										}
									} else {
										show_error('Ошибка! Данного адресата не существует!');
									}
								} else {
									show_error('Ошибка! Слишком длинное или короткое сообщение!');
								}
							} else {
								show_error('Ошибка! Проверочное число не совпало с данными на картинке!');
							}
						} else {
							show_error('Включен режим приватности, писать могут только пользователи из контактов!');
						}
					} else {
						show_error('Ошибка! Нельзя отправлять письмо самому себе!');
					}
				} else {
					show_error('Ошибка! Вы не ввели логин пользователя!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="private.php?act=submit&amp;uz='.$uz.'">Вернуться</a><br />';
			echo '<img src="/images/img/reload.gif" alt="image" /> <a href="private.php">К письмам</a><br />';
		break;

		############################################################################################
		##                                    Жалоба на спам                                      ##
		############################################################################################
		case 'spam':

			$uid = check($_GET['uid']);
			$id = abs(intval($_GET['id']));

			if ($uid == $_SESSION['token']) {
				$data = DB::run() -> queryFetch("SELECT * FROM `inbox` WHERE `inbox_user`=? AND `inbox_id`=? LIMIT 1;", array($log, $id));
				if (!empty($data)) {
					$queryspam = DB::run() -> querySingle("SELECT `spam_id` FROM `spam` WHERE `spam_key`=? AND `spam_idnum`=? LIMIT 1;", array(3, $id));

					if (empty($queryspam)) {
						if (is_flood($log)) {
							DB::run() -> query("INSERT INTO `spam` (`spam_key`, `spam_idnum`, `spam_user`, `spam_login`, `spam_text`, `spam_time`, `spam_addtime`) VALUES (?, ?, ?, ?, ?, ?, ?);", array(3, $data['inbox_id'], $log, $data['inbox_author'], $data['inbox_text'], $data['inbox_time'], SITETIME));

							$_SESSION['note'] = 'Жалоба успешно отправлена!';
							redirect("private.php?start=$start");

						} else {
							show_error('Антифлуд! Разрешается жаловаться на спам не чаще чем раз в '.flood_period().' секунд!');
						}
					} else {
						show_error('Ошибка! Вы уже отправили жалобу на данное сообщение!');
					}
				} else {
					show_error('Ошибка! Данное сообщение адресовано не вам!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="private.php?start='.$start.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                 Удаление сообщений                                     ##
		############################################################################################
		case 'del':

			$uid = check($_GET['uid']);
			if (isset($_POST['del'])) {
				$del = intar($_POST['del']);
			} else {
				$del = 0;
			}

			if ($uid == $_SESSION['token']) {
				if (!empty($del)) {
					$del = implode(',', $del);
					$deltrash = SITETIME + 86400 * $config['expiresmail'];

					DB::run() -> query("DELETE FROM `trash` WHERE `trash_del`<?;", array(SITETIME));

					DB::run() -> query("INSERT INTO `trash` (`trash_user`, `trash_author`, `trash_text`, `trash_time`, `trash_del`) SELECT `inbox_user`, `inbox_author`, `inbox_text`, `inbox_time`, ? FROM `inbox` WHERE `inbox_id` IN (".$del.") AND `inbox_user`=?;", array($deltrash, $log));

					DB::run() -> query("DELETE FROM `inbox` WHERE `inbox_id` IN (".$del.") AND `inbox_user`=?;", array($log));
					save_usermail(60);

					$_SESSION['note'] = 'Выбранные сообщения успешно удалены!';
					redirect("private.php?start=$start");

				} else {
					show_error('Ошибка удаления! Отсутствуют выбранные сообщения');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="private.php?start='.$start.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                           Удаление отправленных сообщений                              ##
		############################################################################################
		case 'outdel':

			$uid = check($_GET['uid']);
			if (isset($_POST['del'])) {
				$del = intar($_POST['del']);
			} else {
				$del = 0;
			}

			if ($uid == $_SESSION['token']) {
				if ($del > 0) {
					$del = implode(',', $del);

					DB::run() -> query("DELETE FROM `outbox` WHERE `outbox_id` IN (".$del.") AND `outbox_author`=?;", array($log));

					$_SESSION['note'] = 'Выбранные сообщения успешно удалены!';
					redirect("private.php?act=output&start=$start");

				} else {
					show_error('Ошибка удаления! Отсутствуют выбранные сообщения');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="private.php?act=output&amp;start='.$start.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Очистка входящих сообщений                           ##
		############################################################################################
		case 'alldel':

			$uid = check($_GET['uid']);

			if ($uid == $_SESSION['token']) {
				if (empty($udata['users_newprivat'])) {
					$deltrash = SITETIME + 86400 * $config['expiresmail'];

					DB::run() -> query("DELETE FROM `trash` WHERE `trash_del`<?;", array(SITETIME));

					DB::run() -> query("INSERT INTO `trash` (`trash_user`, `trash_author`, `trash_text`, `trash_time`, `trash_del`) SELECT `inbox_user`, `inbox_author`, `inbox_text`, `inbox_time`, ? FROM `inbox` WHERE `inbox_user`=?;", array($deltrash, $log));

					DB::run() -> query("DELETE FROM `inbox` WHERE `inbox_user`=?;", array($log));
					save_usermail(60);

					$_SESSION['note'] = 'Ящик успешно очищен!';
					redirect("private.php");

				} else {
					show_error('Ошибка! У вас имеются непрочитанные сообщения!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="private.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                           Очистка отправленных сообщений                               ##
		############################################################################################
		case 'alloutdel':

			$uid = check($_GET['uid']);

			if ($uid == $_SESSION['token']) {
				DB::run() -> query("DELETE FROM `outbox` WHERE `outbox_author`=?;", array($log));

				$_SESSION['note'] = 'Ящик успешно очищен!';
				redirect("private.php?act=output");

			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="private.php?act=output">Вернуться</a><br />';
		break;

		############################################################################################
		##                              Очистка удаленных сообщений                               ##
		############################################################################################
		case 'alltrashdel':

			$uid = check($_GET['uid']);

			if ($uid == $_SESSION['token']) {
				DB::run() -> query("DELETE FROM `trash` WHERE `trash_user`=?;", array($log));

				$_SESSION['note'] = 'Ящик успешно очищен!';
				redirect("private.php?act=trash");

			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="private.php?act=trash">Вернуться</a><br />';
		break;

		############################################################################################
		##                                  Просмотр переписки                                    ##
		############################################################################################
		case 'history':

			echo '<img src="/images/img/mail.gif" alt="image" /> <a href="private.php">Входящие</a> / ';
			echo '<a href="private.php?act=output">Отправленные</a> / ';
			echo '<a href="private.php?act=trash">Корзина</a><hr />';

			if ($uz != $log) {
				$queryuser = DB::run() -> querySingle("SELECT `users_id` FROM `users` WHERE `users_login`=? LIMIT 1;", array($uz));
				if (!empty($queryuser)) {
					$total = DB::run() -> query("SELECT count(*) FROM `inbox` WHERE `inbox_user`=? AND `inbox_author`=? UNION ALL SELECT count(*) FROM `outbox` WHERE `outbox_user`=? AND `outbox_author`=?;", array($log, $uz, $uz, $log));

					$total = array_sum($total -> fetchAll(PDO::FETCH_COLUMN));

					if ($total > 0) {
						if ($start >= $total) {
							$start = last_page($total, $config['privatpost']);
						}

						$queryhistory = DB::run() -> query("SELECT * FROM `inbox` WHERE `inbox_user`=? AND `inbox_author`=? UNION ALL SELECT * FROM `outbox` WHERE `outbox_user`=? AND `outbox_author`=? ORDER BY `inbox_time` DESC LIMIT ".$start.", ".$config['privatpost'].";", array($log, $uz, $uz, $log));

						while ($data = $queryhistory -> fetch()) {
							echo '<div class="b">';
							echo user_avatars($data['inbox_author']);
							echo '<b>'.profile($data['inbox_author']).'</b> '.user_online($data['inbox_author']).' ('.date_fixed($data['inbox_time']).')</div>';
							echo '<div>'.bb_code($data['inbox_text']).'</div>';
						}

						page_strnavigation('private.php?act=history&amp;uz='.$uz.'&amp;', $config['privatpost'], $start, $total);

						if (!user_privacy($uz) || is_admin() || is_contact($uz, $log)){

							echo '<br /><div class="form">';
							echo '<form action="private.php?act=send&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'" method="post">';
							echo 'Сообщение:<br />';
							echo '<textarea cols="25" rows="5" name="msg"></textarea><br />';

							if ($udata['users_point'] < $config['privatprotect']) {
								echo 'Проверочный код:<br /> ';
								echo '<img src="/gallery/protect.php" alt="" /><br />';
								echo '<input name="provkod" size="6" maxlength="6" /><br />';
							}

							echo '<input value="Быстрый ответ" type="submit" /></form></div><br />';

						} else {
							show_error('Включен режим приватности, писать могут только пользователи из контактов!');
						}

						echo 'Всего писем: <b>'.(int)$total.'</b><br /><br />';

						echo '<img src="/images/img/reload.gif" alt="image" /> <a href="private.php?act=history&amp;uz='.$uz.'&amp;rand='.mt_rand(100, 999).'">Обновить список</a><br />';
					} else {
						show_error('История переписки отсутствует!');
					}
				} else {
					show_error('Ошибка! Данного адресата не существует!');
				}
			} else {
				show_error('Ошибка! Отсутствует переписка с самим собой!');
			}
		break;

	default:
		redirect("private.php");
	endswitch;

} else {
	show_login('Вы не авторизованы, для просмотра писем, необходимо');
}

echo '<img src="/images/img/search.gif" alt="Поиск" /> <a href="searchuser.php">Поиск контактов</a><br />';
echo '<img src="/images/img/mail.gif" alt="Написать" /> <a href="private.php?act=submit">Написать письмо</a><br />';
echo '<img src="/images/img/users.gif" alt="Контакт" /> <a href="contact.php">Контакт</a> / <a href="ignore.php">Игнор</a><br />';

include_once ('../themes/footer.php');
?>
