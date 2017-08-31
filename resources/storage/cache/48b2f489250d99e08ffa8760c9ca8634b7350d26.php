<?php $__env->startSection('title'); ?>
    Список новых сообщений - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <h1>Список новых сообщений</h1>

    <a href="/forum">Форум</a>

    <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="b">
            <i class="fa fa-file-text-o"></i> <b><a href="/topic/<?php echo e($data['topic_id']); ?>/<?php echo e($data['id']); ?>"><?php echo e($data->getTopic()->title); ?></a></b>
            (<?php echo e($data->getTopic()->posts); ?>)
        </div>
        <div>
            <?php echo App::bbCode($data['text']); ?><br>

            Написал: <?php echo e($data->getUser()->login); ?> <?php echo user_online($data->user); ?> <small>(<?php echo e(date_fixed($data['created_at'])); ?>)</small><br>

            <?php if(is_admin()): ?>
                <span class="data">(<?php echo e($data['brow']); ?>, <?php echo e($data['ip']); ?>)</span>
            <?php endif; ?>

        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php echo e(App::pagination($page)); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>