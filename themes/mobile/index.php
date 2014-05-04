<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
header('Content-type:text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
    <title>%TITLE%</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="image_src" href="/images/img/icon.png"/>
    <link rel="stylesheet" href="/themes/mobile/css/style.css" type="text/css"/>
    <link rel="alternate" href="/news/rss.php" title="RSS News" type="application/rss+xml"/>
    <?= include_javascript() ?>
    <meta name="keywords" content="%KEYWORDS%"/>
    <meta name="description" content="%DESCRIPTION%"/>
    <meta name="generator" content="RotorCMS <?= $config['rotorversion'] ?>"/>
</head>
<body>
<!--Design by Vantuz (http://pizdec.ru)-->

<table width="600" border="0" cellspacing="0" cellpadding="0" align="center">
    <tr>
        <td width="10" height="10" style="background-image:url(/themes/mobile/img/border_top_left.gif);"></td>
        <td width="580" height="10" style="background-image:url(/themes/mobile/img/border_top.gif);"></td>
        <td width="10" height="10" style="background-image:url(/themes/mobile/img/border_top_right.gif);"></td>
    </tr>
    <tr valign="top">
        <td width="10" style="background-image:url(/themes/mobile/img/border_left.gif);"></td>
        <td width="580">

            <div class="a" id="up">
                <a href="/"><img src="<?= $config['logotip'] ?>" alt="<?= $config['title'] ?>"/></a><br/>
                <?= $config['logos'] ?>
            </div>

            <?php render('includes/menu'); ?>

            <div class="site">

                <?php render('includes/note', array('php_self' => $php_self)); ?>
