<?php $__env->startSection('title'); ?>
    Галерея сайта (Стр. <?php echo e($page['current']); ?>) - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Галерея сайта</h1>

<?php

$links = [
    ['url' => '/gallery/album/'.App::user('login'), 'label' => 'Мои альбом', 'show' => is_user()],
    ['url' => '/gallery/comments/'.App::user('login'), 'label' => 'Мои комментарии', 'show' => is_user()],
    ['url' => '/gallery/albums', 'label' => 'Все альбомы'],
    ['url' => '/gallery/comments', 'label' => 'Все комментарии'],
    ['url' => '/admin/gallery?page='.$page['current'], 'label' => 'Управление', 'show' => is_admin()],
];
?>
    <ol class="breadcrumb">
        <?php foreach ($links as $link): ?>
            <?php if (isset($link['show']) && $link['show'] == false) continue; ?>
            <li class="breadcrumb-item"><a href="<?= $link['url'] ?>"><?= $link['label'] ?></a></li>
        <?php endforeach; ?>
    </ol>
    <?php if($photos->isNotEmpty()): ?>
        <?php $__currentLoopData = $photos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <div class="b"><i class="fa fa-picture-o"></i>
                <b><a href="/gallery/<?php echo e($data['id']); ?>"><?php echo e($data['title']); ?></a></b>
                (<?php echo e(read_file(HOME.'/uploads/pictures/'.$data['link'])); ?>) (Рейтинг: <?php echo format_num($data['rating']); ?>)
            </div>

            <div>
                <a href="/gallery/<?php echo e($data['id']); ?>"><?php echo resize_image('uploads/pictures/', $data['link'], Setting::get('previewsize'), ['alt' => $data['title']]); ?></a><br>

                <?php if($data['text']): ?>
                    <?php echo App::bbCode($data['text']); ?><br>
                <?php endif; ?>

                Добавлено: <?php echo profile($data->user); ?> (<?php echo e(date_fixed($data['created_at'])); ?>)<br>
                <a href="/gallery/<?php echo e($data['id']); ?>/comments">Комментарии</a> (<?php echo e($data['comments']); ?>)
                <a href="/gallery/<?php echo e($data['id']); ?>/end">&raquo;</a>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php echo e(App::pagination($page)); ?>


        Всего фотографий: <b><?php echo e($page['total']); ?></b><br><br>

    <?php else: ?>
        <?php echo e(show_error('Фотографий нет, будь первым!')); ?>

    <?php endif; ?>

    <?php
    $links = [
        ['url' => '/gallery/top', 'label' => 'Топ фото'],
        ['url' => '/gallery/create', 'label' => 'Добавить фото'],
    ];
    ?>
    <ol class="breadcrumb">
        <?php foreach ($links as $link): ?>
            <?php if (isset($link['show']) && $link['show'] == false) continue; ?>
            <li class="breadcrumb-item"><a href="<?= $link['url'] ?>"><?= $link['label'] ?></a></li>
        <?php endforeach; ?>
    </ol>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>