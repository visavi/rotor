<?php $__env->startSection('title'); ?>
    Список смайлов - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Список смайлов</h1>

    <?php if($smiles): ?>
        <?php $__currentLoopData = $smiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $smile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <img src="/uploads/smiles/<?php echo e($smile['name']); ?>" alt=""> — <b><?php echo e($smile['code']); ?></b><br>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php echo e(App::pagination($page)); ?>


        Всего cмайлов: <b><?php echo e($page['total']); ?></b><br><br>
    <?php else: ?>
        <?php echo e(App::showError('Смайлы не найдены!')); ?>

    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>