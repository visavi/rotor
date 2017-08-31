<?php $__env->startSection('title'); ?>
    Блоги - Список разделов - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Блоги</h1>

    <?php if(is_user()): ?>
        Мои: <a href="/blog/active/articles">статьи</a>, <a href="/blog/active/comments">комментарии</a> /
    <?php endif; ?>

    Новые: <a href="/blog/new/articles">статьи</a>, <a href="/blog/new/comments">комментарии</a><hr>

    <?php $__currentLoopData = $blogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <i class="fa fa-folder-open"></i> <b><a href="/blog/<?php echo e($data['id']); ?>"><?php echo e($data['name']); ?></a></b>

        <?php if($data->new): ?>
            (<?php echo e($data->count); ?>/+<?php echo e($data->new->count); ?>)<br>
        <?php else: ?>
            (<?php echo e($data->count); ?>)<br>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <br>
    <a href="/blog/top">Топ статей</a> /
    <a href="/blog/tags">Облако тегов</a> /
    <a href="/blog/search">Поиск</a> /
    <a href="/blog/blogs">Все статьи</a> /
    <a href="/blog/rss">RSS</a><br>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>