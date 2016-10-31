<?php echo $__env->make(App::setting('themes').'.index' , array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>

<div style="text-align:center">
    <?php include_once (STORAGE.'/advert/top_all.dat'); ?>

    <?= show_advertuser(); /* Реклама за игровые деньги */ ?>
</div>

    <?php echo e(App::getFlash()); ?>


    <?php echo $__env->yieldContent('content'); ?>

<?php echo $__env->make(App::setting('themes').'.foot', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
