<?php
include_once __DIR__.'/../../app/bootstrap.php';

$app  = new Phinx\Console\PhinxApplication();
$wrap = new Phinx\Wrapper\TextWrapper($app);

$app->setName('RotorCMS by Vantuz - http://visavi.net');
$app->setVersion(VERSION);

$wrap->setOption('configuration', 'phinx.php');
$wrap->setOption('parser', 'php');
$wrap->setOption('environment', 'default');

header("Content-type:text/html; charset=utf-8");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
    <title>
        Обновление RotorCMS
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="image_src" href="/assets/img/images/icon.png" />
    <link rel="stylesheet" href="/themes/default/css/style.css" type="text/css" />
</head>
<body>

<div class="cs" id="up">
    <img src="/assets/img/images/logo.png" />
</div>
<div class="site">
    <pre>
        <span class="inner-pre" style="font-size: 11px">
            <?php if (empty($_GET['act'])): ?>

                <p style="font-size: 20px">Список доступных миграций</p>

                <?= $wrap->getStatus(); ?>
                <a style="font-size: 18px" href="?act=upgrade">Перейти к обновлению</a>
            <?php elseif($_GET['act'] == 'rollback'): ?>
                <?= $wrap->getRollback(); ?>
            <?php else: ?>
                <?= $wrap->getMigrate(); ?>
                <p style="font-size: 20px">Удалите директории install и upgrade</p>

                <p>Если вы выполняете обновления</p>
            <?php endif; ?>
        </span>
    </pre>
</div>
</body>
</html>


