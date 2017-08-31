<?php $__env->startSection('title'); ?>
    Редактирование сообщения - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <h1>Редактирование сообщения</h1>

    <i class="fa fa-pencil text-muted"></i> <b><?=profile($post->user)?></b> (<?=date_fixed($post['time'])?>)<br><br>

    <div class="form">
        <form action="/book/edit/<?= $id ?>" method="post">
            <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">

            <div class="form-group<?php echo e(App::hasError('msg')); ?>">
                <label for="markItUp">Сообщение:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="msg" placeholder="Текст сообщения" required><?php echo e(App::getInput('msg', $post['text'])); ?></textarea>
                <?php echo App::textError('msg'); ?>

            </div>

            <button class="btn btn-primary">Редактировать</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/book">Вернуться</a><br>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>