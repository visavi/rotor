<?php
header("Content-type:text/html; charset=utf-8");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
    <title>
        <?php $__env->startSection('title'); ?>
            <?php echo e(App::setting('title')); ?>

        <?php echo $__env->yieldSection(); ?>
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="image_src" href="/assets/img/images/icon.png" />
    <?php $__env->startSection('styles'); ?>
        <?= include_style() ?>
    <?php echo $__env->yieldSection(); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
    <link rel="stylesheet" href="/themes/default/css/style.css" type="text/css" />
    <link rel="alternate" href="/news/rss" title="RSS News" type="application/rss+xml" />

    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="<?php echo $__env->yieldContent('description', App::setting('description')); ?>">
    <meta name="keywords" content="<?php echo $__env->yieldContent('keywords', App::setting('keywords')); ?>">
    <meta name="generator" content="RotorCMS <?php echo e(App::setting('rotorversion')); ?>" />
</head>
<body>

<div class="cs" id="up">
    <!-- <a href="/"><span class="logotype"><?php echo e(App::setting('title')); ?></span></a><br /> -->
    <a href="/"><img src="<?php echo e(App::setting('logotip')); ?>" alt="<?php echo e(App::setting('title')); ?>" /></a><br />
    <?php echo e(App::setting('logos')); ?>

</div>

<?php render('includes/menu'); ?>

<div class="site">
<?= render('includes/note'); /*Временно пока шаблоны подключаются напрямую*/ ?>
