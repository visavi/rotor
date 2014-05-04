<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
header("Content-type:text/html; charset=utf-8");

function amendment($skinxhtml)
{
    $skinxhtml = str_replace('images/img/act.png', 'themes/lite/img/act.gif', $skinxhtml);
    $skinxhtml = str_replace('<hr />', '<br />', $skinxhtml);
    return $skinxhtml;
}

ob_start('amendment');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
    <title>%TITLE%</title>
    <?= include_javascript() ?>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <style type="text/css">
        body {
            text-decoration: none;
            font-family: arial;
            font-size: 11pt;
            margin: 5px;
            padding: 5px;
            background: #fff;
            color: #000;
        }

        .a {
            background-color: #333;
            margin: 5px;
            padding: 5px;
            color: #fff;
            font-style: italic;
        }

        .x {
            background-color: #eee;
            margin: 10px;
            padding: 15px;
        }

        .img {
            float: left;
            margin: 1px 5px 1px 3px;
        }
    </style>
    <link rel="shortcut icon" href="/images/img/icon.png" type="image/x-icon"/>
    <meta name="keywords" content="%KEYWORDS%"/>
    <meta name="description" content="%DESCRIPTION%"/>
    <meta name="generator" content="RotorCMS <?= $config['rotorversion'] ?>"/>
</head>
<body>
<!--Design by Vantuz (http://pizdec.ru)-->

<div class="x" id="up">
    <div class="a"><a href="/"><?= $config['logos'] ?></a></div>

    <?php render('includes/menu'); ?>

    <?php render('includes/note', array('php_self' => $php_self)); ?>

    <div>

