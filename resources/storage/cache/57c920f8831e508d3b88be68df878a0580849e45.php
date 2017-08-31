<?php $__env->startSection('title'); ?>
    Последние проголосовавшие - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1><?php echo e($vote->title); ?></h1>

    <i class="fa fa-bar-chart"></i> Голосов: <?php echo e($vote['count']); ?><br><br>

    <?php if($voters->isNotEmpty()): ?>
        <?php $__currentLoopData = $voters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php echo user_gender($voter['user']); ?> <?php echo profile($voter['user']); ?> (<?php echo e(date_fixed($voter['created_at'])); ?>)<br>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>
        <?php echo e(App::showError('В голосовании никто не участвовал!')); ?>

    <?php endif; ?>
    <br>
    <i class="fa fa-arrow-circle-left"></i> <a href="/votes/<?php echo e($vote->id); ?>">Вернуться</a><br>
    <i class="fa fa-arrow-circle-up"></i> <a href="/votes">К голосованиям</a><br>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>