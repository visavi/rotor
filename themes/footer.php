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
	header('Location: /index.php');
	exit;
}

include_once (BASEDIR."/includes/counters.php");

include_once (DATADIR.'/advert/bottom_all.dat');

// -------- Удаление флеш сообщения ---------//
if (isset($_SESSION['note'])) {
	unset($_SESSION['note']);
}

$_SESSION['counton']++;


// Определяет точное название страницы где находится пользователь
if (is_user() && !empty($config['newtitle'])){
	DB::run()->query("UPDATE `visit` SET `visit_page`=? WHERE `visit_user`=? LIMIT 1;", array($config['newtitle'], $log));
}
?>

<?php if ($ip != '127.0.0.1'){?>
<!-- Yandex.Metrika counter -->

<!-- /Yandex.Metrika counter -->
<?php } ?>

<?php
include_once (BASEDIR.'/themes/'.$config['themes'].'/foot.php');
ob_end_flush();
exit();
?>
