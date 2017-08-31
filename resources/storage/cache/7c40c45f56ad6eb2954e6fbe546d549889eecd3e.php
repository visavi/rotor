<?php header("Content-type:application/rss+xml; charset=utf-8"); ?>

<?= '<?xml version="1.0" encoding="utf-8"?>' ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>
            <?php $__env->startSection('title'); ?>
                <?php echo e(Setting::get('title')); ?>

            <?php echo $__env->yieldSection(); ?>
        </title>
        <link><?php echo e(Setting::get('home')); ?>/</link>
        <description>Сообщения RSS - <?php echo e(Setting::get('title')); ?></description>
        <image>
            <url><?php echo e(Setting::get('home')); ?><?php echo e(Setting::get('logotip')); ?></url>
            <title>Сообщения RSS - <?php echo e(Setting::get('title')); ?></title>
            <link><?php echo e(Setting::get('home')); ?>/</link>
        </image>
        <managingEditor><?php echo e(Setting::get('emails')); ?> (<?php echo e(Setting::get('nickname')); ?>)</managingEditor>
        <webMaster><?php echo e(Setting::get('emails')); ?> (<?php echo e(Setting::get('nickname')); ?>)</webMaster>
        <lastBuildDate><?php echo e(date("r", SITETIME)); ?></lastBuildDate>

            <?php echo $__env->yieldContent('content'); ?>

    </channel>
</rss>
