<?php $__env->startSection('title'); ?>
    Топ статей (Стр. <?php echo e($page['current']); ?>)- ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Топ статей</h1>

    Сортировать:

    <?php $active = ($order == 'visits') ? 'success' : 'light'; ?>
    <a href="/blog/top?sort=visits" class="badge badge-<?php echo e($active); ?>">Просмотры</a>

    <?php $active = ($order == 'rating') ? 'success' : 'light'; ?>
    <a href="/blog/top?sort=rated" class="badge badge-<?php echo e($active); ?>">Оценки</a>

    <?php $active = ($order == 'comments') ? 'success' : 'light'; ?>
    <a href="/blog/top?sort=comm" class="badge badge-<?php echo e($active); ?>">Комментарии</a>
    <hr>

    <?php if($blogs->isNotEmpty()): ?>
        <?php $__currentLoopData = $blogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <div class="b">
                <i class="fa fa-pencil"></i>
                <b><a href="/article/<?php echo e($data['id']); ?>"><?php echo e($data['title']); ?></a></b> (<?php echo format_num($data['rating']); ?>)
            </div>

            <div>
                Автор: <?php echo profile($data->user); ?><br>
                Категория: <a href="/blog/<?php echo e($data['category_id']); ?>"><?php echo e($data['name']); ?></a><br>
                Просмотров: <?php echo e($data['visits']); ?><br>
                <a href="/article/<?php echo e($data['id']); ?>/comments">Комментарии</a> (<?php echo e($data['comments']); ?>)
                <a href="/article/<?php echo e($data['id']); ?>/end">&raquo;</a>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php echo e(App::pagination($page)); ?>

    <?php else: ?>
        <?php echo e(show_error('Опубликованных статей еще нет!')); ?>

    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>