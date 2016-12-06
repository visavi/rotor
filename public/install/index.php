<?php
include_once __DIR__.'/../../app/bootstrap.php';

$app  = new Phinx\Console\PhinxApplication();
$wrap = new Phinx\Wrapper\TextWrapper($app);

$wrap->setOption('configuration', BASEDIR.'/phinx.php');
$wrap->setOption('parser', 'php');
$wrap->setOption('environment', 'default');

echo $wrap->getStatus();
echo $wrap->getRollback(null, 0);
echo $wrap->getMigrate();
echo $wrap->getSeed();
