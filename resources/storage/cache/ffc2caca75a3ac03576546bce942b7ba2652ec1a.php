<?php $__env->startSection('title'); ?>
    Список всех комментариев <?php echo e($user->login); ?> (Стр. <?php echo e($page['current']); ?>) - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Список всех комментариев <?php echo e($user->login); ?></h1>

    <?php if($comments->isNotEmpty()): ?>
        <?php $__currentLoopData = $comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="post">
                <div class="b">

                    <i class="fa fa-comment"></i>
                    <b><a href="/article/<?php echo e($data['relate_id']); ?>/<?php echo e($data['id']); ?>"><?php echo e($data['title']); ?></a></b> (<?php echo e($data['comments']); ?>)

                    <div class="float-right">
                        <?php if(is_admin()): ?>
                            <a href="#" onclick="return deleteComment(this)" data-rid="<?php echo e($data['relate_id']); ?>" data-id="<?php echo e($data['id']); ?>" data-type="<?php echo e(Blog::class); ?>" data-token="<?php echo e($_SESSION['token']); ?>" data-toggle="tooltip" title="Удалить"><i class="fa fa-remove text-muted"></i></a>
                        <?php endif; ?>
                    </div>

                </div>
                <div>
                    <?php echo App::bbCode($data['text']); ?>

                    <br>

                    Написал: <?php echo profile($data['user']); ?> <small>(<?php echo e(date_fixed($data['time'])); ?>)</small><br>
                    <?php if(is_admin()): ?>
                        <span class="data">(<?php echo e($data['brow']); ?>, <?php echo e($data['ip']); ?>)</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php echo e(App::pagination($page)); ?>

    <?php else: ?>
        <?php echo e(show_error('Комментарии не найдены!')); ?>

    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>