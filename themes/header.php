<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
if (!defined('BASEDIR')) {
	exit(header('Location: /index.php'));
}

// ------------------------ Отключение кеширования -----------------------------//
if (!empty($config['cache'])){
	header("Cache-Control: public");
	header("Expires: ".date("r", time() + 600));
} else {
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Expires: ".date("r"));
}

$browser_detect = new Mobile_Detect();

// ------------------------ Автоопределение системы -----------------------------//
if (!is_user() || empty($config['themes'])) {
	 if (!empty($config['touchthemes'])) {
		if ($browser_detect->isTablet()) {
			$config['themes'] = $config['touchthemes'];
		}
	}

	if (!empty($config['webthemes'])) {
		if (!$browser_detect->isMobile() && !$browser_detect->isTablet()) {
			$config['themes'] = $config['webthemes'];
		}
	}
}

if ($config['closedsite'] == 2 && !is_admin() && !strsearch($php_self, array('/pages/closed.php', '/input.php'))) {
	redirect('/pages/closed.php');
}

if ($config['closedsite'] == 1 && !is_user() && !strsearch($php_self, array('/pages/login.php', '/pages/registration.php', '/mail/lostpassword.php', '/input.php'))) {
	notice('Для входа на сайт необходимо авторизоваться!');
	redirect('/pages/login.php');
}

if (empty($config['themes']) || !file_exists(BASEDIR.'/themes/'.$config['themes'].'/index.php')) {
	$config['themes'] = 'default';
}

include_once (BASEDIR.'/themes/'.$config['themes'].'/index.php');
?>

<div style="text-align:center">
	<?php include_once (DATADIR.'/advert/top_all.dat'); ?>

	<?= show_advertadmin(); /* Админска реклама */ ?>
	<?= show_sponsors(); /* Реклама спонсоров */ ?>
	<?= show_advertuser(); /* Реклама за игровые деньги */ ?>
</div>

<?php render('includes/note', compact('php_self')); ?>
