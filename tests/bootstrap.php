<?php

require __DIR__ . '/../app/bootstrap.php';

$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->addPsr4('Tests\\', __DIR__, true);
$classLoader->register();


