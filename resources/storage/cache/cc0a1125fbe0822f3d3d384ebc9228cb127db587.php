<?php $__env->startSection('title'); ?>
    Управление жалобами - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Управление жалобами</h1>

    <?php $active = ($type == 'post') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=post" class="badge badge-<?php echo e($active); ?>">Форум <?php echo e($total['post']); ?></a>
    <?php $active = ($type == 'guest') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=guest" class="badge badge-<?php echo e($active); ?>">Гостевая <?php echo e($total['guest']); ?></a>
    <?php $active = ($type == 'photo') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=photo" class="badge badge-<?php echo e($active); ?>">Галерея <?php echo e($total['photo']); ?></a>
    <?php $active = ($type == 'blog') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=blog" class="badge badge-<?php echo e($active); ?>">Блоги <?php echo e($total['blog']); ?></a>
    <?php $active = ($type == 'inbox') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=inbox" class="badge badge-<?php echo e($active); ?>">Приват <?php echo e($total['inbox']); ?></a>
    <?php $active = ($type == 'wall') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=wall" class="badge badge-<?php echo e($active); ?>">Стена <?php echo e($total['wall']); ?></a>
    <?php $active = ($type == 'load') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=load" class="badge badge-<?php echo e($active); ?>">Загрузки<?php echo e($total['load']); ?></a>
        <br><br>

    <?php if($records->isNotEmpty()): ?>
        <?php $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="post">
                <?php if($data->relate): ?>
                    <div class="b">
                        <i class="fa fa-file-o"></i>
                        <b><?php echo profile($data->relate->user); ?></b>
                        <small>(<?php echo e(date_fixed($data->relate->created_at, "d.m.y / H:i:s")); ?>)</small>

                        <div class="float-right">
                            <?php if(is_admin()): ?>
                                <a href="#" onclick="return deleteSpam(this)" data-id="<?php echo e($data['id']); ?>" data-token="<?php echo e($_SESSION['token']); ?>" data-toggle="tooltip" title="Удалить"><i class="fa fa-remove"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div><?php echo App::bbCode($data->relate->text); ?></div>
                <?php else: ?>
                    <div class="b">
                        <i class="fa fa-file-o"></i> <b>Сообщение не найдено</b>

                        <div class="float-right">
                            <?php if(is_admin()): ?>
                                <a href="#" onclick="return deleteSpam(this)" data-id="<?php echo e($data['id']); ?>" data-token="<?php echo e($_SESSION['token']); ?>" data-toggle="tooltip" title="Удалить"><i class="fa fa-remove"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div>
                    <?php if($data['path']): ?>
                        <a href="<?php echo e($data['path']); ?>">Перейти к сообщению</a><br>
                    <?php endif; ?>
                    Жалоба: <?php echo profile($data->user); ?> (<?php echo e(date_fixed($data['created_at'])); ?>)
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php echo e(App::pagination($page)); ?>

    <?php else: ?>
        <?php echo e(App::showError('Жалоб еще нет!')); ?>

    <?php endif; ?>

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>