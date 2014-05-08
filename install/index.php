<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('html_errors', true);
ini_set('error_reporting', E_ALL);

include_once ('func.php');
include_once ('../includes/connect.php');

$arrfile = array(
	'includes/connect.php',
	'upload/avatars',
	'upload/counters',
	'upload/events',
	'upload/forum',
	'upload/news',
	'upload/photos',
	'upload/pictures',
	'upload/thumbnail',
	'images/avatars',
	'images/smiles',
	'load/files',
	'load/screen',
	'load/loader',
	'local/antidos',
	'local/backup',
	//'local/main',
	'local/temp'
);


header("Content-type:text/html; charset=utf-8");
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru"><head>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
echo '<title>Установка RotorCMS</title>';
echo '<link rel="stylesheet" href="style.css" type="text/css" />';
echo '<meta name="generator" content="RotorCMS" />';
echo '</head><body>';

echo '<div class="cs" id="up"><img src="../images/img/logo.png" alt="RotorCMS" /><br />';
echo 'Система управления мобильным сайтом</div><div>';

$act = (isset($_GET['act'])) ? htmlspecialchars($_GET['act']) : 'index';

switch ($act):
############################################################################################
##                                    Принятие соглашения                                 ##
############################################################################################
	case '0':

		echo '<img src="../images/img/setting.png" alt="img" /> <b>ШАГ ПЕРВЫЙ - ПРИНЯТИЕ СОГЛАШЕНИЯ</b><br /><br />';

		echo '<big><b>Пользовательское соглашение</b></big><br />';

		$agreement = 'Пользовательское соглашение на использование скриптов, распространяемых cайтом VISAVI.NET.
Ограниченное использование
Приобретая лицензию на программный продукт RotorCMS, вы должны знать, что не приобретаете авторские права на программный продукт. Вы приобретаете только право на использование программного продукта на единственном веб сайте (одном домене и его поддоменах), принадлежащем Вам или Вашему клиенту. Для использования скрипта на другом сайте, вам необходимо приобретать повторную лицензию. Запрещается перепродажа скрипта третьим лицам, и если вы приобретаете скрипт для Ваших клиентов, то вы обязаны ознакомить Ваших клиентов с данным лицензионным соглашением. Также в случае приобретения скрипта не для собственного использования, а для установки на сайты Ваших клиентов, мы не несем обязательств по поддержке Ваших клиентов.

Права и обязанности сторон

Пользователь имеет право:
- Изменять дизайн и структуру программного кода в соответствии с нуждами своего сайта.
- Производить и распространять инструкции по созданным Вами модификациям и дополнениям, если в них будет иметься указание на оригинального разработчика программного продукта до Ваших модификаций. Модификации, произведенные Вами самостоятельно, не являются собственностью VISAVI.NET, если не содержат программные коды непосредственно скрипта.
- Создавать модули, которые будут взаимодействовать с нашими программными кодами, с указанием на то, что это Ваш оригинальный продукт.
- Переносить программный продукт на другой сайт после обязательного уведомления нас об этом, а также полного удаления скрипта с предыдущего сайта.

Пользователь не имеет право:
- Передавать права на использование программного продукта третьим лицам.
- Изменять структуру программных кодов, функции программы, с целью создания родственных продуктов
- Создавать отдельные самостоятельные продукты, базирующиеся на нашем программном коде
- Использовать копии программного продукта RotorCMS по одной лицензии на более чем одном сайте (одном домене и его поддоменах)
- Рекламировать, продавать или публиковать на своем сайте пиратские копии нашего программного продукта
- Распространять или содействовать распространению нелицензионных копий программного продукта RotorCMS
- Удалять механизмы проверки наличия оригинальной лицензии на использование скрипта
- Удалять копирайты и другую авторскую информацию со страниц движка

Досрочное расторжение договорных обязательств

