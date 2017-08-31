<?php $__env->startSection('title'); ?>
    <?php echo e($vote->title); ?> - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1><?php echo e($vote->title); ?></h1>

    <?php if((is_user() && empty($vote['poll'])) && empty($show)): ?>
        <form action="/votes/<?php echo e($vote->id); ?>" method="post">
            <input type="hidden" name="token" value="<?php echo e($_SESSION['token']); ?>">

            <?php $__currentLoopData = $vote['answers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $answer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label><input name="poll" type="radio" value="<?php echo e($answer['id']); ?>"> <?php echo e($answer['answer']); ?></label><br>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <br>
            <button class="btn btn-sm btn-primary">Голосовать</button>
        </form><br>

        Проголосовало: <b><?php echo e($vote['count']); ?></b><br><br>
        <i class="fa fa-history"></i> <a href="/votes/<?php echo e($vote->id); ?>?show=true">Результаты</a><br>

    <?php else: ?>
        <?php $__currentLoopData = $vote['voted']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $proc = round(($data * 100) / $vote['sum'], 1); ?>
            <?php $maxproc = round(($data * 100) / $vote['max']); ?>

            <b><?php echo e($key); ?></b> (Голосов: <?php echo e($data); ?>)<br>
            <?php echo App::progressBar($maxproc, $proc.'%'); ?>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        Проголосовало: <b><?php echo e($vote['count']); ?></b><br><br>

        <?php if(! empty($show)): ?>
            <i class="fa fa-bar-chart"></i> <a href="/votes/<?php echo e($vote->id); ?>">К вариантам</a><br>
        <?php endif; ?>
        <i class="fa fa-users"></i> <a href="/votes/<?php echo e($vote->id); ?>/voters">Проголосовавшие</a><br>
    <?php endif; ?>

    <i class="fa fa-arrow-circle-up"></i> <a href="/votes">К голосованиям</a><br>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>