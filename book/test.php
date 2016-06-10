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

echo bb_code(htmlspecialchars('[code]<?php
	echo "|xxxx";

	fuction xxx($xxx){
		var_dump($this->xxx);
	}[/code]'));
echo bb_code('http://visavi.net');
echo bb_code('[url=http://visavi.net]xxx[/url]').'<br>';
echo bb_code('https://visavi.net/%[q]цитата[/q]');
echo bb_code('[img]http://visavi.net/images/img/logo.png[/img]');
echo bb_code(':D :D :D :D :D :D').'<br>';

echo bbCode(htmlspecialchars('[code]<?php
	echo "|xxxx";

	fuction xxx($xxx){
		var_dump($this->xxx);
	}[/code]'));
echo bbCode('http://visavi.net');
echo bbCode('[b]xxx[/b][i]xxx[/i][u]xxx[/u][s]xxx[/s]');
echo bbCode('[youtube]33127IhoPOA[/youtube]');
echo bbCode('[color=#ff0000]dwedwedwed[/color][size=4]edwdwedwedwd[/size][center]по центру[/center][spoiler=заголовок спойлера]текст спойлера[/spoiler]');
echo bbCode('[hide]скрытый текст[/hide][quote]цитата[/quote]').'<br>';
echo bbCode('[url=http://visavi.net]xxx[/url]').'<br>';
echo bbCode('https://visavi.net/%');
echo bbCode('[img]http://visavi.net/images/img/logo.png[/img]');

echo bbCode(':D :D :D :D :D :D');
include_once ('../themes/footer.php');
?>
