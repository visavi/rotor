<?php $__env->startSection('title'); ?>
    Список всех статей <?php echo e($user->login); ?> (Стр. <?php echo e($page['current']); ?>) - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Список всех статей <?php echo e($user->login); ?></h1>

    <?php if($blogs->isNotEmpty()): ?>
        <?php $__currentLoopData = $blogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="b">
                <i class="fa fa-pencil"></i>
                <b><a href="/article/<?php echo e($data['id']); ?>"><?php echo e($data['title']); ?></a></b> (<?php echo format_num($data['rating']); ?>)
            </div>

            <div>Автор: <?php echo profile($data['user']); ?> (<?php echo e(date_fixed($data['time'])); ?>)<br>
                <i class="fa fa-comment"></i> <a href="/article/<?php echo e($data['id']); ?>/comments">Комментарии</a> (<?php echo e($data['comments']); ?>)
                <a href="/article/<?php echo e($data['id']); ?>/end">&raquo;</a>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php echo e(App::pagination($page)); ?>


        Всего статей: <b><?php echo e($page['total']); ?></b><br>
    <?php else: ?>
        <?php echo e(show_error('Статей еще нет!')); ?>

    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>