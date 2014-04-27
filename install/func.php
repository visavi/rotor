<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
function split_sql($sql) {
	$sql = trim($sql);
	$sql = preg_replace("|\n#[^\n]*\n|", "\n", $sql);
	$buffer = array();
	$ret = array();
	$in_string = false;
	for ($i = 0; $i < strlen($sql) - 1; $i++) {
		if ($sql[$i] == ";" && !$in_string) {
			$ret[] = substr($sql, 0, $i);
			$sql = substr($sql, $i + 1);
			$i = 0;
		}
		if ($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\") {
			$in_string = false;
		} elseif (!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset ($buffer[0]) || $buffer[0] != "\\")) {
			$in_string = $sql[$i];
		}
		if (isset ($buffer[1])) {
			$buffer[0] = $buffer[1];
		}
		$buffer[1] = $sql[$i];
	}
	if (!empty ($sql)) {
		$ret[] = $sql;
	}
	return ($ret);
}
// ----------------------------------------------------------------------------//
function parsePHPModules() {
	ob_start();
	phpinfo(INFO_MODULES);
	$s = ob_get_contents();
	ob_end_clean();

	$s = strip_tags($s, '<h2><th><td>');
	$s = preg_replace('/<th[^>]*>([^<]+)<\/th>/', "<info>\\1</info>", $s);
	$s = preg_replace('/<td[^>]*>([^<]+)<\/td>/', "<info>\\1</info>", $s);
	$vTmp = preg_split('/(<h2[^>]*>[^<]+<\/h2>)/', $s, -1, PREG_SPLIT_DELIM_CAPTURE);
	$vModules = array();
	for ($i = 1;$i < count($vTmp);$i++) {
		if (preg_match('/<h2[^>]*>([^<]+)<\/h2>/', $vTmp[$i], $vMat)) {
			$vName = trim($vMat[1]);
			$vTmp2 = explode("\n", $vTmp[$i + 1]);
			foreach ($vTmp2 AS $vOne) {
				$vPat = '<info>([^<]+)<\/info>';
				$vPat3 = "/$vPat\s*$vPat\s*$vPat/";
				$vPat2 = "/$vPat\s*$vPat/";
				if (preg_match($vPat3, $vOne, $vMat)) {
					$vModules[$vName][trim($vMat[1])] = array(trim($vMat[2]), trim($vMat[3]));
				} elseif (preg_match($vPat2, $vOne, $vMat)) {
					$vModules[$vName][trim($vMat[1])] = trim($vMat[2]);
				}
			}
		}
	}
	return $vModules;
}
// ------------------------------------------------------------------//
function getModuleSetting($pModuleName, $pSetting) {
	$vModules = parsePHPModules();

	if (!empty($vModules[$pModuleName][$pSetting])) {
		return $vModules[$pModuleName][$pSetting];
	}
}
// ------------------ Функция генерирования паролей --------------------//
function generate_password($length = "") {
	if (empty($length)) {
		$length = mt_rand(10, 12);
	}
	$salt = str_split('aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ0123456789');

	$makepass = "";
	for ($i = 0; $i < $length; $i++) {
		$makepass .= $salt[array_rand($salt)];
	}
	return $makepass;
}
// ------------------ Функция преобразования в нижний регистр для UTF ------------------//
function utf_lower($str) {
	if (function_exists('mb_strtolower')) return mb_strtolower($str, 'utf-8');

	$arraytolower = array('А' => 'а', 'Б' => 'б', 'В' => 'в', 'Г' => 'г', 'Д' => 'д', 'Е' => 'е', 'Ё' => 'ё', 'Ж' => 'ж', 'З' => 'з', 'И' => 'и', 'Й' => 'й', 'К' => 'к', 'Л' => 'л', 'М' => 'м', 'Н' => 'н', 'О' => 'о', 'П' => 'п', 'Р' => 'р', 'С' => 'с', 'Т' => 'т', 'У' => 'у', 'Ф' => 'ф', 'Х' => 'х', 'Ц' => 'ц', 'Ч' => 'ч', 'Ш' => 'ш', 'Щ' => 'щ', 'Ь' => 'ь', 'Ъ' => 'ъ', 'Ы' => 'ы', 'Э' => 'э', 'Ю' => 'ю', 'Я' => 'я',
		'A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd', 'E' => 'e', 'I' => 'i', 'F' => 'f', 'G' => 'g', 'H' => 'h', 'J' => 'j', 'K' => 'k', 'L' => 'l', 'M' => 'm', 'N' => 'n', 'O' => 'o', 'P' => 'p', 'Q' => 'q', 'R' => 'r', 'S' => 's', 'T' => 't', 'U' => 'u', 'V' => 'v', 'W' => 'w', 'X' => 'x', 'Y' => 'y', 'Z' => 'z');

	return strtr($str, $arraytolower);
}

?>
