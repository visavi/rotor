<?php $config['newtitle'] = 'Главная страница'; ?>

<img src="/images/img/act.png" alt="image" /> <a href="/news">Новости сайта</a> (<?=stats_news()?>)<br /> <?=last_news()?>

<div class="b">
	<img src="/images/img/ice-cream.gif" alt="image" /> <b><a href="/pages/index.php?act=recent">Общение</a></b>
</div>
<img src="/images/img/act.png" alt="image" /> <a href="/book">Гостевая книга</a> (<?=stats_guest()?>)<br />
<img src="/images/img/act.png" alt="image" /> <a href="/gallery">Фотогалерея</a> (<?=stats_gallery()?>)<br />
<img src="/images/img/act.png" alt="image" /> <a href="/votes">Голосования</a> (<?=stats_votes()?>)<br />
<img src="/images/img/act.png" alt="image" /> <a href="/pages/offers.php">Предложения / Проблемы</a> (<?=stats_offers()?>)<br />

<div class="b"><img src="/images/img/cup.gif" alt="image" /> <b><a href="/events">События</a></b> (<?=stats_events()?>)</div>
<?=show_events()?>

<div class="b"><img src="/images/img/lollipop.gif" alt="image" /> <b><a href="/forum">Форум</a></b> (<?=stats_forum()?>)</div>
<?=recenttopics()?>

<div class="b"><img src="/images/img/fruit-lime.gif" alt="image" /> <b><a href="/load">Загрузки</a></b> (<?=stats_load()?>)</div>
<?=recentfiles()?>

<div class="b"><img src="/images/img/cookie.gif" alt="image" /> <b><a href="/blog">Блоги</a></b> (<?=stats_blog()?>)</div>
<?=recentblogs()?>

<div class="b"><img src="/images/img/fruit.gif" alt="image" /> <b><a href="/pages/index.php">Сервисы сайта</a></b></div>
<img src="/images/img/act.png" alt="image" /> <a href="/mail">Обратная связь</a><br />
<img src="/images/img/act.png" alt="image" /> <a href="/pages/userlist.php">Список юзеров</a> (<?=stats_users()?>)<br />
<img src="/images/img/act.png" alt="image" /> <a href="/pages/adminlist.php">Администрация</a> (<?=stats_admins()?>)<br />
<img src="/images/img/act.png" alt="image" /> <a href="/pages/index.php?act=stat">Информация</a><br />
<img src="/images/img/act.png" alt="image" /> <a href="/pages/index.php?act=partners">Партнеры и друзья</a><br />
