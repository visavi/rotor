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
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

show_title('Список смайлов');

switch ($act):
/**
 * Главная страница
 */
case "index":

	$total = DBM::run()->count('smiles');

	if ($total > 0) {
		if ($start >= $total) {
			$start = 0;
		}

		$smiles = DBM::run()->query("SELECT * FROM `smiles` ORDER BY CHAR_LENGTH(`smiles_code`) ASC LIMIT :start, :limit;", array('start' => intval($start), 'limit' => intval($config['smilelist'])));

		foreach($smiles as $smile) {
			echo '<img src="/images/smiles/'.$smile['smiles_name'].'" alt="" /> — <b>'.$smile['smiles_code'].'</b><br />';
		}

		page_strnavigation('smiles.php?', $config['smilelist'], $start, $total);

		echo 'Всего cмайлов: <b>'.$total.'</b><br /><br />';
	} else {
		show_error('Смайлы не найдены!');
	}
break;

default:
	redirect("smiles.php");
endswitch;

include_once ('../themes/footer.php');
?>
