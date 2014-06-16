<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
header("Content-type:text/html; charset=utf-8");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
	<title>%TITLE%</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
	<link rel="image_src" href="/images/img/icon.png" />
	<link rel="stylesheet" href="/themes/bluewater/css/style.css" type="text/css" />
	<link rel="alternate" href="/news/rss.php" title="RSS News" type="application/rss+xml" />
	<?= include_javascript() ?>
	<meta name="keywords" content="%KEYWORDS%" />
	<meta name="description" content="%DESCRIPTION%" />
	<meta name="generator" content="RotorCMS <?= $config['rotorversion'] ?>" />
</head>
<body>
<!--Design by WmLiM (http://komwap.ru)-->

<div id="wrap">
	<div id="header">
		<h1 id="logo-text"><a href="/"><?= $config['title'] ?></a></h1>
		<p id="slogan"><?= $config['logos'] ?></p>

		<div id="header-links">
			<p>

<?php
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
?>
			</p>
		</div>
	</div>

	<!-- navigation -->
	<div id="menu">
		<ul>
			<li><a href="/">Главная</a></li>
			<li><a href="/forum">Форум</a></li>
			<li><a href="/load">Загрузки</a></li>
			<li><a href="/blog">Блоги</a></li>
			<li><a href="/gallery">Галерея</a></li>
		</ul>
	</div>

	<!-- content-wrap starts here -->
	<div id="content-wrap">
		<div id="sidebar">

			<?php
			if (is_user()) {
				include (DATADIR.'/main/menu.dat');
			} else {
				include (DATADIR.'/main/recent.dat');
			}
			?>
		</div>
		<div id="main">
			<div class="body_center">
