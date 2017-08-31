<?php $__env->startSection('title'); ?>
    Редактирование комментария - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <h1>Редактирование комментария</h1>

    <i class="fa fa-pencil"></i> <b><?php echo e($comment->getUser()->login); ?></b> <small>(<?php echo e(date_fixed($comment['created_at'])); ?>)</small><br><br>

    <div class="form">
        <form action="/article/<?php echo e($comment['relate_id']); ?>/<?php echo e($comment->id); ?>/edit?page=<?php echo e($page); ?>" method="post">
            <input type="hidden" name="token" value="<?php echo e($_SESSION['token']); ?>">
            <textarea id="markItUp" cols="25" rows="5" name="msg"><?php echo e($comment['text']); ?></textarea><br>
            <input type="submit" value="Редактировать">
        </form>
    </div><br>

    <?php
    App::view('includes/back', ['link' => '/article/'.$comment['relate_id'].'/comments?page='.$page, 'title' => 'Вернуться']); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>