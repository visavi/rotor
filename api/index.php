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

show_title('API интерфейс');

$files = glob('*.php');
$text = 'Нет описания';

foreach ($files as $file){
	if ($file == 'index.php') {$text = 'Главная страница с описанием интерфейса';}
	if ($file == 'user.php') {$text = 'Параметры: key, Возвращаемые значения: массив данных из профиля пользователя';}
	if ($file == 'private.php') {$text = 'Параметры: key, count = 10, Возвращаемые значения: total - кол. сообщений, messages - массив приватных сообщений';}
	if ($file == 'forum.php') {$text = 'Параметры: key, id, Возвращаемые значения: id - id темы, author - автор темы, title - заголовок темы, messages - массив постов';}

	echo '<img src="/images/img/gear.gif" alt="gear" /> <b>'.$file.'</b> - '.$text.'<br />';
}

echo '<br />Для доступа к данным нужен API-ключ, которые можно получить на странице мои данные<br /><br />';

include_once ('../themes/footer.php');
?>