Данное соглашение расторгается автоматически, если Вы отказываетесь выполнять условия нашего договора. Данное лицензионное соглашение может быть расторгнуто нами в одностороннем порядке, в случае установления фактов нарушения данного лицензионного соглашения. В случае досрочного расторжения договора Вы обязуетесь удалить все Ваши копии нашего программного продукта в течении 3 рабочих дней, с момента получения соответствующего уведомления.';

		echo '<form action="index.php?act=1" method="post">';
		echo '<textarea cols="100" rows="20" name="msg">' . $agreement . '</textarea><br /><br />';
		echo '<a href="license.php">Полный текст пользовательского соглашения</a><br /><br />';

		echo '<input name="agree" id="agree" type="checkbox" value="1" /> <label for="agree"><b>Я ПРИНИМАЮ УСЛОВИЯ СОГЛАШЕНИЯ</b></label><br /><br />';

		echo '<input type="submit" value="Продолжить" /></form><hr />';
		echo '<img src="../images/img/back.gif" alt="image" /> <a href="index.php">Вернуться</a>';
	break;

	############################################################################################
	##                                    Проверка системы                                    ##
	############################################################################################
	case '1':

		$agree = (empty($_REQUEST['agree'])) ? 0 : 1;

		if (!empty($agree)) {
			echo '<img src="../images/img/setting.png" alt="img" /> <b>ШАГ ВТОРОЙ - ПРОВЕРКА СИСТЕМЫ</b><br /><br />';

			$error_setting = 0;

			if (version_compare(PHP_VERSION, '5.2.1') > 0) {
				echo '<img src="../images/img/plus.gif" alt="image" /> Версия PHP 5.2.1 и выше: <b><span style="color:#00cc00">ОК</span></b> (Версия ' . phpversion() . ')<br />';
			} else {
				echo '<img src="../images/img/minus.gif" alt="image" /> Версия PHP 5.2.1 и выше: <b><span style="color:#ff0000">Ошибка</span></b>  (Версия ' . phpversion() . ')<br />';
				$error_critical = 1;
			}

			if (extension_loaded('pdo_mysql')) {
				if (getModuleSetting('pdo_mysql', 'Client API version') != "") {
					$pdoversion = strtok(getModuleSetting('pdo_mysql', 'Client API version'), '-');
				} elseif (getModuleSetting('pdo_mysql', 'PDO Driver for MySQL, client library version') != "") {
					$pdoversion = getModuleSetting('pdo_mysql', 'PDO Driver for MySQL, client library version');
				} else {
					$pdoversion = 'Не определено';
				}

				echo '<img src="../images/img/plus.gif" alt="image" /> Расширение PDO-MySQL: <b><span style="color:#00cc00">ОК</span></b> (Версия ' . $pdoversion . ')<br />';
			} else {
				echo '<img src="../images/img/minus.gif" alt="image" /> Расширение PDO-MySQL: <b><span style="color:#ff0000">Ошибка</span></b> (Расширение не загружено)<br />';
				$error_critical = 1;
			}

			if (extension_loaded('gd')) {
				echo '<img src="../images/img/plus.gif" alt="image" /> Библиотека GD: <b><span style="color:#00cc00">ОК</span></b> (Версия ' . getModuleSetting('gd', 'GD Version') . ')<br />';
			} else {
				echo '<img src="../images/img/minus.gif" alt="image" /> Библиотека GD: <b><span style="color:#ffa500">Предупреждение</span></b> (Библиотека не загружена)<br />';
				$error_setting++;
			}

			if (extension_loaded('zlib')) {
				echo '<img src="../images/img/plus.gif" alt="image" /> Библиотека Zlib: <b><span style="color:#00cc00">ОК</span></b> (Версия ' . getModuleSetting('zlib', 'Compiled Version') . ')<br />';
			} else {
				echo '<img src="../images/img/minus.gif" alt="image" /> Библиотека Zlib: <b><span style="color:#ffa500">Предупреждение</span></b> (Библиотека не загружена)<br />';
				$error_setting++;
			}

			if (!ini_get('safe_mode')) {
				echo '<img src="../images/img/plus.gif" alt="image" /> Safe Mode: <b><span style="color:#00cc00">ОК</span></b> (Выключено)<br />';
			} else {
				echo '<img src="../images/img/minus.gif" alt="image" /> Safe Mode: <b><span style="color:#ffa500">Предупреждение</span></b> (Включено)<br />';
				$error_setting++;
			}

			if (!ini_get('magic_quotes_runtime')) {
				echo '<img src="../images/img/plus.gif" alt="image" /> Magic Quotes Runtime: <b><span style="color:#00cc00">ОК</span></b> (Выключено)<br />';
			} else {
				echo '<img src="../images/img/minus.gif" alt="image" /> Magic Quotes Runtime: <b><span style="color:#ffa500">Предупреждение</span></b> (Включено)<br />';
				$error_setting++;
			}

			if (!ini_get('session.auto_start')) {
				echo '<img src="../images/img/plus.gif" alt="image" /> Session auto start: <b><span style="color:#00cc00">ОК</span></b> (Выключено)<br />';
			} else {
				echo '<img src="../images/img/minus.gif" alt="image" /> Session auto start: <b><span style="color:#ffa500">Предупреждение</span></b> (Включено)<br />';
				$error_setting++;
			}

			if (!ini_get('register_globals')) {
				echo '<img src="../images/img/plus.gif" alt="image" /> Register Globals: <b><span style="color:#00cc00">ОК</span></b> (Выключено)<br />';
			} else {
				echo '<img src="../images/img/minus.gif" alt="image" /> Register Globals: <b><span style="color:#ffa500">Предупреждение</span></b> (Включено)<br />';
				$error_setting++;
			}

			if (ini_get('file_uploads')) {
				echo '<img src="../images/img/plus.gif" alt="image" /> Загрузка файлов: <b><span style="color:#00cc00">ОК</span></b> (Включено)<br />';
			} else {
				echo '<img src="../images/img/minus.gif" alt="image" /> Загрузка файлов: <b><span style="color:#ffa500">Предупреждение</span></b> (Выключено)<br />';
				$error_setting++;
			}

			echo '<br /><b>Права доступа</b><br /><br />';

			$chmod_errors = 0;
			$not_found_errors = 0;

			foreach ($arrfile as $file) {
				$realfile = '../'.$file;
				if (!file_exists($realfile)) {
					$file_status = '<span style="color:#ff0000">Не найден!</span>';
					$not_found_errors = 1;
				} elseif (is_writable($realfile)) {
					$file_status = '<span style="color:#00cc00">ОК</span>';
				} else {
					@chmod($realfile, 0777);
					if (is_writable($realfile)) {
						$file_status = '<span style="color:#00cc00">ОК</span>';
					} else {
						@chmod($realfile, 0755);
						if (is_writable($realfile)) {
							$file_status = '<span style="color:#00cc00">Разрешено</span>';
						} else {
							$file_status = '<span style="color:#ff0000">Запрещено</span>';
							$chmod_errors = 1;
						}
					}
				}

				$chmod_value = @decoct(@fileperms($realfile)) % 1000;

				echo '<img src="../images/img/right.gif" alt="image" /> '.$file . ' <b> - ' . $file_status . '</b> (chmod ' . $chmod_value . ')<br />';
			}

			echo '<br />Если какой-то пункт выделен красным, необходимо зайти по фтп и выставить CHMOD разрешающую запись<br />';
			echo 'Некоторые настройки являются рекомендуемыми для полной совместимости, однако скрипт способен работать даже если рекомендуемые настройки не совпадают с текущими.<br /><br />';

			if (empty($error_critical) && empty($not_found_errors) && empty($chmod_errors)) {
				echo '<img src="../images/img/open.gif" alt="image" /> <b><span style="color:#00cc00">Вы можете продолжить установку движка!</span></b><br /><br />';

				if (empty($error_setting)) {
					echo 'Все модули и библиотеки присутствуют, настройки корректны, необходимые файлы и папки доступны для записи<br /><br />';
				} else {
					echo '<b><span style="color:#ffa500">У вас имеются предупреждения!</span></b> (Всего: ' . $error_setting . ')<br />';
					echo 'Данные предупреждения не являются критическими, но тем не менее для полноценной, стабильной и безопасной работы движка желательно их устранить<br />';
					echo 'Вы можете продолжить установку скрипта, но нет никаких гарантий, что движок будет работать стабильно<br /><br />';
				}

				echo '<img src="../images/img/reload.gif" alt="image" /> <b><a href="index.php?act=2">ПРИСТУПИТЬ К УСТАНОВКЕ</a></b><br /><br />';
			} else {
				echo '<b><span style="color:#ff0000">Имеются критические ошибки!</span></b><br />';
				echo 'Вы не сможете приступить к установке, пока не устраните все ошибки<br /><br />';
				echo 'Если ваша версия PHP удовлетворяет требованиям работы движка, тогда скорее всего у вас не подключено расширение PDO-MySQL<br />';
				echo 'Это расширение уже встроено в PHP, его нужно только включить, обратитесть в поддержку вашего хостинга<br /><br />';
			}
		} else {
			echo '<img src="../images/img/setting.png" alt="image" /> <b>ОТКАЗ ПРИНЯТИЯ УСЛОВИЙ СОГЛАШЕНИЯ</b><br /><br />';
			echo 'Вы не можете продолжить установку движка так как отказываетесь принимать условия соглашения<br />';
			echo 'Любое использование нашего движка означает ваше согласие с нашим соглашением<br /><br />';
		}

		echo '<img src="../images/img/back.gif" alt="image" /> <a href="index.php?act=0">Вернуться</a>';
	break;

	############################################################################################
	##                                     Подключение к базе                                 ##
	############################################################################################
	case '2':
		echo '<img src="../images/img/setting.png" alt="img" /> <b>ШАГ ТРЕТИЙ - ПОДКЛЮЧЕНИЕ К БД</b><br /><br />';

		echo 'Данные подключения к БД будут записаны в файл includes/connect.php, после записи файлу будут автоматически присвоены права CHMOD 644<br />';
		echo 'Если этого не произошло, то вы можете вручную выставить файлу права запрещающие запись в него<br /><br />';

		echo '<div class="form">';
		echo '<form method="post" action="index.php?act=3">';
		echo 'Сервер MySQL:<br />';
		echo '<input name="dbhost" value="localhost" /><br />';
		echo 'Порт MySQL:<br />';
		echo '<input name="dbport" value="3306" /><br />';
		echo 'Имя базы данных:<br />';
		echo '<input name="dbname" /><br />';
		echo 'Имя пользователя:<br />';
		echo '<input name="dbuser" /><br />';
		echo 'Пароль:<br />';
		echo '<input name="dbpass" type="password" /><br /><br />';
		echo '<input value="Продолжить" type="submit" /></form></div><br />';

		echo 'База данных и пользователь должны быть созданы в панеле управления вашего сайта!<br /><br />';

		echo '<img src="../images/img/back.gif" alt="image" /> <a href="index.php?act=1&amp;agree=1">Вернуться</a>';
		break;
	# ###########################################################################################
	# #                                     Получение данных                                   ##
	# ###########################################################################################
	case '3':
		echo '<img src="../images/img/setting.png" alt="img" /> <b>ИМПОРТ ТАБЛИЦ</b><br /><br />';

		if (!empty($_POST['dbhost']) && !empty($_POST['dbport']) && !empty($_POST['dbname']) && !empty($_POST['dbuser'])) {
			$dbhost = htmlspecialchars(trim($_POST['dbhost']));
			$dbport = htmlspecialchars(trim($_POST['dbport']));
			$dbname = htmlspecialchars(trim($_POST['dbname']));
			$dbuser = htmlspecialchars(trim($_POST['dbuser']));
			$dbpass = htmlspecialchars(trim($_POST['dbpass']));

			try {
				$db = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $dbname, $dbuser, $dbpass);
				$db -> setAttribute(PDO :: ATTR_ERRMODE, PDO :: ERRMODE_EXCEPTION);
				$db -> setAttribute(PDO :: ATTR_DEFAULT_FETCH_MODE, PDO :: FETCH_ASSOC);
				$db -> exec('SET CHARACTER SET utf8');
				$db -> exec('SET NAMES utf8');

				echo '<b><span style="color:#00cc00">Соединение с базой данных произведено успешно!</span></b><br /><br />';

$dbconfig = "<?php
define ('DBHOST', '$dbhost');
define ('DBPORT', '$dbport');
define ('DBNAME', '$dbname');
define ('DBUSER', '$dbuser');
define ('DBPASS', '$dbpass');
?>
";
				file_put_contents('../includes/connect.php', $dbconfig);
				@chmod('../includes/connect.php', 0664);

				// ------------------------------------------//
				try {
					$query = file_get_contents('sql/tables.sql');
					$pieces = split_sql($query);

					$numtables = 0;

					for ($i = 0; $i < count($pieces); $i++) {
						$pieces[$i] = trim($pieces[$i]);
						if (!empty ($pieces[$i]) && $pieces[$i] != "#") {
							$db -> query($pieces[$i]);
							$numtables++;
						}
					}

					echo '<b><span style="color:#00cc00">Таблицы успешно импортированы</span></b><br />';
					echo 'Всего загружено таблиц: ' . $numtables . '<br /><br />';
				}
				catch (PDOException $e) {
					$errortables = 1;
					echo '<b><span style="color:#ff0000">Ошибка! Не удалось импортировать таблицы в БД!</span></b><br />';
					echo 'Код ошибки: ' . $e -> getMessage() . '<br /><br />';
				}
				// ------------------------------------------//
				try {
					$query = file_get_contents('sql/data.sql');
					$pieces = split_sql($query);

					$numtables = 0;

					for ($i = 0; $i < count($pieces); $i++) {
						$pieces[$i] = trim($pieces[$i]);
						if (!empty ($pieces[$i]) && $pieces[$i] != "#") {
							$db -> query($pieces[$i]);
							$numtables++;
						}
					}

					echo '<b><span style="color:#00cc00">Данные успешно импортированы</span></b><br />';
					echo 'Всего загружено данных: ' . $numtables . '<br /><br />';
				}
				catch (PDOException $e) {
					$errortables = 1;
					echo '<b><span style="color:#ff0000">Ошибка! Не удалось загрузить данные в БД!</span></b><br />';
					echo 'Код ошибки: ' . $e -> getMessage() . '<br /><br />';
				}
				// ------------------------------------------//
			}
			catch (PDOException $e) {
				$errorconnect = 1;
				echo '<b><span style="color:#ff0000">Ошибка! Невозможно соединиться с базой данных, проверьте правильность данных!</span></b><br /><br />';
			}

			if (empty($errorconnect) && empty($errortables)) {
				echo 'Если на этой странице вы не видите никаких ошибок значит все таблицы были успешно импортированы<br /><br />';
				echo 'Не обновляйте страницу, переходите сразу к следующему шагу установки RotorCMS<br /><br />';
				echo '<img src="../images/img/reload.gif" alt="image" /> <b><a href="index.php?act=4">ПРОДОЛЖИТЬ УСТАНОВКУ</a></b><br /><br />';
			}
		} else {
			echo '<b>Ошибка! Вы не ввели важные данные!</b><br /><br />';
		}

		echo '<img src="../images/img/back.gif" alt="image" /> <a href="index.php?act=2">Вернуться</a>';
	break;

	############################################################################################
	##                                     Получение данных                                   ##
	############################################################################################
	case '4':

		echo '<img src="../images/img/setting.png" alt="img" /> <b>ШАГ ЧЕТВЕРТЫЙ - НАСТРОЙКА СИСТЕМЫ</b><br /><br />';

		echo 'Прежде чем перейти к администрированию вашего сайта, необходимо создать аккаунт администратора.<br />';
		echo 'Перед тем как нажимать кнопку Пуск, убедитесь, что на предыдущей странице нет уведомлений об ошибках, иначе процесс не сможет быть завершен удачно.<br />';
		echo 'После окончания инсталляции необходимо удалить директорию <b>install</b> со всем содержимым навсегда, пароль и остальные данные вы сможете поменять в своем профиле<br /><br />';

		if ($_SERVER['HTTP_HOST']) {
			$servername = htmlspecialchars($_SERVER['HTTP_HOST']);
		} else {
			$servername = htmlspecialchars($_SERVER['SERVER_NAME']);
		}

		echo '<div class="form">';
		echo '<form method="post" action="index.php?act=5">';
		echo 'Логин (max20):<br />';
		echo '<input name="login" maxlength="20" /><br />';
		echo 'Пароль(max20):<br />';
		echo '<input name="password" type="password" maxlength="20" /><br />';
		echo 'Повторите пароль:<br />';
		echo '<input name="password2" type="password" maxlength="20" /><br />';
		echo 'Адрес e-mail:<br />';
		echo '<input name="mail" maxlength="100" /><br />';
		echo 'Адрес сайта:<br />';
		echo '<input name="site" value="http://' . $servername . '" maxlength="100" /><br /><br />';
		echo '<input value="Пуск" type="submit" /></form></div><br />';

		echo 'Внимание, в полях логин и пароль разрешены только знаки латинского алфавита, цифры и знак дефис<br />';
		echo 'Все поля обязательны для заполнения<br />E-mail будет нужен для восстановления пароля, пишите только свои данные<br />Не нажимайте кнопку дважды, подождите до тех пор, пока процесс не завершится<br />';
		echo 'В поле ввода адреса сайта необходимо ввести адрес в который у вас распакован движок, если это поддомен или папка, то необходимо указать ее, к примеру http://wap.visavi.net<br />';
		echo 'Пароль необходимо выбирать посложнее, лучше всего состоящий из цифр, маленьких и больших латинских символов одновременно, длинее 5 символов<br /><br />';

		echo '<img src="../images/img/back.gif" alt="image" /> <a href="index.php?act=2">Вернуться</a>';
	break;

	############################################################################################
	##                                     Создание аккаунта                                  ##
	############################################################################################
	case '5':

		echo '<img src="../images/img/setting.png" alt="img" /> <b>Результат установки RotorCMS</b><br /><br />';

		$login = htmlspecialchars($_POST['login']);
		$password = htmlspecialchars($_POST['password']);
		$password2 = htmlspecialchars($_POST['password2']);
		$mail = strtolower(htmlspecialchars($_POST['mail']));
		$site = utf_lower(htmlspecialchars($_POST['site']));

		if (strlen($login) <= 20 && strlen($password) <= 20) {
			if (strlen($login) >= 3 && strlen($password) >= 3) {
				if (preg_match('|^[a-z0-9\-]+$|i', $login)) {
					if (preg_match('|^[a-z0-9\-]+$|i', $password)) {
						if ($password == $password2) {
							if (preg_match('#^([a-z0-9_\-\.])+\@([a-z0-9_\-\.])+(\.([a-z0-9])+)+$#', $mail)) {
								if (preg_match('#^http://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', $site)) {
									try {
										$db = new PDO('mysql:host=' . DBHOST . ';port=' . DBPORT . ';dbname=' . DBNAME, DBUSER, DBPASS);
										$db -> setAttribute(PDO :: ATTR_ERRMODE, PDO :: ERRMODE_EXCEPTION);
										$db -> setAttribute(PDO :: ATTR_DEFAULT_FETCH_MODE, PDO :: FETCH_ASSOC);
										$db -> exec('SET CHARACTER SET utf8');
										$db -> exec('SET NAMES utf8');
									}
									catch (PDOException $e) {
										echo '<b><span style="color:#ff0000">Ошибка! Невозможно соединиться с базой данных!</span></b><br />';
										echo 'Код ошибки: ' . $e -> getMessage() . '<br /><br />';
									}

									$result = $db -> query("SELECT * FROM `setting` WHERE `setting_name`='nickname' LIMIT 1;");
 									$row = $result -> fetch();

									if (empty($row['setting_value'])) {

										$reglogin = $db -> query("SELECT `users_id` FROM `users` WHERE lower(`users_login`)='" . strtolower($login) . "' LIMIT 1;");
 										$data = $reglogin -> fetch();
										if (empty($data['users_id'])) {

											// -------------- Настройки ---------------//
											$db -> query("UPDATE `setting` SET `setting_value`='" . $login . "' WHERE `setting_name`='nickname';");
											$db -> query("UPDATE `setting` SET `setting_value`='" . $mail . "' WHERE `setting_name`='emails';");
											$db -> query("UPDATE `setting` SET `setting_value`='" . $site . "' WHERE `setting_name`='home';");
											$db -> query("UPDATE `setting` SET `setting_value`='" . $site . "/images/img/logo.png' WHERE `setting_name`='logotip';");
											$db -> query("UPDATE `setting` SET `setting_value`='" . generate_password() . "' WHERE `setting_name`='keypass';");
											// ---------- Очистка кэша настроек --------//
											if (file_exists('../local/temp/setting.dat')) {
												unlink ('../local/temp/setting.dat');
											}
											// -------------- Профиль ---------------//
											$db -> query("INSERT INTO `users` (`users_login`, `users_pass`, `users_email`, `users_joined`, `users_level`, `users_info`, `users_site`, `users_newprivat`, `users_themes`, `users_postguest`, `users_postnews`, `users_postprivat`, `users_postforum`, `users_themesforum`, `users_postboard`, `users_point`, `users_money`, `users_status`) VALUES ('" . $login . "', '" . md5(md5($password)) . "', '" . $mail . "', '" . time() . "', 101, 'Администратор сайта', '" . $site . "', 1, 0, 10, 10, 10, 10, 10, 10, 500, 100000, 'Администратор');");

											// -------------- Приват ---------------//
											$textpriv = 'Привет, ' . $login . '! Поздравляем с успешной установкой нашего движка RotorCMS.<br />Новые версии, апгрейды, а также множество других дополнений вы найдете на нашем сайте [url=http://visavi.net]VISAVI.NET[/url]<br />Рекомендуем приобрести лицензионную версию нашего продукта, помимо расширенных возможностей движка вы будете получать постоянные обновления, которые недоступны для бесплатных версий, а также техническую поддержку по использованию движка RotorCMS';

											$db -> query("INSERT INTO `inbox` (`inbox_user`, `inbox_author`, `inbox_text`, `inbox_time`) VALUES ('" . $login . "', 'Vantuz', '" . $textpriv . "', '" . time() . "');");

											// -------------- Новость ---------------//
											$textnews = 'Добро пожаловать на демонстрационную страницу движка RotorCMS<br />RotorCMS - функционально законченная система управления контентом с открытым кодом написанная на PHP. Она использует базу данных MySQL для хранения содержимого вашего сайта. RotorCMS является гибкой, мощной и интуитивно понятной системой с минимальными требованиями к хостингу, высоким уровнем защиты и является превосходным выбором для построения сайта любой степени сложности<br />Главной особенностью RotorCMS является низкая нагрузка на системные ресурсы, даже при очень большой аудитории сайта нагрузка не сервер будет минимальной, и вы не будете испытывать каких-либо проблем с отображением информации.<br />Движок RotorCMS вы можете скачать на официальном сайте [url=http://visavi.net]VISAVI.NET[/url]';

											$db -> query("INSERT INTO news (`news_title`, `news_text`, `news_author`, `news_time`) VALUES ('Добро пожаловать!', '" . $textnews . "', '" . $login . "', '" . time() . "');");

											echo 'Поздравляем Вас, RotorCMS был успешно установлен на Ваш сервер. Вы можете перейти на главную страницу вашего сайта и посмотреть возможности скрипта<br />
Приятной Вам работы<br /><br />';

											echo '<img src="../images/img/reload.gif" alt="image" /> <b><a href="../input.php?login=' . $login . '&amp;pass=' . $password . '">Войти на сайт</a></b><br /><br />';

											echo 'Обязательно удалите директорию <b>install</b> со всем содержимым, эта папка больше не потребуется для работы движка<br /><br />';
										} else {
											echo '<b>Ошибка! Данный логин уже занят другим пользователем!</b><br /><br />';
										}
									} else {
										echo '<b>Ошибка! Профиль администратора уже создан!<br />';
										echo 'Очистите таблицу setting и удалите профиль администратора в таблице users перед повторной инсталляцией!</b><br /><br />';
									}
								} else {
									echo '<b>Ошибка! Неправильный адрес сайта, необходим формата http://my_site.domen</b><br /><br />';
								}
							} else {
								echo '<b>Ошибка! Неправильный адрес e-mail, необходим формат name@site.domen</b><br /><br />';
							}
						} else {
							echo '<b>Ошибка! Веденные пароли отличаются друг от друга</b><br /><br />';
						}
					} else {
						echo '<b>Ошибка! Недопустимые символы в пароле. Разрешены только знаки латинского алфавита и цифры!</b><br /><br />';
					}
				} else {
					echo '<b>Ошибка! Недопустимые символы в логине. Разрешены только знаки латинского алфавита и цифры!</b><br /><br />';
				}
			} else {
				echo '<b>Ошибка! Слишком короткий логин или пароль (От 3 до 20 символов)</b><br /><br />';
			}
		} else {
			echo '<b>Ошибка! Слишком длинный логин или пароль (От 3 до 20 символов)</b><br /><br />';
		}

		echo '<img src="../images/img/back.gif" alt="image" /> <a href="index.php?act=4">Вернуться</a>';
	break;

	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
	default:
		echo '<img src="../images/img/setting.png" alt="img" /> <b>Установка скрипта RotorCMS</b><br /><br />';
		echo 'Добро пожаловать в мастер установки RotorCMS<br />
