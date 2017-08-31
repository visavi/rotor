<?php $__env->startSection('title'); ?>
    Статьи пользователей - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Статьи пользователей</h1>

    <?php if($blogs->isNotEmpty()): ?>
        <?php $__currentLoopData = $blogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <i class="fa fa-pencil"></i>
            <b><a href="/blog/active/articles?user=<?php echo e($data->login); ?>"><?php echo e($data->login); ?></a></b> (<?php echo e($data['cnt']); ?> cтатей / <?php echo e($data->comments); ?> комм.)<br>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php echo e(App::pagination($page)); ?>


        Всего пользователей: <b><?php echo e($page['total']); ?></b><br><br>
    <?php else: ?>
        <?php echo e(show_error('Статей еще нет!')); ?>

    <?php endif; ?>

    <?php App::view('includes/back', ['link' => '/blog', 'title' => 'К блогам']); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>