<?php $__env->startSection('title'); ?>
    Редактирование комментария - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Редактирование комментария</h1>

    <i class="fa fa-pencil"></i> <b><?php echo e($comment->getUser()->login); ?></b> <small>(<?php echo e(date_fixed($comment['created_at'])); ?>)</small><br><br>

    <div class="form">
        <form method="post">
            <input type="hidden" name="token" value="<?php echo e($_SESSION['token']); ?>">
            <textarea id="markItUp" cols="25" rows="5" name="msg" id="msg"><?php echo e($comment['text']); ?></textarea><br>
            <button class="btn btn-success">Редактировать</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/news/<?php echo e($comment['relate_id']); ?>/comments?page=<?php echo e($page); ?>">Вернуться</a><br>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>