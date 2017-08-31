<?php $__env->startSection('title'); ?>
    Альбомы пользователей (Стр. <?php echo e($page['current']); ?>) - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Альбомы пользователей</h1>

    <?php if($albums->isNotEmpty()): ?>
        <?php $__currentLoopData = $albums; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <i class="fa fa-picture-o"></i>
            <b><a href="/gallery/album/<?php echo e($data->login); ?>"><?php echo e($data->login); ?></a></b> (<?php echo e($data['cnt']); ?> фото / <?php echo e($data['comments']); ?> комм.)<br>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php echo e(App::pagination($page)); ?>


        Всего альбомов: <b><?php echo e($page['total']); ?></b><br><br>
    <?php else: ?>
        <?php echo e(show_error('Альбомов еще нет!')); ?>

    <?php endif; ?>

    <i class="fa fa-arrow-circle-left"></i> <a href="/gallery">В галерею</a><br>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>