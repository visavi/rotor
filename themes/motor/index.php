<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
header('Content-type:text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
	<title>%TITLE%</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
	<link rel="image_src" href="/images/img/icon.png" />
	<link rel="stylesheet" href="/themes/motor/css/style.css" type="text/css" />
	<link rel="alternate" href="/news/rss.php" title="RSS News" type="application/rss+xml" />
	<?= include_javascript() ?>
	<meta name="keywords" content="%KEYWORDS%" />
	<meta name="description" content="%DESCRIPTION%" />
	<meta name="generator" content="RotorCMS <?= $config['rotorversion'] ?>" />
</head>
<body>
<!--Design by Vantuz (http://visavi.net)-->

<div id="wrapper">
	<div class="main" id="up">

		<div class="panelTop">
			<img src="/themes/motor/img/panel_top.gif" alt="" />
		</div>
		<div class="backgr_top">
			<div class="content">
				<div class="logo">
					<a href="/"><img src="/images/img/logo.png" alt="<?=$config['title']?>" /></a>
				</div>

				<div class="menu">
					<a href="/forum">Форум</a>
					<a href="/book">Гостевая</a>
					<a href="/news">Новости</a>
					<a href="/load">Скрипты</a>
					<a href="/blog">Блоги</a>

					<span class="mright">

<?php if (is_user()): ?>
	<?php if (is_admin()): ?>

		<?php if (stats_spam()>0): ?>
			<a href="/admin/spam.php"><span style="color:#ff0000">Спам!</span></a>
		<?php endif; ?>

		<?php if ($udata['users_newchat']<stats_newchat()): ?>
			<a href="/admin/chat.php"><span style="color:#ff0000">Чат</span></a>
		<?php endif; ?>

			<a href="/admin/">Панель</a>
	<?php endif; ?>

	<a href="/pages/index.php?act=menu">Меню</a>
	<a href="/input.php?act=exit" onclick="return confirm('Вы действительно хотите выйти?')">Выход</a>

<?php else: ?>
	<a href="/pages/login.php">Авторизация</a>/
	<a href="/pages/registration.php">Регистрация</a>
<?php endif; ?>

					</span>

				</div>
			</div>
		</div>

		<div class="backgr">
			<div class="bcontent">
				<div class="mcontentwide">
