<?php $__env->startSection('title'); ?>
    Правила сайта - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Правила сайта</h1>

    <?php if($rules): ?>
        <?php echo App::bbCode($rules['text']); ?><br>
    <?php else: ?>
        <?php echo e(App::showError('Правила сайта еще не установлены!')); ?>

    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>