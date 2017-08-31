<?php $__env->startSection('title'); ?>
    Кто в онлайне - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Кто в онлайне</h1>

    Всего на сайте: <b><?php echo e($page['total']); ?></b><br>
    Зарегистрированных:  <b><?php echo e($registered); ?></b><br><br>


    <?php if($online->isNotEmpty()): ?>

        <?php $__currentLoopData = $online; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <div class="b">
                <?php if($data->user): ?>
                    <?php echo user_gender($data->user); ?> <b><?php echo profile($data->user); ?></b> (Время: <?php echo e(date_fixed($data['updated_at'], 'H:i:s')); ?>)
                <?php else: ?>
                    <i class="fa fa-user-circle-o"></i> <b><?php echo e(Setting::get('guestsuser')); ?></b>  (Время: <?php echo e(date_fixed($data['updated_at'], 'H:i:s')); ?>)
                <?php endif; ?>
            </div>

            <?php if(is_admin()): ?>
                <div>
                    <span class="data">(<?php echo e($data['brow']); ?>, <?php echo e($data['ip']); ?>)</span>
                </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php echo e(App::pagination($page)); ?>

    <?php else: ?>
        <?php echo e(App::showError('На сайте никого нет!')); ?>

    <?php endif; ?>

    <i class="fa fa-users"></i> <a href="/online">Скрыть гостей</a><br>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>