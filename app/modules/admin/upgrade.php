<?php
App::view(Setting::get('themes').'/index');

if (is_admin()) {

//show_title('Апгрейд системы');

$app  = new Phinx\Console\PhinxApplication();
$wrap = new Phinx\Wrapper\TextWrapper($app);

$app->setName('RotorCMS by Vantuz - http://visavi.net');
$app->setVersion(VERSION);

$wrap->setOption('configuration', BASEDIR.'/phinx.php');
$wrap->setOption('parser', 'php');
$wrap->setOption('environment', 'default');
?>

<pre>
    <span class="inner-pre" style="font-size: 11px">
        <?= $wrap->getMigrate(); ?>
    </span>
</pre>


<?php
echo '<i class="fa fa-check"></i> <b>Установлена актуальная версия RotorCMS</b><br />';

echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    App::redirect('/');
}

App::view(Setting::get('themes').'/foot');
