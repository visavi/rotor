<?php $__env->startSection('title'); ?>
    Поиск запроса <?php echo e($find); ?> - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Поиск запроса <?=$find?></h1>

    <p>Найдено совпадений в сообщениях: <?=$page['total']?></p>

    <?php foreach ($posts as $post): ?>

        <div class="b">
            <i class="fa fa-file-text-o"></i> <b><a href="/topic/<?=$post['topic_id']?>/<?=$post['id']?>"><?= $post->getTopic()->title ?></a></b>
        </div>

        <div><?=App::bbCode($post['text'])?><br>
            Написал: <?=profile($post->user)?> <?=user_online($post->user)?> <small>(<?=date_fixed($post['created_at'])?>)</small><br>
        </div>

    <?php endforeach; ?>

    <?php App::pagination($page) ?>

    <i class="fa fa-arrow-circle-left"></i> <a href="/forum/search">Вернуться</a>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>