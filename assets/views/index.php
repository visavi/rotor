<?php $config['newtitle'] = 'Главная страница'; ?>

<i class="fa fa-circle fa-lg"></i> <a href="/news/">Новости сайта</a> (<?=stats_news()?>)<br /> <?=last_news()?>

<div class="b">
	<i class="fa fa-comment fa-lg"></i> <b><a href="/pages/index.php?act=recent">Общение</a></b>
</div>
<i class="fa fa-circle fa-lg"></i> <a href="/book/">Гостевая книга</a> (<?=stats_guest()?>)<br />
<i class="fa fa-circle fa-lg"></i> <a href="/gallery/">Фотогалерея</a> (<?=stats_gallery()?>)<br />
<i class="fa fa-circle fa-lg"></i> <a href="/votes/">Голосования</a> (<?=stats_votes()?>)<br />
<i class="fa fa-circle fa-lg"></i> <a href="/pages/offers.php">Предложения / Проблемы</a> (<?=stats_offers()?>)<br />
<i class="fa fa-circle fa-lg"></i> <a href="/chat/">Мини-чат</a> (<?= stats_minichat() ?>)<br />


<div class="b">
	<i class="fa fa-calendar fa-lg"></i>
	<b><a href="/events/">События</a></b> (<?=stats_events()?>)
</div>
<?=show_events()?>

<div class="b">
	<i class="fa fa-forumbee fa-lg"></i>
	<b><a href="/forum/">Форум</a></b> (<?=stats_forum()?>)
</div>
<?=recenttopics()?>

<div class="b">
	<i class="fa fa-download fa-lg"></i> <b><a href="/load/">Загрузки</a></b> (<?=stats_load()?>)
</div>
<?=recentfiles()?>

<div class="b">
	<i class="fa fa-globe fa-lg"></i>
	<b><a href="/blog/">Блоги</a></b> (<?=stats_blog()?>)
</div>
<?=recentblogs()?>

<div class="b">
	<i class="fa fa-cog fa-lg"></i>
	<b><a href="/pages/index.php">Сервисы сайта</a></b>
</div>
<i class="fa fa-circle fa-lg"></i> <a href="/mail/">Обратная связь</a><br />
<i class="fa fa-circle fa-lg"></i> <a href="/board/">Доска объявлений</a> (<?= stats_board() ?>)<br />
<i class="fa fa-circle fa-lg"></i> <a href="/pages/userlist.php">Список юзеров</a> (<?=stats_users()?>)<br />
<i class="fa fa-circle fa-lg"></i> <a href="/pages/adminlist.php">Администрация</a> (<?=stats_admins()?>)<br />
<i class="fa fa-circle fa-lg"></i> <a href="/pages/index.php?act=stat">Информация</a><br />
<i class="fa fa-circle fa-lg"></i> <a href="/pages/index.php?act=partners">Партнеры и друзья</a><br />

<div class="b"><i class="fa fa-comment fa-lg"></i> <b>Курсы валют</b></div>
<?php include_once(BASEDIR.'/includes/courses.php') ?>
