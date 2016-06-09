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

echo bb_code('http://visavi.net');
echo bb_code('[url=http://visavi.net]xxx[/url]').'<br>';
echo bb_code('https://visavi.net/%');
echo bb_code('[img]http://visavi.net/images/img/logo.png[/img]');
echo bb_code(':D :D :D :D :D :D').'<br>';

echo bbCode('http://visavi.net');
echo bbCode('[url=http://visavi.net]xxx[/url]').'<br>';
echo bbCode('https://visavi.net/%');
echo bbCode('[img]http://visavi.net/images/img/logo.png[/img]');

echo bbCode(':D :D :D :D :D :D');
include_once ('../themes/footer.php');
?>
