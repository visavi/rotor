<?php $__env->startSection('title'); ?>
    <?php echo e($category['name']); ?> (Стр. <?php echo e($page['current']); ?>) - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <?php if(is_user()): ?>
        <div class="float-right">
            <a class="btn btn-success" href="/blog/create?cid=<?php echo e($category['id']); ?>">Добавить статью</a>
        </div>
    <?php endif; ?>

    <h1><?php echo e($category['name']); ?> <small>(Статей: <?php echo e($category['count']); ?>)</small></h1>
    <a href="/blog">Блоги</a>

    <?php if(is_admin()): ?>
        / <a href="/admin/blog?act=blog&amp;cid=<?php echo e($category['id']); ?>&amp;page=<?php echo e($page['current']); ?>">Управление</a>
    <?php endif; ?>
    <hr>

    <?php if($blogs): ?>
        <?php $__currentLoopData = $blogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="b">
                <i class="fa fa-pencil"></i>
                <b><a href="/article/<?php echo e($data['id']); ?>"><?php echo e($data['title']); ?></a></b> (<?php echo format_num($data['rating']); ?>)
            </div>
            <div>
                Автор: <?php echo profile($data['user']); ?> (<?php echo e(date_fixed($data['created_at'])); ?>)<br>
                Просмотров: <?php echo e($data['visits']); ?><br>
                <a href="/article/<?php echo e($data['id']); ?>/comments">Комментарии</a> (<?php echo e($data['comments']); ?>)
                <a href="/article/<?php echo e($data['id']); ?>/end">&raquo;</a>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php echo e(App::pagination($page)); ?>

    <?php else: ?>
        <?php echo e(show_error('Статей еще нет, будь первым!')); ?>

    <?php endif; ?>

    <a href="/blog/top">Топ статей</a> /
    <a href="/blog/tags">Облако тегов</a> /
    <a href="/blog/search">Поиск</a> /
    <a href="/blog/blogs">Все статьи</a> /
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>