<?php $__env->startSection('title'); ?>
    <?php echo e($news['title']); ?> - Комментарии (Стр. <?php echo e($page['current']); ?>) - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1><a href="/news/<?php echo e($news['id']); ?>"><?php echo e($news['title']); ?></a></h1>

    <?php if($comments->isNotEmpty()): ?>
        <?php $__currentLoopData = $comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="post">
                <div class="b" id="comment_<?php echo e($data['id']); ?>">
                    <div class="img"><?php echo user_avatars($data['user']); ?></div>

                    <div class="float-right">
                        <?php if(App::getUserId() != $data['user_id']): ?>
                            <a href="#" onclick="return postReply(this)" title="Ответить"><i class="fa fa-reply text-muted"></i></a>

                            <a href="#" onclick="return postQuote(this)" title="Цитировать"><i class="fa fa-quote-right text-muted"></i></a>

                            <a href="#" onclick="return sendComplaint(this)" data-type="<?php echo e(News::class); ?>" data-id="<?php echo e($data['id']); ?>" data-token="<?php echo e($_SESSION['token']); ?>" data-page="<?php echo e($page['current']); ?>" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a>

                        <?php endif; ?>

                        <?php if($data->user_id == App::getUserId() && $data['created_at'] + 600 > SITETIME): ?>
                            <a title="Редактировать" href="/news/<?php echo e($news->id); ?>/<?php echo e($data['id']); ?>/edit?page=<?php echo e($page['current']); ?>"><i class="fa fa-pencil text-muted"></i></a>
                        <?php endif; ?>

                        <?php if(is_admin()): ?>
                            <a href="#" onclick="return deleteComment(this)" data-rid="<?php echo e($data['relate_id']); ?>" data-id="<?php echo e($data['id']); ?>" data-type="<?php echo e(News::class); ?>" data-token="<?php echo e($_SESSION['token']); ?>" data-toggle="tooltip" title="Удалить"><i class="fa fa-remove text-muted"></i></a>
                        <?php endif; ?>
                    </div>

                    <b><?php echo profile($data['user']); ?></b>
                    <small> (<?php echo e(date_fixed($data['created_at'])); ?>)</small><br>
                    <?php echo user_title($data['user']); ?> <?php echo user_online($data['user']); ?>

                </div>

                <div class="message">
                    <?php echo App::bbCode($data['text']); ?><br>

                    <?php if(is_admin()): ?>
                        <span class="data">(<?php echo e($data['brow']); ?>, <?php echo e($data['ip']); ?>)</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php echo e(App::pagination($page)); ?>

    <?php endif; ?>

    <?php if(! $news['closed']): ?>

        <?php if($comments->isEmpty()): ?>
            <?php echo e(show_error('Комментариев еще нет!')); ?>

        <?php endif; ?>

        <?php if(is_user()): ?>
            <div class="form">
                <form action="/news/<?php echo e($news->id); ?>/create" method="post">
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
            <?php echo e(show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо')); ?>

        <?php endif; ?>
    <?php else: ?>
        <?php echo e(show_error('Комментирование данной новости закрыто!')); ?>

    <?php endif; ?>

    <i class="fa fa-arrow-circle-left"></i> <a href="/news">К новостям</a><br>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>