<?php $__env->startSection('title'); ?>
    <?php echo e($blog['title']); ?> - Комментарии - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <h1><a href="/article/<?=$blog['id']?>"><?=$blog['title']?></a></h1>

    <a href="/article/<?=$blog['id']?>/rss">RSS-лента</a><hr>

    <?php if($comments): ?>
        <?php $__currentLoopData = $comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="post">
                <div class="b">
                    <div class="img"><?=user_avatars($data['user'])?></div>

                    <div class="float-right">
                        <?php if(App::getUserId() != $data['user_id']): ?>
                            <a href="#" onclick="return postReply(this)" title="Ответить"><i class="fa fa-reply text-muted"></i></a>

                            <a href="#" onclick="return postQuote(this)" title="Цитировать"><i class="fa fa-quote-right text-muted"></i></a>

                            <a href="#" onclick="return sendComplaint(this)" data-type="<?php echo e(Blog::class); ?>" data-id="<?php echo e($data['id']); ?>" data-token="<?php echo e($_SESSION['token']); ?>" data-page="<?php echo e($page['current']); ?>" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a>

                        <?php endif; ?>

                        <?php if(App::getUserId() == $data->getUser()->id && $data['created_at'] + 600 > SITETIME): ?>
                            <a href="/article/<?=$blog['id']?>/<?=$data['id']?>/edit?page=<?php echo e($page['current']); ?>"><i class="fa fa-pencil text-muted"></i></a>
                        <?php endif; ?>

                        <?php if(is_admin()): ?>
                            <a href="#" onclick="return deleteComment(this)" data-rid="<?php echo e($data['relate_id']); ?>" data-id="<?php echo e($data['id']); ?>" data-type="<?php echo e(Blog::class); ?>" data-token="<?php echo e($_SESSION['token']); ?>" data-toggle="tooltip" title="Удалить"><i class="fa fa-remove text-muted"></i></a>
                        <?php endif; ?>
                    </div>

                    <b><?=profile($data['user'])?></b> <small>(<?=date_fixed($data['created_at'])?>)</small><br>
                    <?=user_title($data['user'])?> <?=user_online($data['user'])?>
                </div>
                <div class="message">
                    <?php echo App::bbCode($data['text']); ?><br>
                </div>

                <?php if(is_admin()): ?>
                    <span class="data">(<?php echo e($data['brow']); ?>, <?php echo e($data['ip']); ?>)</span>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php echo e(App::pagination($page)); ?>

    <?php else: ?>
        <?php echo e(show_error('Нет сообщений')); ?>

    <?php endif; ?>

    <?php if(is_user()): ?>
        <div class="form">
            <form action="/article/<?php echo e($blog['id']); ?>/comments" method="post">
                <input type="hidden" name="token" value="<?php echo e($_SESSION['token']); ?>">
                <textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br>
                <button class="btn btn-success">Написать</button>
            </form>
        </div><br>

        <a href="/rules">Правила</a> /
        <a href="/smiles">Смайлы</a> /
        <a href="/tags">Теги</a><br><br>

    <?php else: ?>
        <?php echo e(show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо')); ?>

    <?php endif; ?>

<?php
App::view('includes/back', ['link' => '/blog', 'title' => 'К блогам', 'icon' => 'fa-arrow-circle-up']);
App::view('includes/back', ['link' => '/article/'.$blog['id'], 'title' => 'Вернуться']);
?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>