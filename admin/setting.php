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

if (is_admin(array(101))) {
	show_title('Настройки сайта');

	$queryset = DB::run() -> query("SELECT `setting_name`, `setting_value` FROM `setting`;");
	$setting = $queryset -> fetchAssoc();

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			if ($log == $config['nickname']) {
			  	echo'<img src="/images/img/edit.gif" alt="image" /> <a href="setting.php?act=setzero">Администраторская</a><br />';

				echo'<img src="/images/img/edit.gif" alt="image" /> <a href="setting.php?act=setone">Основные настройки</a><br />';
			}

			echo '<img src="/images/img/edit.gif" alt="image" /> <a href="setting.php?act=settwo">Вывод информации</a><br />';
			echo '<img src="/images/img/edit.gif" alt="image" /> <a href="setting.php?act=setthree">Гостевая / Новости</a><br />';
			echo '<img src="/images/img/edit.gif" alt="image" /> <a href="setting.php?act=setfour">Форум / Галерея / Объявления</a><br />';
			echo '<img src="/images/img/edit.gif" alt="image" /> <a href="setting.php?act=setfive">Закладки / Голосования / Приват</a><br />';
			echo '<img src="/images/img/edit.gif" alt="image" /> <a href="setting.php?act=setload">Загруз-центр</a> / <a href="setting.php?act=setblog">Блоги</a><br />';
			echo '<img src="/images/img/edit.gif" alt="image" /> <a href="setting.php?act=setseven">Постраничная навигация</a><br />';
			echo '<img src="/images/img/edit.gif" alt="image" /> <a href="setting.php?act=seteight">Прочее / Другое</a><br />';
			// echo '<img src="/images/img/edit.gif" alt="image" /> <a href="setting.php?act=setnine">Кэширование</a><br />';
			echo '<img src="/images/img/edit.gif" alt="image" /> <a href="setting.php?act=setten">Защита / Безопасность</a><br />';
			echo '<img src="/images/img/edit.gif" alt="image" /> <a href="setting.php?act=seteleven">Стоимость и цены</a><br />';
			echo '<img src="/images/img/edit.gif" alt="image" /> <a href="setting.php?act=setadv">Реклама на сайте</a><br />';
			echo '<img src="/images/img/edit.gif" alt="image" /> <a href="setting.php?act=setimage">Загрузка изображений</a><br /><br />';
		break;

		############################################################################################
		##                          Форма администраторских настроек                              ##
		############################################################################################
		case 'setzero':
		  	if ($log == $config['nickname']) {
		  		echo '<b>Администраторская</b><br /><hr />';

				echo '<div class="form">';
				echo '<form method="post" action="setting.php?act=editzero&amp;uid='.$_SESSION['token'].'">';

		  		echo 'Логин администратора:<br /><input name="nickname" maxlength="20" value="'.$setting['nickname'].'" /><br />';
				echo 'E-mail администратора:<br /><input name="emails" maxlength="50" value="'.$setting['emails'].'" /><br />';
				echo 'Подтвердите пароль:<br /><input name="pass" type="password" maxlength="20" /><br />';
		  		echo '<input value="Изменить" type="submit" /></form></div><br />';

		  		echo '<b><span style="color:#ff0000">Внимание!</span></b> При изменении логина вы передаете права на управление сайтом другому человеку<br />';
		  		echo 'После этого доступ к данной странице и к основным настройкам вам будет закрыт<br />';
		  		echo 'Будет произведена проверка нового пользователя и назначены ему все администраторские права<br /><br />';

		  	} else {
				show_error('Ошибка! Данные настройки доступны только старшему суперадминистратору!');
			}
			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                         Изменение администраторских настроек                           ##
		############################################################################################
		case 'editzero':

			$uid = check($_GET['uid']);
			$login = check($_POST['nickname']);
			$mail = check($_POST['emails']);
			$pass = check($_POST['pass']);

			if ($log == $config['nickname']) {
				if ($uid == $_SESSION['token']) {
					if (!empty($login) && !empty($mail) && !empty($pass)) {

					  	if (md5(md5($pass)) == $udata['users_pass']) {
							if (preg_match('#^([a-z0-9_\-\.])+\@([a-z0-9_\-\.])+(\.([a-z0-9])+)+$#', $mail)) {

					  			$queryuser = DB::run()->queryFetch("SELECT * FROM `users` WHERE `users_login`=? LIMIT 1;", array($login));
					  			if (!empty($queryuser)){

									if ($login!=$setting['nickname']){
										DB::run() -> query("UPDATE `users` SET `users_level`=? WHERE `users_login`=? LIMIT 1;", array(101, $login));
									}

									$dbr = DB::run() -> prepare("UPDATE `setting` SET `setting_value`=? WHERE `setting_name`=?;");
									$dbr -> execute($login, 'nickname');
									$dbr -> execute($mail, 'emails');
									$dbr -> execute($pass, 'pass');

									save_setting();

									notice('Настройки сайта успешно изменены!');
									redirect("setting.php?act=setzero");

								} else {
									show_error('Ошибка! Аккаунт пользователя '.$login.' не найден в базе');
								}
							} else {
								show_error('Неправильный адрес e-mail, необходим формат name@site.domen!');
							}
						} else {
							show_error('Ошибка! Пароль не совпадает с данными в профиле!');
						}
					} else {
						show_error('Ошибка! Все поля настроек обязательны для заполнения!');
					}
				} else {
					show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
				}
			} else {
				show_error('Ошибка! Данные настройки доступны только старшему суперадминистратору!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php?act=setzero">Вернуться</a><br />';
		break;

		############################################################################################
		##                               Форма основных настроек                                  ##
		############################################################################################
		case 'setone':

			if ($log == $config['nickname']) {
				echo '<b>Основные настройки</b><br /><hr />';

				echo '<div class="form">';
				echo '<form method="post" action="setting.php?act=editone&amp;uid='.$_SESSION['token'].'">';

				echo 'Заголовок всех страниц:<br /><input name="title" maxlength="100" value="'.$setting['title'].'" /><br />';
				echo 'Подпись вверху:<br /><input name="logos" maxlength="100" value="'.$setting['logos'].'" /><br />';
				echo 'Подпись внизу:<br /><input name="copy" maxlength="100" value="'.$setting['copy'].'" /><br />';
				echo 'Главная страница сайта:<br /><input name="home" maxlength="50" value="'.$setting['home'].'" /><br />';
				echo 'Адрес логотипа:<br /><input name="logotip" maxlength="100" value="'.$setting['logotip'].'" /><br />';
				echo 'Время антифлуда (сек):<br /><input name="floodstime" maxlength="3" value="'.$setting['floodstime'].'" /><br />';
				echo 'Ключ для паролей:<br /><input name="keypass" maxlength="25" value="'.$setting['keypass'].'" /><br />';
				echo 'Лимит запросов с IP (0 - Выкл):<br /><input name="doslimit" maxlength="3" value="'.$setting['doslimit'].'" /><br />';

				echo 'Временная зона:<br /><input name="timezone" maxlength="50" value="'.$setting['timezone'].'" /><br />';

				# ----------------------------------------#
				echo 'Wap-тема по умолчанию:<br />';
				echo '<select name="themes">';

				$globthemes = glob(BASEDIR."/themes/*", GLOB_ONLYDIR);

				foreach ($globthemes as $wapthemes) {
					$selected = (basename($wapthemes) == $setting['themes']) ? ' selected="selected"' : '';

					echo '<option value="'.basename($wapthemes).'"'.$selected.'>'.basename($wapthemes).'</option>';
				}
				echo '</select><br />';

				# ----------------------------------------#
				echo 'Web-тема по умолчанию:<br />';
				echo '<select name="webthemes">';

				$globthemes = glob(BASEDIR."/themes/*", GLOB_ONLYDIR);

				echo '<option value="0">Выключить</option>';

				foreach ($globthemes as $webthemes) {
					$selected = (basename($webthemes) == $setting['webthemes']) ? ' selected="selected"' : '';

					echo '<option value="'.basename($webthemes).'"'.$selected.'>'.basename($webthemes).'</option>';
				}
				echo '</select><br />';

				# ----------------------------------------#
				echo 'Touch-тема по умолчанию:<br />';
				echo '<select name="touchthemes">';

				$globthemes = glob(BASEDIR."/themes/*", GLOB_ONLYDIR);

				echo '<option value="0">Выключить</option>';

				foreach ($globthemes as $touchthemes) {
					$selected = (basename($touchthemes) == $setting['touchthemes']) ? ' selected="selected"' : '';

					echo '<option value="'.basename($touchthemes).'"'.$selected.'>'.basename($touchthemes).'</option>';
				}
				echo '</select><br />';

				# ----------------------------------------#
				echo 'Время карантина:<br />';
				echo '<select name="karantin">';
				$karantin = array(0 => 'Выключить', 21600 => '6 часов', 43200 => '12 часов', 86400 => '24 часа', 129600 => '36 часов', 172800 => '48 часов');

				foreach($karantin as $k => $v) {
					$selected = ($k == $setting['karantin']) ? ' selected="selected"' : '';

					echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>';
				}
				echo '</select><br />';

				echo 'Подтверждение регистрации:<br />';
				echo '<select name="regkeys">';
				$regist = array(0 => 'Выключить', 1 => 'Автоматически', 2 => 'Вручную');

				foreach($regist as $k => $v) {
					$selected = ($k == $setting['regkeys']) ? ' selected="selected"' : '';

					echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>';
				}
				echo '</select><br />';

				echo 'Доступ к сайту:<br />';
				echo '<select name="closedsite">';
				$statsite = array(0 => 'Сайт открыт', 1 => 'Закрыто для гостей', 2 => 'Закрыто для всех');

				foreach($statsite as $k => $v) {
					$selected = ($k == $setting['closedsite']) ? ' selected="selected"' : '';

					echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>';
				}
				echo '</select><br />';

				$checked = ($setting['openreg'] == 1) ? ' checked="checked"' : '';
				echo '<input name="openreg" type="checkbox" value="1"'.$checked.' /> Разрешить регистрацию<br />';

				$checked = ($setting['invite'] == 1) ? ' checked="checked"' : '';
				echo '<input name="invite" id="invite" type="checkbox" value="1"'.$checked.' title="Для регистрация необходимо ввести специальный пригласительный ключ" /> <label for="invite">Регистрация по приглашениям</label><br />';

				$checked = ($setting['regmail'] == 1) ? ' checked="checked"' : '';
				echo '<input name="regmail" type="checkbox" value="1"'.$checked.' /> Запрос email при регистрации<br />';

				$checked = ($setting['sendmail'] == 1) ? ' checked="checked"' : '';
				echo '<input name="sendmail" type="checkbox" value="1"'.$checked.' /> Связь с админом в приват<br />';

				$checked = ($setting['cache'] == 1) ? ' checked="checked"' : '';
				echo '<input name="cache" type="checkbox" value="1"'.$checked.' /> Включить кэширование страниц<br />';

				$checked = ($setting['gzip'] == 1) ? ' checked="checked"' : '';
				echo '<input name="gzip" type="checkbox" value="1"'.$checked.' /> Включить сжатие GZIP<br />';

				$checked = ($setting['anonymity'] == 1) ? ' checked="checked"' : '';
				echo '<input name="anonymity" type="checkbox" value="1"'.$checked.' /> Анонимность пользователей<br />';

				$checked = ($setting['session'] == 1) ? ' checked="checked"' : '';
				echo '<input name="session" type="checkbox" value="1"'.$checked.' /> Контроль сессий<br />';

				echo '<input value="Изменить" type="submit" /></form></div><br />';
			} else {
				show_error('Ошибка! Основные настройки доступны только старшему суперадминистратору!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                Изменение основных настроек                             ##
		############################################################################################
		case 'editone':

			$uid = check($_GET['uid']);
			$regmail = (empty($_POST['regmail'])) ? 0 : 1;
			$invite = (empty($_POST['invite'])) ? 0 : 1;
			$sendmail = (empty($_POST['sendmail'])) ? 0 : 1;
			$cache = (empty($_POST['cache'])) ? 0 : 1;
			$openreg = (empty($_POST['openreg'])) ? 0 : 1;
			$gzip = (empty($_POST['gzip'])) ? 0 : 1;
			$anonymity = (empty($_POST['anonymity'])) ? 0 : 1;
			$session = (empty($_POST['session'])) ? 0 : 1;
			$regkeys = (isset($_POST['regkeys'])) ? abs(intval($_POST['regkeys'])) : 0;
			$closedsite = (isset($_POST['closedsite'])) ? abs(intval($_POST['closedsite'])) : 0;

			if ($log == $config['nickname']) {
				if ($uid == $_SESSION['token']) {
					if ($_POST['title'] != "" && $_POST['copy'] != "" && $_POST['home'] != "" && $_POST['logotip'] != "" && $_POST['floodstime'] != "" && $_POST['keypass'] != "" && $_POST['doslimit'] != "" && $_POST['timezone'] != "" && $_POST['themes'] != "" && $_POST['webthemes'] != "" && $_POST['touchthemes'] != "" && $_POST['karantin'] != "") {

						$dbr = DB::run() -> prepare("UPDATE `setting` SET `setting_value`=? WHERE `setting_name`=?;");
						$dbr -> execute(check($_POST['title']), 'title');
						$dbr -> execute(check($_POST['logos']), 'logos');
						$dbr -> execute(check($_POST['copy']), 'copy');
						$dbr -> execute(check($_POST['home']), 'home');
						$dbr -> execute(check($_POST['logotip']), 'logotip');
						$dbr -> execute(intval($_POST['floodstime']), 'floodstime');
						$dbr -> execute(check($_POST['keypass']), 'keypass');
						$dbr -> execute(intval($_POST['doslimit']), 'doslimit');
						$dbr -> execute(check($_POST['timezone']), 'timezone');
						$dbr -> execute(check($_POST['themes']), 'themes');
						$dbr -> execute(check($_POST['webthemes']), 'webthemes');
						$dbr -> execute(check($_POST['touchthemes']), 'touchthemes');
						$dbr -> execute(intval($_POST['karantin']), 'karantin');
						$dbr -> execute($regkeys, 'regkeys');
						$dbr -> execute($regmail, 'regmail');
						$dbr -> execute($invite, 'invite');
						$dbr -> execute($sendmail, 'sendmail');
						$dbr -> execute($cache, 'cache');
						$dbr -> execute($openreg, 'openreg');
						$dbr -> execute($gzip, 'gzip');
						$dbr -> execute($anonymity, 'anonymity');
						$dbr -> execute($closedsite, 'closedsite');
						$dbr -> execute($session, 'session');

						save_setting();

						notice('Настройки сайта успешно изменены!');
						redirect("setting.php?act=setone");

					} else {
						show_error('Ошибка! Все поля настроек обязательны для заполнения!');
					}
				} else {
					show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
				}
			} else {
				show_error('Ошибка! Основные настройки доступны только старшему суперадминистратору!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php?act=setone">Вернуться</a><br />';
		break;

		############################################################################################
		##                                Форма вывода информации                                 ##
		############################################################################################
		case 'settwo':

			echo '<b>Вывод информации</b><br /><hr />';

			echo '<div class="form">';
			echo '<form method="post" action="setting.php?act=edittwo&amp;uid='.$_SESSION['token'].'">';

			echo 'Отображение счетчика:<br /><select name="incount">';

			$incounters = array('Выключить', 'Хосты | Хосты всего', 'Хиты | Хиты всего', 'Хиты | Хосты', 'Хиты всего | Хосты всего', 'Графический');

			foreach($incounters as $k => $v) {
				$selected = ($k == $setting['incount']) ? ' selected="selected"' : '';

				echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>';
			}
			echo '</select><br />';

			echo 'Быстрый переход:<br /><select name="navigation">';

			$innav = array('Выключить', 'Обычный список', 'Список без кнопки');

			foreach($innav as $k => $v) {
				$selected = ($k == $setting['navigation']) ? ' selected="selected"' : '';

				echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>';
			}
			echo '</select><br />';

			$checked = ($setting['performance'] == 1) ? ' checked="checked"' : '';
			echo '<input name="performance" type="checkbox" value="1"'.$checked.' /> Производительность<br />';

			$checked = ($setting['onlines'] == 1) ? ' checked="checked"' : '';
			echo '<input name="onlines" type="checkbox" value="1"'.$checked.' /> Онлайн<br />';

			echo '<input value="Изменить" type="submit" /></form></div><br />';
			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                              Изменение вывода информации                               ##
		############################################################################################
		case 'edittwo':

			$uid = check($_GET['uid']);
			$performance = (empty($_POST['performance'])) ? 0 : 1;
			$onlines = (empty($_POST['onlines'])) ? 0 : 1;

			if ($uid == $_SESSION['token']) {
				if ($_POST['incount'] != "" && $_POST['navigation'] != "") {
					$dbr = DB::run() -> prepare("UPDATE `setting` SET `setting_value`=? WHERE `setting_name`=?;");
					$dbr -> execute(intval($_POST['incount']), 'incount');
					$dbr -> execute(intval($_POST['navigation']), 'navigation');
					$dbr -> execute($performance, 'performance');
					$dbr -> execute($onlines, 'onlines');

					save_setting();

					notice('Настройки сайта успешно изменены!');
					redirect("setting.php?act=settwo");

				} else {
					show_error('Ошибка! Все поля настроек обязательны для заполнения!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php?act=settwo">Вернуться</a><br />';
		break;

		############################################################################################
		##                          Форма изменения гостевой и новостей                           ##
		############################################################################################
		case 'setthree':

			echo '<b>Настройки гостевой и новостей</b><br /><hr />';

			echo '<div class="form">';
			echo '<form method="post" action="setting.php?act=editthree&amp;uid='.$_SESSION['token'].'">';


			echo '<b>Новости</b><br />';
			echo 'Макс. кол. новостей на главной: <br />';

			$innews = array('Не выводить', '1 новость', '2 новости', '3 новости', '4 новости', '5 новостей');
			echo '<select name="lastnews">';

			foreach ($innews as $k => $v) {
				$selected = ($k == $setting['lastnews']) ? ' selected="selected"' : '';

				echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>';
			}
			echo '</select><br />';

			echo 'Новостей на страницу:<br /><input name="postnews" maxlength="2" value="'.$setting['postnews'].'" title="Сколько новостей выводить на 1 страницу (По умолчанию: 10)" /><br />';

			echo 'Кол-во комментариев в новостях сохраняется:<br /><input name="maxkommnews" maxlength="4" value="'.$setting['maxkommnews'].'" title="Сколько сохранять комментарий к новостям (По умолчанию: 500)" /><br />';

			echo '<hr /><b>Гостевая</b><br />';
			$checked = ($setting['bookadds'] == 1) ? ' checked="checked"' : '';
			echo '<input name="bookadds" id="bookadds" type="checkbox" value="1"'.$checked.' title="Разрешить гостям писать в гостевой" /> <label for="bookadds">Допуск гостей</label><br />';

			$checked = ($setting['bookscores'] == 1) ? ' checked="checked"' : '';
			echo '<input name="bookscores" id="bookscores" type="checkbox" value="1"'.$checked.' title="При добавлении сообщений в гостевую начисляются баллы" /> <label for="bookscores">Начисление баллов</label><br />';

			echo 'Сообщений в гостевой на стр.:<br /><input name="bookpost" maxlength="2" value="'.$setting['bookpost'].'" title="Сколько сообщений выводить на 1 страницу (По умолчанию: 10)" /><br />';
			echo 'Неавторизованный пользователь:<br /><input name="guestsuser" maxlength="20" value="'.$setting['guestsuser'].'" title="Как подписывать пользователя если он не авторизован (По умолчанию: Гость)" /><br />';

			echo 'Кол-во постов в гостевой сохраняется:<br /><input name="maxpostbook" maxlength="4" value="'.$setting['maxpostbook'].'" title="Сколько сохранять сообщений в гостевой (По умолчанию: 500)" /><br />';
			echo 'Символов в сообщении гостевой:<br /><input name="guesttextlength" maxlength="5" value="'.$setting['guesttextlength'].'" title="Максимальное количество символов в сообщении (По умолчанию: 1000)" /><br />';

			echo '<hr /><b>Админ-чат</b><br />';
			echo 'Сообщений в админ-чате на стр.:<br /><input name="chatpost" maxlength="2" value="'.$setting['chatpost'].'" title="Сколько сообщений выводить на 1 страницу (По умолчанию: 10)" /><br />';
			echo 'Общее кол-во сообщений в админ-чате:<br /><input name="maxpostchat" maxlength="4" value="'.$setting['maxpostchat'].'" title="Сколько всего сообщений сохраняется в админ-чате (По умолчанию: 500)" /><br />';

			echo '<input value="Изменить" type="submit" /></form></div><br />';

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                           Изменение в гостевой и новостях                              ##
		############################################################################################
		case 'editthree':

			$uid = check($_GET['uid']);
			$bookadds = (empty($_POST['bookadds'])) ? 0 : 1;
			$bookscores = (empty($_POST['bookscores'])) ? 0 : 1;

			if ($uid == $_SESSION['token']) {
				if ($_POST['lastnews'] != "" && $_POST['postnews'] != "" && $_POST['bookpost'] != "" && $_POST['guestsuser'] != "" && $_POST['maxkommnews'] != "" && $_POST['maxpostbook'] != "" && $_POST['guesttextlength'] != "" && $_POST['chatpost'] != "" && $_POST['maxpostchat'] != "") {
					$dbr = DB::run() -> prepare("UPDATE `setting` SET `setting_value`=? WHERE `setting_name`=?;");
					$dbr -> execute(intval($_POST['lastnews']), 'lastnews');
					$dbr -> execute(intval($_POST['postnews']), 'postnews');
					$dbr -> execute(intval($_POST['bookpost']), 'bookpost');
					$dbr -> execute(check($_POST['guestsuser']), 'guestsuser');
					$dbr -> execute(intval($_POST['maxkommnews']), 'maxkommnews');
					$dbr -> execute(intval($_POST['maxpostbook']), 'maxpostbook');
					$dbr -> execute(intval($_POST['guesttextlength']), 'guesttextlength');
					$dbr -> execute(intval($_POST['chatpost']), 'chatpost');
					$dbr -> execute(intval($_POST['maxpostchat']), 'maxpostchat');
					$dbr -> execute($bookadds, 'bookadds');
					$dbr -> execute($bookscores, 'bookscores');

					save_setting();

					notice('Настройки сайта успешно изменены!');
					redirect("setting.php?act=setthree");

				} else {
					show_error('Ошибка! Все поля настроек обязательны для заполнения!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php?act=setthree">Вернуться</a><br />';
		break;

		############################################################################################
		##                     Форма изменения форума, галереи и объявлений                       ##
		############################################################################################
		case 'setfour':

			echo '<b>Настройки форума, галереи и объявлений</b><br /><hr />';

			echo '<div class="form">';
			echo '<form method="post" action="setting.php?act=editfour&amp;uid='.$_SESSION['token'].'">';
			echo '<b>Форум</b><br />';
			echo 'Сообщений в форуме на стр.:<br /><input name="forumpost" maxlength="2" value="'.$setting['forumpost'].'" /><br />';
			echo 'Тем в форуме на стр.:<br /><input name="forumtem" maxlength="2" value="'.$setting['forumtem'].'" /><br />';
			echo 'Символов в сообщении форума:<br /><input name="forumtextlength" maxlength="5" value="'.$setting['forumtextlength'].'" /><br />';

			echo 'Максимальный вес файла (Kb): <br /><input name="forumloadsize" maxlength="6" value="'.round($setting['forumloadsize'] / 1024).'" /><br />';
			echo 'Допустимые расширения файлов:<br /><textarea cols="25" rows="5" name="forumextload">'.$setting['forumextload'].'</textarea><br />';
			echo 'Актива для загрузки файлов: <br /><input name="forumloadpoints" maxlength="4" value="'.$setting['forumloadpoints'].'" /><br /><hr />';

			echo '<b>Объявления</b><br />';
			echo 'Объявлений на стр.:<br /><input name="boardspost" maxlength="2" value="'.$setting['boardspost'].'" /><br />';
			echo 'Кол-во дней показа объявлений:<br /><input name="boarddays" maxlength="3" value="'.$setting['boarddays'].'" /><br /><hr />';

			echo '<b>Галерея</b><br />';
			echo 'Kол-во фото на стр.:<br /><input name="fotolist" maxlength="2" value="'.$setting['fotolist'].'" /><br />';
			echo 'Комментариев на страницу в галерее:<br /><input name="postgallery" maxlength="3" value="'.$setting['postgallery'].'" /><br />';
			echo 'Комментариев сохраняется в галерее:<br /><input name="maxpostgallery" maxlength="3" value="'.$setting['maxpostgallery'].'" /><br />';
			echo 'Хранение голосований (часов):<br /><input name="photoexprated" maxlength="3" value="'.$setting['photoexprated'].'" /><br />';
			echo 'Группы в галереях:<br /><input name="photogroup" maxlength="2" value="'.$setting['photogroup'].'" /><br />';

			echo '<input value="Изменить" type="submit" /></form></div><br />';
			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                     Изменение в форуме, галерее и объявлениях                          ##
		############################################################################################
		case 'editfour':

			$uid = check($_GET['uid']);

			if ($uid == $_SESSION['token']) {
				if ($_POST['forumpost'] != "" && $_POST['forumtem'] != "" && $_POST['forumtextlength'] != "" && $_POST['forumloadsize'] != "" && $_POST['forumextload'] != "" && $_POST['forumloadpoints'] != "" && $_POST['boardspost'] != "" && $_POST['boarddays'] != "" && $_POST['fotolist'] != "" && $_POST['postgallery'] != "" && $_POST['maxpostgallery'] != "" && $_POST['photoexprated'] != "" && $_POST['photogroup'] != "") {
					$dbr = DB::run() -> prepare("UPDATE `setting` SET `setting_value`=? WHERE `setting_name`=?;");
					$dbr -> execute(intval($_POST['forumpost']), 'forumpost');
					$dbr -> execute(intval($_POST['forumtem']), 'forumtem');
					$dbr -> execute(intval($_POST['forumtextlength']), 'forumtextlength');
					$dbr -> execute(intval($_POST['forumloadsize'] * 1024), 'forumloadsize');
					$dbr -> execute(check($_POST['forumextload']), 'forumextload');
					$dbr -> execute(intval($_POST['forumloadpoints']), 'forumloadpoints');
					$dbr -> execute(intval($_POST['boardspost']), 'boardspost');
					$dbr -> execute(intval($_POST['boarddays']), 'boarddays');
					$dbr -> execute(intval($_POST['fotolist']), 'fotolist');
					$dbr -> execute(intval($_POST['postgallery']), 'postgallery');
					$dbr -> execute(intval($_POST['maxpostgallery']), 'maxpostgallery');
					$dbr -> execute(intval($_POST['photoexprated']), 'photoexprated');
					$dbr -> execute(intval($_POST['photogroup']), 'photogroup');

					save_setting();

					notice('Настройки сайта успешно изменены!');
					redirect("setting.php?act=setfour");

				} else {
					show_error('Ошибка! Все поля настроек обязательны для заполнения!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php?act=setfour">Вернуться</a><br />';
		break;

		############################################################################################
		##                    Форма изменения закладок, голосований и привата                     ##
		############################################################################################
		case 'setfive':

			echo '<b>Настройки закладок, голосований и привата</b><br /><hr />';

			echo '<div class="form">';
			echo '<form method="post" action="setting.php?act=editfive&amp;uid='.$_SESSION['token'].'">';

			echo 'Кол. писем во входящих:<br /><input name="limitmail" maxlength="3" value="'.$setting['limitmail'].'" /><br />';
			echo 'Кол. писем в отправленных:<br /><input name="limitoutmail" maxlength="3" value="'.$setting['limitoutmail'].'" /><br />';
			echo 'Срок хранения удаленных писем:<br /><input name="expiresmail" maxlength="2" value="'.$setting['expiresmail'].'" /><br />';
			echo 'Писем в привате на стр.:<br /><input name="privatpost" maxlength="2" value="'.$setting['privatpost'].'" /><br />';
			echo 'Порог выключения защитной картинки:<br /><input name="privatprotect" maxlength="4" value="'.$setting['privatprotect'].'" /><br />';
			echo 'Листинг в контакт-листе:<br /><input name="contactlist" maxlength="2" value="'.$setting['contactlist'].'" /><br />';
			echo 'Листинг в игнор-листе:<br /><input name="ignorlist" maxlength="2" value="'.$setting['ignorlist'].'" /><br />';
			echo 'Максимальное кол. в контакт-листе:<br /><input name="limitcontact" maxlength="2" value="'.$setting['limitcontact'].'" /><br />';
			echo 'Максимальное кол. в игнор-листе:<br /><input name="limitignore" maxlength="2" value="'.$setting['limitignore'].'" /><br />';
			echo 'Кол-во голосований на стр.:<br /><input name="allvotes" maxlength="2" value="'.$setting['allvotes'].'" /><br />';

			echo '<input value="Изменить" type="submit" /></form></div><br />';
			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                  Изменение в закладках, голосованиях и привате                         ##
		############################################################################################
		case 'editfive':

			$uid = check($_GET['uid']);

			if ($uid == $_SESSION['token']) {
				if ($_POST['limitmail'] != "" && $_POST['limitoutmail'] != "" && $_POST['expiresmail'] != "" && $_POST['privatpost'] != "" && $_POST['privatprotect'] != "" && $_POST['contactlist'] != "" && $_POST['ignorlist'] != "" && $_POST['limitcontact'] != "" && $_POST['limitignore'] != "" && $_POST['allvotes'] != "") {
					$dbr = DB::run() -> prepare("UPDATE `setting` SET `setting_value`=? WHERE `setting_name`=?;");
					$dbr -> execute(intval($_POST['limitmail']), 'limitmail');
					$dbr -> execute(intval($_POST['limitoutmail']), 'limitoutmail');
					$dbr -> execute(intval($_POST['expiresmail']), 'expiresmail');
					$dbr -> execute(intval($_POST['privatpost']), 'privatpost');
					$dbr -> execute(intval($_POST['privatprotect']), 'privatprotect');
					$dbr -> execute(intval($_POST['contactlist']), 'contactlist');
					$dbr -> execute(intval($_POST['ignorlist']), 'ignorlist');
					$dbr -> execute(intval($_POST['limitcontact']), 'limitcontact');
					$dbr -> execute(intval($_POST['limitignore']), 'limitignore');
					$dbr -> execute(intval($_POST['allvotes']), 'allvotes');

					save_setting();

					notice('Настройки сайта успешно изменены!');
					redirect("setting.php?act=setfive");

				} else {
					show_error('Ошибка! Все поля настроек обязательны для заполнения!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php?act=setfive">Вернуться</a><br />';
		break;

		############################################################################################
		##                             Форма изменения загруз-центра                              ##
		############################################################################################
		case 'setload':

			echo '<b>Настройки загруз-центра</b><br /><hr />';

			echo '<div class="form">';
			echo '<form method="post" action="setting.php?act=editload&amp;uid='.$_SESSION['token'].'">';

			echo 'Файлов в загрузках:<br /><input name="downlist" maxlength="2" value="'.$setting['downlist'].'" /><br />';
			echo 'Комментариев в загрузках:<br /><input name="downcomm" maxlength="2" value="'.$setting['downcomm'].'" /><br />';
			echo 'Сохраняется комментариев:<br /><input name="maxdowncomm" maxlength="4" value="'.$setting['maxdowncomm'].'" /><br />';
			echo 'Хранение скачиваний (часов):<br /><input name="expiresloads" maxlength="3" value="'.$setting['expiresloads'].'" /><br />';
			echo 'Хранение голосований (часов):<br /><input name="expiresrated" maxlength="3" value="'.$setting['expiresrated'].'" /><br />';
			echo 'Просмотр архивов на стр.:<br /><input name="ziplist" maxlength="2" value="'.$setting['ziplist'].'" /><br />';
			echo 'Максимальный вес файла (Kb):<br /><input name="fileupload" maxlength="6" value="'.round($setting['fileupload'] / 1024).'" /><br />';

			echo 'Максимальный вес скриншота (Kb) (Ограничение: '.ini_get('upload_max_filesize').'):<br /><input name="screenupload" maxlength="6" value="'.round($setting['screenupload'] / 1024).'" /><br />';
			echo 'Максимальный размер скриншота (px):<br /><input name="screenupsize" maxlength="6" value="'.$setting['screenupsize'].'" /><br />';
			echo 'Допустимые расширения файлов:<br /><textarea cols="25" rows="5" name="allowextload">'.$setting['allowextload'].'</textarea><br />';

			$checked = ($setting['downupload'] == 1) ? ' checked="checked"' : '';
			echo '<input name="downupload" type="checkbox" value="1"'.$checked.' /> Разрешать загружать файлы пользователям<br />';

			echo '<input value="Изменить" type="submit" /></form></div><br />';
			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                Изменение в загруз-центре                               ##
		############################################################################################
		case 'editload':

			$uid = check($_GET['uid']);
			$downupload = (empty($_POST['downupload'])) ? 0 : 1;

			if ($uid == $_SESSION['token']) {
				if ($_POST['downlist'] != "" && $_POST['downcomm'] != "" && $_POST['maxdowncomm'] != "" && $_POST['expiresloads'] != "" && $_POST['expiresrated'] != "" && $_POST['ziplist'] != "" && $_POST['fileupload'] != "" && $_POST['screenupload'] != "" && $_POST['screenupsize'] != "" && $_POST['allowextload'] != "") {
					$dbr = DB::run() -> prepare("UPDATE `setting` SET `setting_value`=? WHERE `setting_name`=?;");
					$dbr -> execute(intval($_POST['downlist']), 'downlist');
					$dbr -> execute(intval($_POST['downcomm']), 'downcomm');
					$dbr -> execute(intval($_POST['maxdowncomm']), 'maxdowncomm');
					$dbr -> execute(intval($_POST['expiresloads']), 'expiresloads');
					$dbr -> execute(intval($_POST['expiresrated']), 'expiresrated');
					$dbr -> execute(intval($_POST['ziplist']), 'ziplist');
					$dbr -> execute(intval($_POST['fileupload'] * 1024), 'fileupload');
					$dbr -> execute(intval($_POST['screenupload'] * 1024), 'screenupload');
					$dbr -> execute(intval($_POST['screenupsize']), 'screenupsize');
					$dbr -> execute(check($_POST['allowextload']), 'allowextload');
					$dbr -> execute($downupload, 'downupload');

					save_setting();

					notice('Настройки сайта успешно изменены!');
					redirect("setting.php?act=setload");

				} else {
					show_error('Ошибка! Все поля настроек обязательны для заполнения!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php?act=setload">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Форма изменения блогов                               ##
		############################################################################################
		case 'setblog':

			echo '<b>Настройки блогов</b><br /><hr />';

			echo '<div class="form">';
			echo '<form method="post" action="setting.php?act=editblog&amp;uid='.$_SESSION['token'].'">';

			echo 'Статей на страницу:<br /><input name="blogpost" maxlength="2" value="'.$setting['blogpost'].'" /><br />';
			echo 'Комментариев в блогах:<br /><input name="blogcomm" maxlength="2" value="'.$setting['blogcomm'].'" /><br />';
			echo 'Сохраняется комментариев:<br /><input name="maxblogcomm" maxlength="4" value="'.$setting['maxblogcomm'].'" /><br />';
			echo 'Хранение прочтений (часов):<br /><input name="blogexpread" maxlength="3" value="'.$setting['blogexpread'].'" /><br />';
			echo 'Хранение голосований (часов):<br /><input name="blogexprated" maxlength="3" value="'.$setting['blogexprated'].'" /><br />';
			echo 'Группы блогов:<br /><input name="bloggroup" maxlength="2" value="'.$setting['bloggroup'].'" /><br />';
			echo 'Кол. символов в статье:<br /><input name="maxblogpost" maxlength="6" value="'.$setting['maxblogpost'].'" /><br />';

			echo 'Актива для голосования за статьи: <br /><input name="blogvotepoint" maxlength="3" value="'.$setting['blogvotepoint'].'" /><br />';

			echo '<input value="Изменить" type="submit" /></form></div><br />';
			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                     Изменение в блогах                                 ##
		############################################################################################
		case 'editblog':

			$uid = check($_GET['uid']);

			if ($uid == $_SESSION['token']) {
				if ($_POST['blogpost'] != "" && $_POST['blogcomm'] != "" && $_POST['maxblogcomm'] != "" && $_POST['blogexpread'] != "" && $_POST['blogexprated'] != "" && $_POST['bloggroup'] != "" && $_POST['maxblogpost'] != "" && $_POST['blogvotepoint'] != "") {
					$dbr = DB::run() -> prepare("UPDATE `setting` SET `setting_value`=? WHERE `setting_name`=?;");
					$dbr -> execute(intval($_POST['blogpost']), 'blogpost');
					$dbr -> execute(intval($_POST['blogcomm']), 'blogcomm');
					$dbr -> execute(intval($_POST['maxblogcomm']), 'maxblogcomm');
					$dbr -> execute(intval($_POST['blogexpread']), 'blogexpread');
					$dbr -> execute(intval($_POST['blogexprated']), 'blogexprated');
					$dbr -> execute(intval($_POST['bloggroup']), 'bloggroup');
					$dbr -> execute(intval($_POST['maxblogpost']), 'maxblogpost');
					$dbr -> execute(intval($_POST['blogvotepoint']), 'blogvotepoint');

					save_setting();

					notice('Настройки сайта успешно изменены!');
					redirect("setting.php?act=setblog");

				} else {
					show_error('Ошибка! Все поля настроек обязательны для заполнения!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php?act=setblog">Вернуться</a><br />';
		break;

		############################################################################################
		##                           Форма изменения постраничной навигации                       ##
		############################################################################################
		case 'setseven':

			echo '<b>Настройки постраничной навигации</b><br /><hr />';

			echo '<div class="form">';
			echo '<form method="post" action="setting.php?act=editseven&amp;uid='.$_SESSION['token'].'">';

			echo 'Пользователей в юзерлисте:<br /><input name="userlist" maxlength="2" value="'.$setting['userlist'].'" /><br />';
			echo 'Пользователей в кто-где:<br /><input name="showuser" maxlength="2" value="'.$setting['showuser'].'" /><br />';
			echo 'Сохраняется истории в кто-где:<br /><input name="lastusers" maxlength="3" value="'.$setting['lastusers'].'" /><br />';
			echo 'Сайтов в кто-откуда<br /><input name="showref" maxlength="3" value="'.$setting['showref'].'" /><br />';
			echo 'Сохраняется истории в кто-откуда:<br /><input name="referer" maxlength="3" value="'.$setting['referer'].'" /><br />';
			echo 'Ссылок пирамиды на главной:<br /><input name="showlink" maxlength="2" value="'.$setting['showlink'].'" /><br />';
			echo 'Пользователей в онлайне:<br /><input name="onlinelist" maxlength="2" value="'.$setting['onlinelist'].'" /><br />';
			echo 'Смайлов на стр.:<br /><input name="smilelist" maxlength="2" value="'.$setting['smilelist'].'" /><br />';
			echo 'Юзеров в рейтинге авторитетов на стр.:<br /><input name="avtorlist" maxlength="2" value="'.$setting['avtorlist'].'" /><br />';
			echo 'Юзеров в рейтинге долгожителей:<br /><input name="lifelist" maxlength="2" value="'.$setting['lifelist'].'" /><br />';
			echo 'Юзеров в списке забаненных:<br /><input name="banlist" maxlength="2" value="'.$setting['banlist'].'" /><br />';
			echo 'Листинг в IP-бан панеле:<br /><input name="ipbanlist" maxlength="2" value="'.$setting['ipbanlist'].'" /><br />';
			echo 'Заголовков на страницу:<br /><input name="headlines" maxlength="2" value="'.$setting['headlines'].'" /><br />';
			echo 'Аватаров на страницу:<br /><input name="avlist" maxlength="2" value="'.$setting['avlist'].'" /><br />';
			echo 'Файлов в редакторе админки:<br /><input name="editfiles" maxlength="2" value="'.$setting['editfiles'].'" /><br />';
			echo 'Просмотр логов на страницу:<br /><input name="loglist" maxlength="2" value="'.$setting['loglist'].'" /><br />';
			echo 'Данных на страницу в черном списке:<br /><input name="blacklist" maxlength="2" value="'.$setting['blacklist'].'" /><br />';
			echo 'Пользователей в списке ожидающих:<br /><input name="reglist" maxlength="2" value="'.$setting['reglist'].'" /><br />';
			echo 'Листинг в списке обновлений:<br /><input name="postchanges" maxlength="2" value="'.$setting['postchanges'].'" /><br />';

			echo '<input value="Изменить" type="submit" /></form></div><br />';

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                               Изменение постраничной навигации                         ##
		############################################################################################
		case 'editseven':

			$uid = check($_GET['uid']);

			if ($uid == $_SESSION['token']) {
				if ($_POST['userlist'] != "" && $_POST['showuser'] != "" && $_POST['lastusers'] != "" && $_POST['showref'] != "" && $_POST['referer'] != "" && $_POST['showlink'] != "" && $_POST['onlinelist'] != "" && $_POST['smilelist'] != "" && $_POST['avtorlist'] != "" && $_POST['lifelist'] != "" && $_POST['banlist'] != "" && $_POST['ipbanlist'] != "" && $_POST['headlines'] != "" && $_POST['avlist'] != "" && $_POST['editfiles'] != "" && $_POST['loglist'] != "" && $_POST['blacklist'] != "" && $_POST['reglist'] != "" && $_POST['postchanges'] != "") {
					$dbr = DB::run() -> prepare("UPDATE `setting` SET `setting_value`=? WHERE `setting_name`=?;");
					$dbr -> execute(intval($_POST['userlist']), 'userlist');
					$dbr -> execute(intval($_POST['showuser']), 'showuser');
					$dbr -> execute(intval($_POST['lastusers']), 'lastusers');
					$dbr -> execute(intval($_POST['showref']), 'showref');
					$dbr -> execute(intval($_POST['referer']), 'referer');
					$dbr -> execute(intval($_POST['showlink']), 'showlink');
					$dbr -> execute(intval($_POST['onlinelist']), 'onlinelist');
					$dbr -> execute(intval($_POST['smilelist']), 'smilelist');
					$dbr -> execute(intval($_POST['avtorlist']), 'avtorlist');
					$dbr -> execute(intval($_POST['lifelist']), 'lifelist');
					$dbr -> execute(intval($_POST['banlist']), 'banlist');
					$dbr -> execute(intval($_POST['ipbanlist']), 'ipbanlist');
					$dbr -> execute(intval($_POST['headlines']), 'headlines');
					$dbr -> execute(intval($_POST['avlist']), 'avlist');
					$dbr -> execute(intval($_POST['editfiles']), 'editfiles');
					$dbr -> execute(intval($_POST['loglist']), 'loglist');
					$dbr -> execute(intval($_POST['blacklist']), 'blacklist');
					$dbr -> execute(intval($_POST['reglist']), 'reglist');
					$dbr -> execute(intval($_POST['postchanges']), 'postchanges');

					save_setting();

					notice('Настройки сайта успешно изменены!');
					redirect("setting.php?act=setseven");

				} else {
					show_error('Ошибка! Все поля настроек обязательны для заполнения!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php?act=setseven">Вернуться</a><br />';
		break;

		############################################################################################
		##                            Форма основных прочих настроек                              ##
		############################################################################################
		case 'seteight':

			echo '<b>Прочее/Другое</b><br /><hr />';

			echo '<div class="form">';
			echo '<form method="post" action="setting.php?act=editeight&amp;uid='.$_SESSION['token'].'">';


			$checked = ($setting['errorlog'] == 1) ? ' checked="checked"' : '';
			echo '<input name="errorlog" type="checkbox" value="1"'.$checked.' /> Включить запись логов<br />';

			echo 'Сохраняется информации в лог-файле:<br /><input name="maxlogdat" maxlength="3" value="'.$setting['maxlogdat'].'" /><br />';
			echo 'Ключевые слова (keywords):<br /><input name="keywords" maxlength="250" value="'.$setting['keywords'].'" /><br />';
			echo 'Краткое описание (description):<br /><input name="description" maxlength="250" value="'.$setting['description'].'" /><br />';
			echo 'Не сканируемые расширения (через запятую):<br /><input name="nocheck" maxlength="100" value="'.$setting['nocheck'].'" /><br />';
			echo 'Максимальное время бана (суток):<br /><input name="maxbantime" maxlength="2" value="'.round($setting['maxbantime'] / 1440).'" /><br />';
			echo 'Название денег:<br /><input name="moneyname" maxlength="100" value="'.$setting['moneyname'].'" /><br />';
			echo 'Название баллов:<br /><input name="scorename" maxlength="100" value="'.$setting['scorename'].'" /><br />';
			echo 'Статусы пользователей:<br /><input name="statusname" maxlength="100" value="'.$setting['statusname'].'" /><br />';
			echo 'Статус по умолчанию:<br /><input name="statusdef" maxlength="20" value="'.$setting['statusdef'].'" /><br />';

			$checked = ($setting['addbansend'] == 1) ? ' checked="checked"' : '';
			echo '<input name="addbansend" type="checkbox" value="1"'.$checked.' /> Объяснение из бана<br />';

			echo 'Curl прокси (ip:port):<br /><input name="proxy" maxlength="50" value="'.$setting['proxy'].'" /><br />';

			echo '<input value="Изменить" type="submit" /></form></div><br />';

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                 Форма прочих настроек                                  ##
		############################################################################################
		case 'editeight':

			$uid = check($_GET['uid']);
			$errorlog = (empty($_POST['errorlog'])) ? 0 : 1;
			$addbansend = (empty($_POST['addbansend'])) ? 0 : 1;

			if ($uid == $_SESSION['token']) {
				if ($_POST['maxlogdat'] != "" && $_POST['keywords'] != "" && $_POST['description'] != "" && $_POST['nocheck'] != "" && $_POST['maxbantime'] != "" && $_POST['moneyname'] != "" && $_POST['scorename'] != "" && $_POST['statusname'] != "" && $_POST['statusdef'] != "") {
					$dbr = DB::run() -> prepare("UPDATE `setting` SET `setting_value`=? WHERE `setting_name`=?;");
					$dbr -> execute(intval($_POST['maxlogdat']), 'maxlogdat');
					$dbr -> execute(check($_POST['keywords']), 'keywords');
					$dbr -> execute(check($_POST['description']), 'description');
					$dbr -> execute(check($_POST['nocheck']), 'nocheck');
					$dbr -> execute(intval($_POST['maxbantime'] * 1440), 'maxbantime');
					$dbr -> execute(check($_POST['moneyname']), 'moneyname');
					$dbr -> execute(check($_POST['scorename']), 'scorename');
					$dbr -> execute(check($_POST['statusname']), 'statusname');
					$dbr -> execute(check($_POST['statusdef']), 'statusdef');
					$dbr -> execute($errorlog, 'errorlog');
					$dbr -> execute($addbansend, 'addbansend');
					$dbr -> execute(check($_POST['proxy']), 'proxy');

					save_setting();

					notice('Настройки сайта успешно изменены!');
					redirect("setting.php?act=seteight");

				} else {
					show_error('Ошибка! Все поля настроек обязательны для заполнения!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php?act=seteight">Вернуться</a><br />';
		break;

		############################################################################################
		##                               Форма изменения кеширования                              ##
		############################################################################################
		/**
		* case "setnine":
		*
		* echo '<b>Настройки кэширования</b><br /><hr />';
		*
		* echo '<div class="form">';
		* echo '<form method="post" action="setting.php?act=editnine&amp;uid='.$_SESSION['token'].'">';
		*
		* echo 'Кеш в юзерлисте: <br /><input name="userlistcache" maxlength="2" value="'.$setting['userlistcache'].'" /><br />';
		* echo 'Рейтинг авторитетов: <br /><input name="avtorlistcache" maxlength="2" value="'.$setting['avtorlistcache'].'" /><br />';
		* echo 'Рейтинг толстосумов: <br /><input name="raitinglistcache" maxlength="2" value="'.$setting['raitinglistcache'].'" /><br />';
		* echo 'Поиск пользователей: <br /><input name="usersearchcache" maxlength="2" value="'.$setting['usersearchcache'].'" /><br />';
		* echo 'Рейтинг долгожителей: <br /><input name="lifelistcache" maxlength="2" value="'.$setting['lifelistcache'].'" /><br />';
		* echo 'Рейтинг вкладчиков: <br /><input name="vkladlistcache" maxlength="2" value="'.$setting['vkladlistcache'].'" /><br />';
		* echo 'Листинг администрации: <br /><input name="adminlistcache" maxlength="2" value="'.$setting['adminlistcache'].'" /><br />';
		* echo 'Кеширование при регистрации: <br /><input name="regusercache" maxlength="2" value="'.$setting['regusercache'].'" /><br />';
		* echo 'Популярныe скины: <br /><input name="themescache" maxlength="2" value="'.$setting['themescache'].'" /><br />';
		*
		* echo '<br /><input value="Изменить" type="submit" /></form></div>';
		*
		* echo '<br />* Все настройки измеряются в часах<br />';
		* echo '<br /><img src="/images/img/back.gif" alt="image" /> <a href="setting.php">Вернуться</a><br />';
		* break;
		*
		* ############################################################################################
		* ##                                  Изменение кэширования                                 ##
		* ############################################################################################
		* case "editnine":
		*
		* $uid = check($_GET['uid']);
		*
		* if ($uid==$_SESSION['token']){
		* if ($_POST['userlistcache']!="" && $_POST['avtorlistcache']!="" && $_POST['raitinglistcache']!="" &&  $_POST['usersearchcache']!="" && $_POST['lifelistcache']!="" && $_POST['vkladlistcache']!="" && $_POST['adminlistcache']!="" && $_POST['regusercache']!="" && $_POST['themescache']!=""){
		*
		* $dbr = DB::run()->prepare("UPDATE setting SET setting_value=? WHERE setting_name=?;");
		* $dbr->execute(intval($_POST['userlistcache']), 'userlistcache');
		* $dbr->execute(intval($_POST['avtorlistcache']), 'avtorlistcache');
		* $dbr->execute(intval($_POST['raitinglistcache']), 'raitinglistcache');
		* $dbr->execute(intval($_POST['usersearchcache']), 'usersearchcache');
		* $dbr->execute(intval($_POST['lifelistcache']), 'lifelistcache');
		* $dbr->execute(intval($_POST['adminlistcache']), 'adminlistcache');
		* $dbr->execute(intval($_POST['regusercache']), 'regusercache');
		* $dbr->execute(intval($_POST['themescache']), 'themescache');
		*
		* save_setting();
		*
		* notice('Настройки сайта успешно изменены!');
		* redirect("setting.php");
		*
		* } else {show_error('Ошибка! Все поля настроек обязательны для заполнения!');}
		* } else {show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');}
		*
		* echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php?act=setnine">Вернуться</a><br />';
		* break;
		*/

		############################################################################################
		##                               Форма изменения безопасности                             ##
		############################################################################################
		case 'setten':

			echo '<b>Настройки безопасности</b><br /><hr />';

			echo '<div class="form">';
			echo '<form method="post" action="setting.php?act=editten&amp;uid='.$_SESSION['token'].'">';

			echo 'Замена смайлов в сообщениях:<br /><input name="resmiles" maxlength="2" value="'.$setting['resmiles'].'" /><br /><hr />';

			echo '<b>Captcha</b><br />';
			echo 'Допустимые символы [a-z0-9]:<br /><input name="captcha_symbols" maxlength="26" value="'.$setting['captcha_symbols'].'" /><br />';

			echo 'Максимальное количество символов [4-6]:<br /><input name="captcha_maxlength" maxlength="1" value="'.$setting['captcha_maxlength'].'" /><br />';

			echo 'Амплитуда колебаний символов [0-8]:<br /><input name="captcha_amplitude" maxlength="1" value="'.$setting['captcha_amplitude'].'" /><br />';

			$checked = ($setting['captcha_noise']) ? ' checked="checked"' : '';
			echo '<input name="captcha_noise" type="checkbox" value="1"'.$checked.' /> Цифровой шум<br />';

			$checked = ($setting['captcha_spaces']) ? ' checked="checked"' : '';
			echo '<input name="captcha_spaces" type="checkbox" value="1"'.$checked.' /> Пробелы между символами<br />';

			$checked = ($setting['captcha_credits']) ? ' checked="checked"' : '';
			echo '<input name="captcha_credits" type="checkbox" value="1"'.$checked.' /> Копирайт на картинке<br />';

			echo '<input value="Изменить" type="submit" /></form></div><br />';

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                               Изменение безопасности                                   ##
		############################################################################################
		case 'editten':

			$uid = check($_GET['uid']);
			$captcha_symbols = check(strtolower($_POST['captcha_symbols']));
			$captcha_maxlength = intval(strtolower($_POST['captcha_maxlength']));
			$captcha_amplitude = intval(strtolower($_POST['captcha_amplitude']));
			$captcha_noise = (empty($_POST['captcha_noise'])) ? 0 : 1;
			$captcha_spaces = (empty($_POST['captcha_spaces'])) ? 0 : 1;
			$captcha_credits = (empty($_POST['captcha_credits'])) ? 0 : 1;

			if ($uid == $_SESSION['token']) {
				if ($_POST['resmiles'] != "") {
					if (preg_match('|^[a-z0-9]+$|', $captcha_symbols)) {
						if (preg_match('|^[4-6]{1}+$|', $captcha_maxlength)) {
							if (preg_match('|^[0-8]{1}+$|', $captcha_amplitude)) {

								$dbr = DB::run() -> prepare("UPDATE `setting` SET `setting_value`=? WHERE `setting_name`=?;");
								$dbr -> execute(intval($_POST['resmiles']), 'resmiles');
								$dbr -> execute($captcha_symbols, 'captcha_symbols');
								$dbr -> execute($captcha_maxlength, 'captcha_maxlength');
								$dbr -> execute($captcha_amplitude, 'captcha_amplitude');
								$dbr -> execute($captcha_noise, 'captcha_noise');
								$dbr -> execute($captcha_spaces, 'captcha_spaces');
								$dbr -> execute($captcha_credits, 'captcha_credits');
								save_setting();

								notice('Настройки сайта успешно изменены!');
								redirect("setting.php?act=setten");

							} else {
								show_error('Ошибка! Амплитуда колебаний может быть от 0 до 8!');
							}
						} else {
							show_error('Ошибка! Максимальное количество символов может быть от 4 до 6!');
						}
					} else {
						show_error('Ошибка! Недопустимые символы в captcha!');
					}
				} else {
					show_error('Ошибка! Все поля настроек обязательны для заполнения!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php?act=setten">Вернуться</a><br />';
		break;

		############################################################################################
		##                            Форма изменения стоимости и цен                             ##
		############################################################################################
		case 'seteleven':

			echo '<b>Стоимость и цены</b><br /><hr />';

			echo '<div class="form">';
			echo '<form method="post" action="setting.php?act=editeleven&amp;uid='.$_SESSION['token'].'">';
			echo 'Цена на загрузку аватара: <br /><input name="avatarupload" maxlength="9" value="'.$setting['avatarupload'].'" /><br />';
			echo 'Актива для загрузки аватара: <br /><input name="avatarpoints" maxlength="4" value="'.$setting['avatarpoints'].'" /><br />';
			echo 'Размер загружаемого аватара (px): <br /><input name="avatarsize" maxlength="3" value="'.$setting['avatarsize'].'" /><br />';
			echo 'Вес загружаемого аватара (byte): <br /><input name="avatarweight" maxlength="5" value="'.$setting['avatarweight'].'" /><br /><hr />';

			echo 'Актива для изменения ника: <br /><input name="editnickpoint" maxlength="4" value="'.$setting['editnickpoint'].'" /><br />';
			echo 'Актива для перечисления денег: <br /><input name="sendmoneypoint" maxlength="4" value="'.$setting['sendmoneypoint'].'" /><br />';
			echo 'Актива для изменения авторитета: <br /><input name="editratingpoint" maxlength="4" value="'.$setting['editratingpoint'].'" /><br />';
			echo 'Актива для изменения тем форума: <br /><input name="editforumpoint" maxlength="4" value="'.$setting['editforumpoint'].'" /><br />';
			echo 'Актива для скрытия рекламы: <br /><input name="advertpoint" maxlength="4" value="'.$setting['advertpoint'].'" /><br />';

			echo 'Актива для создания предложения или проблемы: <br /><input name="addofferspoint" maxlength="4" value="'.$setting['addofferspoint'].'" /><br /><hr />';

			$checked = ($setting['editstatus'] == 1) ? ' checked="checked"' : '';
			echo '<input name="editstatus" type="checkbox" value="1"'.$checked.' /> Разрешить менять статус<br />';

			echo 'Актива для изменения статуса: <br /><input name="editstatuspoint" maxlength="4" value="'.$setting['editstatuspoint'].'" /><br />';
			echo 'Стоимость изменения статуса: <br /><input name="editstatusmoney" maxlength="9" value="'.$setting['editstatusmoney'].'" /><br /><hr />';

			echo 'Ежедневный бонус: <br /><input name="bonusmoney" maxlength="10" value="'.$setting['bonusmoney'].'" /><br />';
			echo 'Денег за регистрацию: <br /><input name="registermoney" maxlength="10" value="'.$setting['registermoney'].'" /><br /><hr />';

			echo '<input value="Изменить" type="submit" /></form></div><br />';

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                               Изменение стоимости и цен                                ##
		############################################################################################
		case 'editeleven':

			$uid = check($_GET['uid']);
			$editstatus = (empty($_POST['editstatus'])) ? 0 : 1;

			if ($uid == $_SESSION['token']) {
				if ($_POST['avatarupload'] != "" && $_POST['avatarpoints'] != "" && $_POST['avatarsize'] != "" && $_POST['avatarweight'] != "" && $_POST['editnickpoint'] != "" && $_POST['sendmoneypoint'] != "" && $_POST['editratingpoint'] != "" && $_POST['editforumpoint'] != "" && $_POST['editnickpoint'] != "" && $_POST['editstatuspoint'] != "" && $_POST['editstatusmoney'] != "" && $_POST['bonusmoney'] != "" && $_POST['registermoney'] != "") {

					$dbr = DB::run() -> prepare("UPDATE `setting` SET `setting_value`=? WHERE `setting_name`=?;");
					$dbr -> execute(intval($_POST['avatarupload']), 'avatarupload');
					$dbr -> execute(intval($_POST['avatarpoints']), 'avatarpoints');
					$dbr -> execute(intval($_POST['avatarsize']), 'avatarsize');
					$dbr -> execute(intval($_POST['avatarweight']), 'avatarweight');
					$dbr -> execute(intval($_POST['editnickpoint']), 'editnickpoint');
					$dbr -> execute(intval($_POST['sendmoneypoint']), 'sendmoneypoint');
					$dbr -> execute(intval($_POST['editratingpoint']), 'editratingpoint');
					$dbr -> execute(intval($_POST['editforumpoint']), 'editforumpoint');
					$dbr -> execute(intval($_POST['advertpoint']), 'advertpoint');
					$dbr -> execute(intval($_POST['addofferspoint']), 'addofferspoint');
					$dbr -> execute($editstatus, 'editstatus');
					$dbr -> execute(intval($_POST['editstatuspoint']), 'editstatuspoint');
					$dbr -> execute(intval($_POST['editstatusmoney']), 'editstatusmoney');
					$dbr -> execute(intval($_POST['bonusmoney']), 'bonusmoney');
					$dbr -> execute(intval($_POST['registermoney']), 'registermoney');

					save_setting();

					notice('Настройки сайта успешно изменены!');
					redirect("setting.php?act=seteleven");

				} else {
					show_error('Ошибка! Все поля настроек обязательны для заполнения!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php?act=seteleven">Вернуться</a><br />';
		break;

		############################################################################################
		##                            Форма изменения рекламы на сайте                            ##
		############################################################################################
		case 'setadv':

			echo '<b>Реклама на сайте</b><br /><hr />';

			echo '<div class="form">';
			echo '<form method="post" action="setting.php?act=editadv&amp;uid='.$_SESSION['token'].'">';

			echo 'Кол. рекламных ссылок: <br /><input name="rekusershow" maxlength="2" value="'.$setting['rekusershow'].'" /><br />';
			echo 'Цена рекламы: <br /><input name="rekuserprice" maxlength="7" value="'.$setting['rekuserprice'].'" /><br />';
			echo 'Цена опций (жирность, цвет): <br /><input name="rekuseroptprice" maxlength="7" value="'.$setting['rekuseroptprice'].'" /><br />';
			echo 'Срок рекламы (часов): <br /><input name="rekusertime" maxlength="3" value="'.$setting['rekusertime'].'" /><br />';
			echo 'Максимум ссылок разрешено: <br /><input name="rekusertotal" maxlength="3" value="'.$setting['rekusertotal'].'" /><br />';
			echo 'Листинг всех ссылок: <br /><input name="rekuserpost" maxlength="2" value="'.$setting['rekuserpost'].'" /><br />';

			echo '<input value="Изменить" type="submit" /></form></div><br />';

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                               Изменение настроек рекламы                               ##
		############################################################################################
		case 'editadv':

			$uid = check($_GET['uid']);

			if ($uid == $_SESSION['token']) {
				if ($_POST['rekusershow'] != "" && $_POST['rekuserprice'] != "" && $_POST['rekuseroptprice'] != "" && $_POST['rekusertime'] != "" && $_POST['rekusertotal'] != "" && $_POST['rekuserpost'] != "") {
					$dbr = DB::run() -> prepare("UPDATE `setting` SET `setting_value`=? WHERE `setting_name`=?;");
					$dbr -> execute(intval($_POST['rekusershow']), 'rekusershow');
					$dbr -> execute(intval($_POST['rekuserprice']), 'rekuserprice');
					$dbr -> execute(intval($_POST['rekuseroptprice']), 'rekuseroptprice');
					$dbr -> execute(intval($_POST['rekusertime']), 'rekusertime');
					$dbr -> execute(intval($_POST['rekusertotal']), 'rekusertotal');
					$dbr -> execute(intval($_POST['rekuserpost']), 'rekuserpost');

					save_setting();

					notice('Настройки сайта успешно изменены!');
					redirect("setting.php?act=setadv");

				} else {
					show_error('Ошибка! Все поля настроек обязательны для заполнения!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php?act=setadv">Вернуться</a><br />';
		break;

		############################################################################################
		##                         Форма изменения настройки изображений                          ##
		############################################################################################
		case 'setimage':

			echo '<b>Загрузка изображений</b><br /><hr />';

			echo '<div class="form">';
			echo '<form method="post" action="setting.php?act=editimage&amp;uid='.$_SESSION['token'].'">';

			echo 'Максимальный вес фото (kb) (Ограничение: '.ini_get('upload_max_filesize').'):<br /><input name="filesize" maxlength="6" value="'.round($setting['filesize'] / 1024).'" /><br />';
			echo 'Максимальный размер фото (px):<br /><input name="fileupfoto" maxlength="6" value="'.$setting['fileupfoto'].'" /><br />';
			echo 'Уменьшение фото при загрузке (px):<br /><input name="screensize" maxlength="4" value="'.$setting['screensize'].'" /><br />';

			echo 'Размер скриншотов (px):<br /><input name="previewsize" maxlength="3" value="'.$setting['previewsize'].'" /><br />';

			$checked = ($setting['copyfoto'] == 1) ? ' checked="checked"' : '';
			echo '<input name="copyfoto" type="checkbox" value="1"'.$checked.' /> Наложение копирайта<br />';
			echo '<img src="/images/img/watermark.png" alt="watermark" title="'.$config['home'].'/images/img/watermark.png" /><br />';

			echo '<input value="Изменить" type="submit" /></form></div><br />';

			echo 'Не устанавливайте слишком большие размеры веса и размера изображений, так как может не хватить процессорного времени для обработки<br />';
			echo 'При изменении размера скриншота, необходимо вручную очистить кэш изображений<br /><br />';

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                             Изменение настроек изображения                             ##
		############################################################################################
		case 'editimage':

			$uid = check($_GET['uid']);
			$copyfoto = (empty($_POST['copyfoto'])) ? 0 : 1;

			if ($uid == $_SESSION['token']) {
				if ($_POST['filesize'] != "" && $_POST['fileupfoto'] != "" && $_POST['screensize'] != "" && $_POST['previewsize'] != "") {
					$dbr = DB::run() -> prepare("UPDATE `setting` SET `setting_value`=? WHERE `setting_name`=?;");

					$dbr -> execute(intval($_POST['filesize'] * 1024), 'filesize');
					$dbr -> execute(intval($_POST['fileupfoto']), 'fileupfoto');
					$dbr -> execute(intval($_POST['screensize']), 'screensize');
					$dbr -> execute(intval($_POST['previewsize']), 'previewsize');
					$dbr -> execute($copyfoto, 'copyfoto');

					save_setting();

					notice('Настройки сайта успешно изменены!');
					redirect("setting.php?act=setimage");

				} else {
					show_error('Ошибка! Все поля настроек обязательны для заполнения!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="setting.php?act=setimage">Вернуться</a><br />';
		break;

	default:
		redirect("setting.php");
	endswitch;

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
