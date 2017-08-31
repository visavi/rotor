<?php $__env->startSection('title'); ?>
    <?php echo e($forum['title']); ?> (Стр. <?php echo e($page['current']); ?>) - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1><?php echo e($forum['title']); ?></h1>

    <a href="/forum">Форум</a>

    <?php if($forum->parent): ?>
        / <a href="/forum/<?php echo e($forum->parent->id); ?>"><?php echo e($forum->parent->title); ?></a>
    <?php endif; ?>

    / <?php echo e($forum['title']); ?>


    <?php if(is_admin()): ?>
        / <a href="/admin/forum?act=forum&amp;fid=<?php echo e($forum->id); ?>&amp;page=<?php echo e($page['current']); ?>">Управление</a>
    <?php endif; ?>

    <?php if(is_user() && empty($forum['closed'])): ?>
        <div class="float-right">
            <a class="btn btn-success" href="/forum/create?fid=<?php echo e($forum->id); ?>">Создать тему</a>
        </div>
    <?php endif; ?>

    <hr>

    <?php if(!$forum->children->isEmpty() && $page['current'] == 1): ?>
        <div class="act">

        <?php $__currentLoopData = $forum->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <div class="b"><i class="fa fa-file-text-o fa-lg text-muted"></i>
            <b><a href="/forum/<?php echo e($child['id']); ?>"><?php echo e($child['title']); ?></a></b> (<?php echo e($child->topics); ?>/<?php echo e($child->posts); ?>)</div>

            <?php if($child->lastTopic): ?>
                <div>
                    Тема: <a href="/topic/<?php echo e($child->lastTopic->id); ?>/end"><?php echo e($child->lastTopic->title); ?></a><br>
                    <?php if($child->lastTopic->lastPost): ?>
                        Сообщение: <?php echo e($child->lastTopic->lastPost->getUser()->login); ?> (<?php echo e(date_fixed($child->lastTopic->lastPost->created_at)); ?>)
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div>Темы еще не созданы!</div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        </div>
        <hr>
    <?php endif; ?>

    <?php if($topics): ?>
        <?php $__currentLoopData = $topics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $topic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="b" id="topic_<?php echo e($topic['id']); ?>">
                <i class="fa <?php echo e($topic->getIcon()); ?> text-muted"></i>
                <b><a href="/topic/<?php echo e($topic['id']); ?>"><?php echo e($topic['title']); ?></a></b> (<?php echo e($topic->posts); ?>)
            </div>
            <div>
                <?php if($topic->lastPost): ?>
                    <?php echo e(Forum::pagination($topic)); ?>

                    Сообщение: <?php echo e($topic->lastPost->getUser()->login); ?> (<?php echo e(date_fixed($topic->lastPost->created_at)); ?>)
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php echo e(App::pagination($page)); ?>


    <?php elseif($forums['closed']): ?>
        <?php echo e(App::showError('В данном разделе запрещено создавать темы!')); ?>

    <?php else: ?>
        <?php echo e(App::showError('Тем еще нет, будь первым!')); ?>

    <?php endif; ?>


    <a href="/rules">Правила</a> /
    <a href="/forum/top/themes">Топ тем</a> /
    <a href="/forum/top/posts">Топ постов</a> /
    <a href="/forum/search?fid=<?php echo e($forum->id); ?>">Поиск</a><br>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>