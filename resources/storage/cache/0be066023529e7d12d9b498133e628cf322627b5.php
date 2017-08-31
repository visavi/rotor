<?php $__env->startSection('title'); ?>
    Облако тегов - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <h1>Облако тегов</h1>
    <div style="text-align:center">
        <?php foreach ($tags as $key => $val): ?>

            <?php $fontsize = ($min != $max) ? round((($val - $min) / ($max - $min)) * 110 + 100) : 100; ?>

            <a href="/blog/tags/<?=urlencode($key)?>"><span style="font-size:<?=$fontsize?>%"><?=$key?></span></a>
        <?php endforeach; ?>
    </div><br>

    <?php App::view('includes/back', ['link' => '/blog', 'title' => 'К блогам']); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>