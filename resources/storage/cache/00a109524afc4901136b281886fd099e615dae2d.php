<?php $__env->startSection('title'); ?>
    Голосования - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Голосования</h1>

    <?php if($votes->isNotEmpty()): ?>
        <?php $__currentLoopData = $votes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="b">
                <i class="fa fa-bar-chart"></i>
                <b><a href="/votes/<?php echo e($vote['id']); ?>"><?php echo e($vote['title']); ?></a></b>
            </div>
            <div>
                <?php if($vote->topic): ?>
                    Тема: <a href="/topic/<?php echo e($vote->getTopic()->id); ?>"><?php echo e($vote->getTopic()->title); ?></a><br>
                <?php endif; ?>

                Создано: <?php echo e(date_fixed($vote['created_at'])); ?><br>
                Всего голосов: <?php echo e($vote['count']); ?><br>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <br>
    <?php else: ?>
        <?php echo e(App::showError('Открытых голосований еще нет!')); ?>

    <?php endif; ?>

    <i class="fa fa-briefcase"></i> <a href="/votes/history">Архив голосований</a><br>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>