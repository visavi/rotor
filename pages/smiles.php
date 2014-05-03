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
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case "index":

	$total = DB::run() -> querySingle("SELECT count(*) FROM `smiles`;");

	if ($total > 0) {
		if ($start >= $total) {
			$start = 0;
		}

		$querysmiles = DB::run() -> query("SELECT * FROM `smiles` ORDER BY LENGTH(`smiles_code`) ASC LIMIT ".$start.", ".$config['smilelist'].";");

		while ($data = $querysmiles -> fetch()) {
			echo '<img src="/images/smiles/'.$data['smiles_name'].'" alt="" /> — <b>'.$data['smiles_code'].'</b><br />';
		}

		page_strnavigation('smiles.php?', $config['smilelist'], $start, $total);

		echo 'Всего cмайлов: <b>'.$total.'</b><br /><br />';
	} else {
		show_error('В данной категории смайлов нет!');
	}
break;

default:
	redirect("smiles.php");
endswitch;

include_once ('../themes/footer.php');
?>
