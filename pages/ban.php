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

show_title('Бан пользователя');

if (is_user()) {
	if ($udata['users_ban'] == 1) {
		if ($udata['users_timeban'] > SITETIME) {
			switch ($act):
			############################################################################################
			##                                    Главная страница                                    ##
			############################################################################################
				case 'index':

					echo '<img src="/images/img/error.gif" alt="image" /> <b>Вас забанили</b><br /><br />';
					echo '<b><span style="color:#ff0000">Причина бана: '.bb_code($udata['users_reasonban']).'</span></b><br /><br />';

					echo 'До окончания бана осталось <b>'.formattime($udata['users_timeban'] - SITETIME).'</b><br /><br />';

					echo 'Чтобы не терять время зря, рекомендуем вам ознакомиться с <b><a href="/pages/rules.php">Правилами сайта</a></b><br /><br />';

					echo 'Общее число строгих нарушений: <b>'.$udata['users_totalban'].'</b><br />';
					echo 'Внимание, максимальное количество нарушений: <b>5</b><br />';
					echo 'При превышении лимита нарушений ваш профиль автоматически удаляется<br />';
					echo 'Востановление профиля или данных после этого будет невозможным<br />';
					echo 'Будьте внимательны, старайтесь не нарушать больше правил<br /><br />';
					// --------------------------------------------------//
					if ($config['addbansend'] == 1 && $udata['users_explainban'] == 1) {
						echo '<div class="form">';
						echo '<form method="post" action="ban.php?act=send">';
						echo 'Объяснение:<br />';
						echo '<textarea cols="25" rows="5" name="msg"></textarea><br />';
						echo '<input value="Отправить" name="do" type="submit" /></form></div><br />';

						echo 'Если модер вас забанил по ошибке или вы считаете, что бан не заслужен, то вы можете написать объяснение своего нарушения<br />';
						echo 'В случае если ваше объяснение будет рассмотрено и удовлетворено, то возможно вас и разбанят<br /><br />';
					}
				break;

				############################################################################################
				##                                    Отправка объяснения                                 ##
				############################################################################################
				case 'send':

					$msg = check($_POST['msg']);

					if ($config['addbansend'] == 1) {
						if ($udata['users_explainban'] == 1) {
							if (utf_strlen($msg) >= 5 && utf_strlen($msg) < 1000) {
								$queryuser = DB::run() -> querySingle("SELECT `users_id` FROM `users` WHERE `users_login`=? LIMIT 1;", array($udata['users_loginsendban']));
								if (!empty($queryuser)) {
									$msg = no_br($msg);
									$msg = antimat($msg);
									$msg = smiles($msg);

									$textpriv = 'Объяснение нарушения: '.$msg;

									DB::run() -> query("INSERT INTO `inbox` (`inbox_user`, `inbox_author`, `inbox_text`, `inbox_time`) VALUES (?, ?, ?, ?);", array($udata['users_loginsendban'], $log, $textpriv, SITETIME));

									DB::run() -> query("UPDATE `users` SET `users_explainban`=? WHERE `users_login`=?;", array(0, $log));
									DB::run() -> query("UPDATE `users` SET `users_newprivat`=`users_newprivat`+1 WHERE `users_login`=?;", array($udata['users_loginsendban']));

									$_SESSION['note'] = 'Объяснение успешно отправлено!';
									redirect("ban.php");

								} else {
									show_error('Ошибка! Пользователь который вас забанил не найден!');
								}
							} else {
								show_error('Ошибка! Слишком длинное или короткое объяснение!');
							}
						} else {
							show_error('Ошибка! Вы уже писали объяснение!');
						}
					} else {
						show_error('Ошибка! Писать объяснительные запрещено админом!');
					}

					echo '<img src="/images/img/back.gif" alt="image" /> <a href="ban.php">Вернуться</a><br />';
				break;

			default:
				redirect("ban.php");
			endswitch;

		############################################################################################
		##                                    Конец бана                                          ##
		############################################################################################
		} else {
			echo '<img src="/images/img/open.gif" alt="image" /> <b>Срок бана закончился!</b><br /><br />';
			echo '<b><span style="color:#ff0000">Причина бана: '.bb_code($udata['users_reasonban']).'</span></b><br /><br />';

			echo 'Поздравляем!!! Время вашего бана вышло, постарайтесь вести себя достойно и не нарушать правила сайта<br /><br />';

			echo 'Рекомендуем ознакомиться с <b><a href="/pages/rules.php">Правилами сайта</a></b><br />';

			echo 'Также у вас есть возможность исправиться и снять строгое нарушение.<br />';
			echo 'Если прошло более 1 месяца после последнего бана, то на странице <b><a href="/pages/razban.php">Исправительная</a></b> заплатив штраф вы можете снять 1 строгое нарушение<br /><br />';

			DB::run() -> query("UPDATE `users` SET `users_ban`=?, `users_timeban`=?, `users_explainban`=? WHERE `users_login`=?;", array(0, 0, 0, $log));
		}
	} else {
		show_error('Ошибка! Вы не забанены или срок бана истек!');
	}
} else {
	show_error('Ошибка! Вы не авторизованы!');
}

include_once ('../themes/footer.php');
?>
