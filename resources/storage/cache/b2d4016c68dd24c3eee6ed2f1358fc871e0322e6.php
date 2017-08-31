<?php $__env->startSection('title'); ?>
    <?php echo e($news['title']); ?> - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('description', strip_str($news['text'])); ?>

<?php $__env->startSection('content'); ?>

    <h1><?php echo e($news['title']); ?> <small> (<?php echo e(date_fixed($news['created_at'])); ?>)</small></h1>

    <?php if(is_admin()): ?>
        <div class="form">
            <a href="/admin/news?act=edit&amp;id=<?php echo e($news->id); ?>">Редактировать</a> /
            <a href="/admin/news?act=del&amp;del=<?php echo e($news->id); ?>&amp;token=<?php echo e($_SESSION['token']); ?>" onclick="return confirm('Вы действительно хотите удалить данную новость?')">Удалить</a>
        </div>
    <?php endif; ?>

    <?php if($news['image']): ?>
        <div class="img">
            <a href="/uploads/news/<?php echo e($news['image']); ?>"><?php echo resize_image('uploads/news/', $news['image'], 75, ['alt' => $news['title']]); ?></a></div>
    <?php endif; ?>

    <div><?php echo App::bbCode($news['text']); ?></div>

    <div style="clear:both;">
        Добавлено: <?php echo profile($news['user']); ?>

    </div><br>

    <?php if($comments->isNotEmpty()): ?>
        <div class="act">
            <i class="fa fa-comment"></i> <b>Последние комментарии</b>
        </div>

        <?php $__currentLoopData = $comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="b">
                <div class="img"><?php echo user_avatars($comm['user']); ?></div>

                <b><?php echo profile($comm['user']); ?></b>
                <small> (<?php echo e(date_fixed($comm['created_at'])); ?>)</small><br>
                <?php echo user_title($comm['user']); ?> <?php echo user_online($comm['user']); ?>

            </div>

            <div>
                <?php echo App::bbCode($comm['text']); ?><br>

                <?php if(is_admin()): ?>
                 <span class="data">(<?php echo e($comm['brow']); ?>, <?php echo e($comm['ip']); ?>)</span>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php if($news['comments'] > 5): ?>
            <div class="act">
                <b><a href="/news/<?php echo e($news['id']); ?>/comments">Все комментарии</a></b> (<?php echo e($news['comments']); ?>)
                <a href="/news/<?php echo e($news['id']); ?>/end">&raquo;</a>
            </div><br>
        <?php endif; ?>
    <?php endif; ?>

    <?php if(! $news['closed']): ?>
        <?php if($comments->isEmpty()): ?>
            <?php echo e(App::showError('Комментариев еще нет!')); ?>

        <?php endif; ?>

        <?php if(is_user()): ?>
            <div class="form">
                <form action="/news/<?php echo e($news->id); ?>/create?read=1" method="post">
                    <input type="hidden" name="token" value="<?php echo e($_SESSION['token']); ?>">
                    <textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br>
                    <button class="btn btn-success">Написать</button>
                </form>
            </div>

            <br>
            <a href="/rules">Правила</a> /
            <a href="/smiles">Смайлы</a> /
            <a href="/tags">Теги</a><br><br>
        <?php else: ?>
            <?php echo e(App::showError('Для добавления сообщения необходимо авторизоваться')); ?>

        <?php endif; ?>
    <?php else: ?>
        <?php echo e(App::showError('Комментирование данной новости закрыто!')); ?>

    <?php endif; ?>


    <i class="fa fa-arrow-circle-left"></i> <a href="/news">К новостям</a><br>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>