Данный мастер поможет вам установить скрипт всего за пару минут<br /><br />
Прежде чем начать установку убедитесь, что все файлы дистрибутива загружены на сервер, а также выставлены необходимые права доступа для папок и файлов<br /><br />';

		foreach ($arrfile as $file) {
			echo '<img src="../images/img/right.gif" alt="image" /> <b>'.$file.'</b> (chmod ';
			echo (is_file('../'.$file)) ? 666 : 777;
			echo ')<br />';
		}

		echo 'А также всем файлам внутри папки <b>local/main</b> (chmod 666)<br /><br />';

		echo 'После установки движка скрипт автоматически присвоит права CHMOD 644 файлу includes/connect.php<br />';
		echo 'Если этого не произошло, то вы можете вручную выставить файлу права запрещающие запись в него<br /><br />';

		echo '<b>Действия при повторной установке движка RotorCMS</b><br />';
		echo '1. Загрузите из дистрибутива на сайт директорию install со всем ее содержимым<br />';
		echo '2. Очистите таблицу setting в базе данных<br />';
		echo '3. Удалите профиль администратора в таблице users<br />';
		echo '4. Перейдите по адресу http://ваш_сайт/install и переустановите движок<br />';

		echo 'После этих действий можно повторно установить движок на ваш сайт<br /><br />';

		echo '<span style="color:#ff0000">Внимание:</span> при установке скрипта создается структура базы данных, создается аккаунт администратора, а также прописываются основные настройки системы, поэтому после успешной установки удалите директорию <b>install</b> во избежание повторной установки скрипта!<br /><br />
Приятной Вам работы<br /><br />';

		echo '<img src="../images/img/open.gif" alt="image" /> <b><a href="index.php?act=0">ПРОДОЛЖИТЬ УСТАНОВКУ</a></b><br /><br />';

		if (file_exists('../upgrade/index.php')){
			echo 'Если вам нужно обновить ваш движок, то перейдите к мастеру обновлений движка<br /><br />';
			echo '<img src="../images/img/circle.gif" alt="image" /> <b><a href="../upgrade/index.php">Мастер обновлений движка</a></b><br />';
			echo ' Обновление доступно для версий движка RotorCMS 3.0.0 - 3.6.5<br /><br />';
		}

	endswitch;

echo '</div><div class="lol" id="down">';
echo '<p style="text-align:center">';
echo '<a href="http://visavi.net">Powered by RotorCMS</a><br />';
echo '</p>';
echo '</div></body></html>';
?>
