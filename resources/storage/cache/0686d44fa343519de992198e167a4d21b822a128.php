<?php $__env->startSection('title'); ?>
    Список всех фотографий <?php echo e($user->login); ?> (Стр. <?php echo e($page['current']); ?>) - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Список всех фотографий <?php echo e($user->login); ?></h1>

    <?php if($photos->isNotEmpty()): ?>

        <?php $__currentLoopData = $photos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="b">
                <i class="fa fa-picture-o"></i>
                <b><a href="/gallery/<?php echo e($data['id']); ?>"><?php echo e($data['title']); ?></a></b> (<?php echo e(read_file(HOME.'/uploads/pictures/'.$data['link'])); ?>)<br>

                <?php if($moder): ?>
                    <a href="/gallery/<?php echo e($data['id']); ?>/edit?page=<?php echo e($page['current']); ?>">Редактировать</a> /
                    <a href="/gallery/<?php echo e($data['id']); ?>/delete?page=<?php echo e($page['current']); ?>&amp;token=<?php echo e($_SESSION['token']); ?>" onclick="return confirm('Вы подтверждаете удаление изображения?')">Удалить</a>
                <?php endif; ?>
            </div>
            <div>
                <a href="/gallery/<?php echo e($data['id']); ?>"><?php echo resize_image('uploads/pictures/', $data['link'], Setting::get('previewsize'), ['alt' => $data['title']]); ?></a><br>

                <?php if($data['text']): ?>
                   <?php echo e(App::bbCode($data['text'])); ?><br>
                <?php endif; ?>

                Добавлено: <?php echo profile($data->user); ?> (<?php echo e(date_fixed($data['created_at'])); ?>)<br>
                <a href="/gallery/<?php echo e($data['id']); ?>/comments">Комментарии</a> (<?php echo e($data['comments']); ?>)
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php echo e(App::pagination($page)); ?>


        Всего фотографий: <b><?php echo e($page['total']); ?></b><br><br>
    <?php else: ?>
        <?php echo e(show_error('Фотографий в альбоме еще нет!')); ?>

    <?php endif; ?>

    <i class="fa fa-arrow-circle-up"></i> <a href="/gallery/albums">Альбомы</a><br>
    <i class="fa fa-arrow-circle-left"></i> <a href="/gallery">В галерею</a><br>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>