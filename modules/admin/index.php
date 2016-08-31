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
	site_version();
?>
	<div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>Модератор</b></div>
	<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="chat.php">Админ-чат</a> (<?=stats_chat()?>)<br />
	<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="book.php">Гостевая книга</a> (<?=stats_guest()?>)<br />
	<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="forum.php">Форум</a> (<?=stats_forum()?>)<br />
	<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="gallery.php">Галерея</a> (<?=stats_gallery()?>)<br />
	<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="blog.php">Блоги</a> (<?=stats_blog()?>)<br />
	<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="events.php">События</a> (<?=stats_events()?>)<br />
	<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="newload.php">Новые публикации</a> (<?=stats_newload()?>)<br />

	<?=show_admin_links(105);?>

	<?php if (is_admin(array(101, 102, 103))) {?>
		<div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>Старший модер</b></div>
		<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="zaban.php">Бан / Разбан</a><br />
		<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="banlist.php">Список забаненых</a> (<?=stats_banned()?>)<br />
		<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="spam.php">Список жалоб</a> (<?=stats_spam()?>)<br />
		<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="adminlist.php">Список старших</a> (<?=stats_admins()?>)<br />
		<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="reglist.php">Список ожидающих</a> (<?=stats_reglist()?>)<br />
		<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="votes.php">Голосования</a> (<?=stats_votes()?>)<br />
		<?=show_admin_links(103);?>
	<?php }?>

	<?php if (is_admin(array(101, 102))) {?>
		<div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>Администратор</b></div>
		<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="rules.php">Правила сайта</a><br />
		<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="news.php">Новости</a> (<?=stats_allnews()?>)<br />
		<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="users.php">Пользователи</a> (<?=stats_users()?>)<br />
		<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="ban.php">IP-бан панель</a> (<?=stats_ipbanned()?>)<br />
		<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="phpinfo.php">PHP-информация</a> (<?=phpversion()?>)<br />
		<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="load.php">Загруз-центр</a> (<?=stats_load()?>)<br />
		<?=show_admin_links(102);?>
	<?php }?>

	<?php if (is_admin(array(101))) {?>
		<div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>Суперадмин</b></div>
		<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="setting.php">Настройки сайта</a><br />
		<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="cache.php">Очистка кэша</a><br />
		<?=show_admin_links(101);?>

		<?php if ($log == $config['nickname']) {?>
			<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="files.php">Редактирование файлов</a><br />
			<?php show_admin_links();?>
		<?php }?>
	<?php }?>

	<?php if ($admin = user($config['nickname'])) {?>
		<?php if ($admin['users_level'] != 101) {?>

			<br /><div class="b"><b><span style="color:#ff0000">Внимание!!! Cуперадминистратор не имеет достаточных прав!</span></b><br />
			Профилю назначен уровень доступа <b><?=$admin['users_level']?> - <?=user_status($admin['users_level'])?></b></div>

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
