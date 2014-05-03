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
# 0 - Выключить выпадающий список
# 1 - Обычный выпадающий список
# 2 - Выпадающий список без кнопки
if (!empty($config['navigation'])) {
	if (file_exists(DATADIR."/temp/navigation.dat")) {
		$arrnav = unserialize(file_get_contents(DATADIR."/temp/navigation.dat"));
	} else {
		$querynav = DB::run() -> query("SELECT `nav_url`, `nav_title` FROM `navigation` ORDER BY `nav_order` ASC;");
		$arrnav = $querynav -> fetchAll();
	}

	if (count($arrnav) > 0) {
		if ($config['navigation'] == 1) {
			echo '<form method="post" action="/pages/skin.php?act=navigation">';
			echo '<select name="link"><option value="index.php">Быстрый переход</option>';

			foreach($arrnav as $val) {
				echo '<option value="'.$val['nav_url'].'">'.$val['nav_title'].'</option>';
			}

			echo '</select>';
			echo '<input value="Go!" type="submit" /></form>';
		}

		if ($config['navigation'] == 2) {
			echo '<form method="post" action="/pages/skin.php?act=navigation">';
			echo '<select name="link" onchange="this.form.submit();"><option value="index.php">Быстрый переход</option>';

			foreach($arrnav as $val) {
				echo '<option value="'.$val['nav_url'].'">'.$val['nav_title'].'</option>';
			}

			echo '</select>';
			echo '</form>';
		}
	}
}
?>
