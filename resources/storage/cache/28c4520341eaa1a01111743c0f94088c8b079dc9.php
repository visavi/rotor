<?php $__env->startSection('title'); ?>
    Форум - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Форум <?php echo e(Setting::get('title')); ?></h1>
    <?php if(is_user()): ?>
        Мои: <a href="/forum/active/themes">темы</a>, <a href="/forum/active/posts">сообщения</a>, <a href="/forum/bookmark">закладки</a> /
    <?php endif; ?>

    Новые: <a href="/forum/new/themes">темы</a>, <a href="/forum/new/posts">сообщения</a>
    <hr/>

    <?php $__currentLoopData = $forums; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $forum): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="b">
            <i class="fa fa-file-text-o fa-lg text-muted"></i>
            <b><a href="/forum/<?php echo e($forum['id']); ?>"><?php echo e($forum['title']); ?></a></b>
            (<?php echo e($forum->topics); ?>/<?php echo e($forum->posts); ?>)

            <?php if(!empty($forum['desc'])): ?>
                <br/>
                <small><?php echo e($forum['desc']); ?></small>
            <?php endif; ?>
        </div>

        <div>
            <?php if($forum->children->isNotEmpty()): ?>
                <?php $__currentLoopData = $forum->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <i class="fa fa-files-o text-muted"></i> <b><a href="/forum/<?php echo e($child['id']); ?>"><?php echo e($child['title']); ?></a></b>
                    (<?php echo e($child->topics); ?>/<?php echo e($child->posts); ?>)<br/>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>

            <?php if($forum->getLastTopic()->lastPost): ?>
                Тема: <a href="/topic/<?php echo e($forum->getLastTopic()->id); ?>/end"><?php echo e($forum->getLastTopic()->title); ?></a>
                <br/>
                Сообщение: <?php echo e($forum->getLastTopic()->getLastPost()->getUser()->login); ?> (<?php echo e(date_fixed($forum->getLastTopic()->getLastPost()->created_at)); ?>)
            <?php else: ?>
                Темы еще не созданы!
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <br/><a href="/rules">Правила</a> / <a href="/forum/top/themes">Топ тем</a> / <a href="/forum/top/posts">Топ постов</a> / <a href="/forum/search">Поиск</a> / <a href="/forum/rss">RSS</a><br/>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>