<?php $__env->startSection('title', 'Ошибка 404 - @parent'); ?>

<?php $__env->startSection('content'); ?>

    <?php $images = glob(BASEDIR.'/images/errors/*.png'); ?>

    <div class="row">
        <div class="col-md-4 text-right">
            <img src="/images/errors/<?php echo e(basename($images[array_rand($images)])); ?>" alt="error 404" />
        </div>
        <div class="col-md-8">
            <h3>Ошибка 404!</h3>
            <div class="lead">Данной страницы не существует!</div>

            <?php if($message): ?>
                <div class="lead"><?php echo e($message); ?></div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>