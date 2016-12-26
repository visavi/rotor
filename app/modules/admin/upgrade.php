<?php
App::view($config['themes'].'/index');

if (is_admin()) {

show_title('Апгрейд системы');

$app  = new Phinx\Console\PhinxApplication();
$wrap = new Phinx\Wrapper\TextWrapper($app);

$app->setName('RotorCMS by Vantuz - http://visavi.net');
$app->setVersion(VERSION);

$wrap->setOption('configuration', BASEDIR.'/phinx.php');
$wrap->setOption('parser', 'php');
$wrap->setOption('environment', 'default');

echo $wrap->getMigrate();

echo '<i class="fa fa-check"></i> <b>Обновления успешно установлены!</b><br />';

echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    redirect('/');
}

App::view($config['themes'].'/foot');
