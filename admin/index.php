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

if (is_admin()) {
	show_title('Панель управления');
	site_verification();
?>
	<div class="b"><img src="/images/img/panel.gif" alt="image" /> <b>Модератор</b></div>
	<img src="/images/img/act.png" alt="image" /> <a href="chat.php">Админ-чат</a> (<?=stats_chat()?>)<br />
	<img src="/images/img/act.png" alt="image" /> <a href="book.php">Гостевая книга</a> (<?=stats_guest()?>)<br />
	<img src="/images/img/act.png" alt="image" /> <a href="forum.php">Форум</a> (<?=stats_forum()?>)<br />
	<img src="/images/img/act.png" alt="image" /> <a href="gallery.php">Галерея</a> (<?=stats_gallery()?>)<br />
	<img src="/images/img/act.png" alt="image" /> <a href="blog.php">Блоги</a> (<?=stats_blog()?>)<br />
	<img src="/images/img/act.png" alt="image" /> <a href="events.php">События</a> (<?=stats_events()?>)<br />
	<img src="/images/img/act.png" alt="image" /> <a href="newload.php">Новые публикации</a> (<?=stats_newload()?>)<br />
	<img src="/images/img/act.png" alt="image" /> <a href="changes.php">Новости RotorCMS</a><br />
	<?=show_admin_links(105);?>

	<?php if (is_admin(array(101, 102, 103))) {?>
		<div class="b"><img src="/images/img/panel.gif" alt="image" /> <b>Старший модер</b></div>
		<img src="/images/img/act.png" alt="image" /> <a href="zaban.php">Бан / Разбан</a><br />
		<img src="/images/img/act.png" alt="image" /> <a href="banlist.php">Список забаненых</a> (<?=stats_banned()?>)<br />
		<img src="/images/img/act.png" alt="image" /> <a href="spam.php">Список жалоб</a> (<?=stats_spam()?>)<br />
		<img src="/images/img/act.png" alt="image" /> <a href="adminlist.php">Список старших</a> (<?=stats_admins()?>)<br />
		<img src="/images/img/act.png" alt="image" /> <a href="reglist.php">Список ожидающих</a> (<?=stats_reglist()?>)<br />
		<img src="/images/img/act.png" alt="image" /> <a href="votes.php">Голосования</a> (<?=stats_votes()?>)<br />
		<?=show_admin_links(103);?>
	<?php }?>

	<?php if (is_admin(array(101, 102))) {?>
		<div class="b"><img src="/images/img/panel.gif" alt="image" /> <b>Администратор</b></div>
		<img src="/images/img/act.png" alt="image" /> <a href="rules.php">Правила сайта</a><br />
		<img src="/images/img/act.png" alt="image" /> <a href="news.php">Новости</a> (<?=stats_allnews()?>)<br />
		<img src="/images/img/act.png" alt="image" /> <a href="users.php">Пользователи</a> (<?=stats_users()?>)<br />
		<img src="/images/img/act.png" alt="image" /> <a href="ban.php">IP-бан панель</a> (<?=stats_ipbanned()?>)<br />
		<img src="/images/img/act.png" alt="image" /> <a href="phpinfo.php">PHP-информация</a> (<?=phpversion()?>)<br />
		<img src="/images/img/act.png" alt="image" /> <a href="load.php">Загруз-центр</a> (<?=stats_load()?>)<br />
		<?=show_admin_links(102);?>
	<?php }?>

	<?php if (is_admin(array(101))) {?>
		<div class="b"><img src="/images/img/panel.gif" alt="image" /> <b>Суперадмин</b></div>
		<img src="/images/img/act.png" alt="image" /> <a href="setting.php">Настройки сайта</a><br />
		<img src="/images/img/act.png" alt="image" /> <a href="cache.php">Очистка кэша</a><br />
		<?=show_admin_links(101);?>

		<?php if ($log == $config['nickname']) {?>
			<img src="/images/img/act.png" alt="image" /> <a href="files.php">Редактирование файлов</a><br />
			<?php show_admin_links();?>
		<?php }?>
	<?php }?>

	<?php if (check_user($config['nickname'])) {?>
		<?php $adminlevel = DB::run() -> querySingle("SELECT `users_level` FROM `users` WHERE `users_login`=? LIMIT 1;", array($config['nickname']));?>

		<?php if ($adminlevel != 101) {?>

			<br /><div class="b"><b><span style="color:#ff0000">Внимание!!! Cуперадминистратор не имеет достаточных прав!</span></b><br />
			Профилю назначен уровень доступа <b><?=$adminlevel?> - <?=user_status($adminlevel)?></b></div>

		<?php }?>

	<?php } else {?>

		<br /><div class="b"><b><span style="color:#ff0000">Внимание!!! Отсутствует профиль суперадмина</span></b><br />
		Профиль администратора <b><?=$config['nickname']?></b> не задействован на сайте</div>

	<?php }?>

	<?php if (file_exists(BASEDIR.'/install') && !empty($config['nickname'])) {?>

		<br /><div class="b"><b><span style="color:#ff0000">Внимание!!! Необходимо удалить директорию install</span></b><br />
		Наличие этой директории может нарушить безопасность сайта. Удалите ее прямо сейчас!</div>

	<?php }?>

<?php
} else {
	redirect ('/index.php');
}

include_once ('../themes/footer.php');
?>
