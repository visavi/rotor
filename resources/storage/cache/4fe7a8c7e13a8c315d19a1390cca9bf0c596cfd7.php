<?php $__env->startSection('title'); ?>
    <?php echo e($blog['title']); ?> - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('keywords'); ?>
    <?php echo e($blog['tags']); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('description'); ?>
    <?php echo e(strip_str($blog['text'])); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1><?php echo e($blog['title']); ?> <small>(Оценка: <?php echo format_num($blog['rating']); ?>)</small></h1>

    <a href="/blog">Блоги</a> / <a href="/blog/<?php echo e($blog['category_id']); ?>"><?php echo e($blog['name']); ?></a> / <a href="/article/<?php echo e($blog['id']); ?>/print">Печать</a> / <a href="/article/<?php echo e($blog['id']); ?>/rss">RSS-лента</a>

    <?php if($blog->getUser()->id == App::getUserId()): ?>
         / <a href="/article/<?php echo e($blog['id']); ?>/edit">Изменить</a>
    <?php endif; ?>

    <br>

    <?php if(is_admin()): ?>
        <br> <a href="/admin/blog?act=editblog&amp;cid=<?php echo e($blog['category_id']); ?>&amp;id=<?php echo e($blog['id']); ?>">Редактировать</a> /
        <a href="/admin/blog?act=moveblog&amp;cid=<?php echo e($blog['category_id']); ?>&amp;id=<?php echo e($blog['id']); ?>">Переместить</a> /
        <a href="/admin/blog?act=delblog&amp;cid=<?php echo e($blog['category_id']); ?>&amp;del=<?php echo e($blog['id']); ?>&amp;uid=<?php echo e($_SESSION['token']); ?>" onclick="return confirm('Вы действительно хотите удалить данную статью?')">Удалить</a>
    <?php endif; ?>
    <hr>

    <?php echo $blog['text']; ?>


    <?php echo e(App::pagination($page)); ?>


    Автор статьи: <?php echo profile($blog['user']); ?> (<?php echo e(date_fixed($blog['created_at'])); ?>)<br>

    <i class="fa fa-tag"></i> <?php echo $tags; ?>


    <hr>

    <div class="js-rating">Рейтинг:
        <?php if (! (App::getUserId() == $blog['user_id'])): ?>
            <a class="post-rating-down<?= $blog->vote == -1 ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="<?php echo e($blog['id']); ?>" data-type="<?php echo e(Blog::class); ?>" data-vote="-1" data-token="<?php echo e($_SESSION['token']); ?>"><i class="fa fa-thumbs-down"></i></a>
        <?php endif; ?>
        <span><?php echo format_num($blog['rating']); ?></span>
        <?php if (! (App::getUserId() == $blog['user_id'])): ?>
            <a class="post-rating-up<?= $blog->vote == 1 ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="<?php echo e($blog['id']); ?>" data-type="<?php echo e(Blog::class); ?>" data-vote="1" data-token="<?php echo e($_SESSION['token']); ?>"><i class="fa fa-thumbs-up"></i></a>
        <?php endif; ?>
    </div>

    <i class="fa fa-eye"></i> Просмотров: <?php echo e($blog['visits']); ?><br>
    <i class="fa fa-comment"></i> <a href="/article/<?php echo e($blog['id']); ?>/comments">Комментарии</a> (<?php echo e($blog['comments']); ?>)
    <a href="/article/<?php echo e($blog['id']); ?>/end">&raquo;</a><br><br>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>