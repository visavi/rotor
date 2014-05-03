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

if (!empty($_GET['page'])){

	$page = check($_GET['page']);

	if (preg_match('|^[a-z0-9_\-/]+$|i', $page)){

		$file = explode('/', $page);

		if (empty($file[1])){
			$page = $page.'/index';
		}

		if (file_exists(BASEDIR.'/files/'.$page.'.dat')){

			include_once (BASEDIR.'/files/'.$page.'.dat');

		} else {
			notice('Ошибка! Данной страницы не существует!');
			redirect("index.php");
		}
	} else {
		notice('Ошибка! Недопустимое название страницы!');
		redirect("index.php");
	}

} else {
	include_once (DATADIR.'/main/files.dat');
}

include_once ('../themes/footer.php');
?>
