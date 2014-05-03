<?php
header("Content-type:text/html; charset=utf-8");
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru"><head>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
echo '<title>%TITLE%</title>';
echo '<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />';
echo '<link rel="stylesheet" href="/themes/bluewater/css/style.css" type="text/css" media="screen" />';
echo '<link rel="alternate" href="/news/rss.php" title="RSS News" type="application/rss+xml" />';
include_javascript();
echo '<meta name="keywords" content="%KEYWORDS%" />';
echo '<meta name="description" content="%DESCRIPTION%" />';
echo '<meta name="generator" content="RotorCMS '.$config['rotorversion'].'" />';
echo '</head><body>';
echo '<!--Design by WmLiM (http://komwap.ru)-->';

echo '<div id="wrap">

	<!--header -->
	<div id="header">

		<h1 id="logo-text"><a href="'.$config['home'].'">'.$config['title'].'</a></h1>
		<p id="slogan">'.$config['logos'].'</p>

		<div id="header-links">
			<div><p>';

if (is_user()){

	echo user_gender($log).profile($log);
	if (is_admin()){

		echo ' | <a href="/admin/index.php">Админ-панель</a>';
		if (stats_spam()>0){
		echo ' | <a href="/admin/spam.php"><span style="color:#ff0000">Спам!</span></a>';
		}

		if ($udata['users_newchat']<stats_newchat()){
		echo ' | <a href="/admin/chat.php"><span style="color:#ff0000">Чат</span></a>';
		}

	}

} else {
	echo '<a href="/pages/login.php">Авторизация</a> | ';
	echo '<a href="/pages/registration.php">Регистрация</a>';
}

echo '</p>
		</div>
	</div>

	</div>

	<!-- navigation -->
	<div id="menu">
		<ul>
			<li><a href="'.$config['home'].'">Главная</a></li>
			<li><a href="'.$config['home'].'/forum">Форум</a></li>
			<li><a href="'.$config['home'].'/load">Загрузки</a></li>
			<li><a href="'.$config['home'].'/blog">Блоги</a></li>
			<li><a href="'.$config['home'].'/gallery">Галерея</a></li>
		</ul>
	</div>

	<!-- content-wrap starts here -->
	<div id="content-wrap">

    <div id="sidebar">';

if (is_user()) {
  include (DATADIR.'/main/menu.dat');
} else {
	include (DATADIR.'/main/recent.dat');
}

echo '</div>
 		<div id="main">
        <div class="body_center">';
render('includes/note', array('php_self' => $php_self));
?>
