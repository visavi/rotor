<?php $__env->startSection('title'); ?>
    <?php echo e($photo->title); ?> - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1><?php echo e($photo['title']); ?></h1>

    <?php if($photo): ?>

    <?php
    $links = [
        ['url' => '/admin/gallery?act=edit&amp;gid='.$photo['id'], 'label' => 'Редактировать', 'show' => is_admin()],
        ['url' => '/admin/gallery?act=del&amp;del='.$photo['id'].'&amp;uid='.$_SESSION['token'], 'label' => 'Удалить', 'params' => ['onclick' => "return confirm('Вы подтверждаете удаление изображения?')"], 'show' => is_admin()],
        ['url' => '/gallery/'.$photo['id'].'/edit', 'label' => 'Редактировать', 'show' => (($photo['user'] == App::getUsername()) && !is_admin())],
        ['url' => '/gallery/'.$photo['id'].'/delete?token='.$_SESSION['token'], 'label' => 'Удалить', 'params' => ['onclick' => "return confirm('Вы подтверждаете удаление изображения?')"], 'show' => (($photo['user'] == App::getUsername()) && !is_admin())],
    ];
    ?>

    <ol class="breadcrumb">
        <?php foreach ($links as $link): ?>
            <?php if (isset($link['show']) && $link['show'] == false) continue; ?>
            <li class="breadcrumb-item"><a href="<?= $link['url'] ?>"><?= $link['label'] ?></a></li>
        <?php endforeach; ?>
    </ol>

    <div>
        <a href="/uploads/pictures/<?php echo e($photo['link']); ?>" class="gallery"><img  class="img-fluid" src="/uploads/pictures/<?php echo e($photo['link']); ?>" alt="image"></a><br>

        <?php if($photo['text']): ?>
            <?php echo App::bbCode($photo['text']); ?><br>
        <?php endif; ?>

        <div class="js-rating">Рейтинг:
            <?php if (! (App::getUserId() == $photo['user_id'])): ?>
                <a class="post-rating-down<?= $photo->vote == -1 ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="<?php echo e($photo['id']); ?>" data-type="<?php echo e(Photo::class); ?>" data-vote="-1" data-token="<?php echo e($_SESSION['token']); ?>"><i class="fa fa-thumbs-down"></i></a>
            <?php endif; ?>
            <span><?php echo format_num($photo['rating']); ?></span>
            <?php if (! (App::getUserId() == $photo['user_id'])): ?>
                <a class="post-rating-up<?= $photo->vote == 1 ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="<?php echo e($photo['id']); ?>" data-type="<?php echo e(Photo::class); ?>" data-vote="1" data-token="<?php echo e($_SESSION['token']); ?>"><i class="fa fa-thumbs-up"></i></a>
            <?php endif; ?>
        </div>

        Размер: <?php echo e(read_file(HOME.'/uploads/pictures/'.$photo['link'])); ?><br>
        Добавлено: <?php echo profile($photo['user']); ?> (<?php echo e(date_fixed($photo['time'])); ?>)<br>
        <a href="/gallery/<?php echo e($photo['id']); ?>/comments">Комментарии</a> (<?php echo e($photo['comments']); ?>)
        <a href="/gallery/<?php echo e($photo['id']); ?>/end">&raquo;</a>
    </div>
    <br>

    <?php $nav = photo_navigation($photo['id']); ?>

    <?php if($nav['next'] || $nav['prev']): ?>
        <div class="form" style="text-align:center">
            <?php if($nav['next']): ?>
                <a href="/gallery/<?php echo e($nav['next']); ?>">&laquo; Назад</a> &nbsp;
            <?php endif; ?>

            <?php if($nav['prev']): ?>
                &nbsp; <a href="/gallery/<?php echo e($nav['prev']); ?>">Вперед &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <i class="fa fa-arrow-circle-up"></i> <a href="/gallery/album/<?php echo e($photo->getUser()->login); ?>">В альбом</a><br>

    <?php else: ?>
        <?php echo e(show_error('Ошибка! Данного изображения нет в базе')); ?>

    <?php endif; ?>

    <i class="fa fa-arrow-circle-left"></i> <a href="/gallery">В галерею</a><br>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>