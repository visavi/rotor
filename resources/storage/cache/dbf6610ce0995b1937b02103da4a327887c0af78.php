<?php echo $__env->make(setting('themes').'.index' , array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>

<div style="text-align:center">
    <?php echo $__env->make('advert.top_all', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>

    <?= show_advertuser(); ?>
</div>

<?php echo e(getFlash()); ?>


<?php echo $__env->yieldContent('content'); ?>

<?php echo $__env->make('advert.bottom_all', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php echo $__env->make(setting('themes').'.foot', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
