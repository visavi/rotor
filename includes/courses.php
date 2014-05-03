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

// libxml_use_internal_errors(true);

if (@filemtime(DATADIR."/temp/courses.dat") < time()-3600 || @filesize(DATADIR."/temp/courses.dat") == 0) {

	#$xml_str = file_get_contents("http://www.cbr.ru/scripts/XML_daily.asp");
	if ($xml_str = curl_connect("http://www.cbr.ru/scripts/XML_daily.asp")){

		$xml = new SimpleXMLElement($xml_str);

		$courses = array();
		$courses['Date'] = strval($xml->attributes()->Date);

		foreach($xml->Valute as $item){
			$courses[strval($item->CharCode)] =  array('name' => strval($item->Name), 'value' => strval($item->Value), 'nominal' => strval($item->Nominal));
		}

		file_put_contents(DATADIR."/temp/courses.dat", serialize($courses), LOCK_EX);
	}
}

$courses = @unserialize(file_get_contents(DATADIR."/temp/courses.dat"));

if (!empty($courses)){

	echo '<b>Курсы валют</b> ('.$courses['Date'].')<br />';
	echo '<b>'.$courses['USD']['nominal'].' '.$courses['USD']['name'].'</b> - '.$courses['USD']['value'].'<br />';
	echo '<b>'.$courses['EUR']['nominal'].' '.$courses['EUR']['name'].'</b> - '.$courses['EUR']['value'].'<br />';
	echo '<b>'.$courses['UAH']['nominal'].' '.$courses['UAH']['name'].'</b> - '.$courses['UAH']['value'].'<br />';

} else {
	show_error('Ошибка! Не удалось загрузить последние курсы валют!');
}
?>
