<?php $__env->startSection('title'); ?>
    Поиск запроса <?php echo e($find); ?> - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Поиск запроса <?=$find?></h1>

    <p>Найдено совпадений в темах: <?=$page['total']?></p>

    <?php foreach ($topics as $topic): ?>
        <div class="b">

            <i class="fa <?php echo e($topic->getIcon()); ?> text-muted"></i>
            <b><a href="/topic/<?=$topic['id']?>"><?=$topic['title']?></a></b> (<?=$topic['posts']?>)
        </div>
        <div>
            <?= Forum::pagination($topic)?>
            Сообщение: <?=$topic->getLastPost()->getUser()->login?> (<?=date_fixed($topic->getLastPost()->created_at)?>)
        </div>
    <?php endforeach; ?>

    <?php App::pagination($page) ?>

    <i class="fa fa-arrow-circle-left"></i> <a href="/forum/search">Вернуться</a>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>