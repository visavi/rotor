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

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

/*	$skins = (isset($_REQUEST['skins'])) ? check($_REQUEST['skins']) : '';

	if (preg_match('|^[a-z0-9_\-]+$|i', $skins)){
		if (file_exists(BASEDIR.'/themes/'.$skins.'/index.php')){
			unset($_SESSION['my_themes']);
			$_SESSION['my_themes'] = $skins;
		}
	}

	notice('Тема успешно изменена!');
	redirect('/index.php');*/
break;

############################################################################################
##                                    Переход по навигации                                ##
############################################################################################
case 'navigation':
	$link = (isset($_POST['link'])) ? check($_POST['link']) : 'index.php';
	redirect('/'.$link);
endswitch;

include_once ('../themes/footer.php');
?>
