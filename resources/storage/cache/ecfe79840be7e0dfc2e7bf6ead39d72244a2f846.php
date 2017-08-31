<?php $__env->startSection('title'); ?>
    Новости сайта (Стр. <?php echo e($page['current']); ?>) - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Новости сайта</h1>

    <?php if(is_admin([101, 102])): ?>
        <div class="form"><a href="/admin/news">Управление новостями</a></div>
    <?php endif; ?>

    <?php if($news->isNotEmpty()): ?>
        <?php $__currentLoopData = $news; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="b">
                <?php echo $data['closed'] == 0 ? '<i class="fa fa-plus-square-o"></i> ' : '<i class="fa fa-minus-square-o"></i>'; ?>

                <b><a href="/news/<?php echo e($data['id']); ?>"><?php echo e($data['title']); ?></a></b><small> (<?php echo e(date_fixed($data['created_at'])); ?>)</small>
            </div>

            <?php if($data['image']): ?>
                <div class="img">
                    <a href="/uploads/news/<?php echo e($data['image']); ?>"><?php echo resize_image('uploads/news/', $data['image'], 75, ['alt' => $data['title']]); ?></a>
                </div>
            <?php endif; ?>

            <?php if(stristr($data['text'], '[cut]')): ?>
                <?php 
                 $data['text'] = current(explode('[cut]', $data['text'])).' <a href="/news/'.$data['id'].'">Читать далее &raquo;</a>';
                 ?>
            <?php endif; ?>

            <div><?php echo App::bbCode($data['text']); ?></div>
            <div style="clear:both;">
                Добавлено: <?php echo profile($data->user); ?><br>
                <a href="/news/<?php echo e($data['id']); ?>/comments">Комментарии</a> (<?php echo e($data['comments']); ?>)
                <a href="/news/<?php echo e($data['id']); ?>/end">&raquo;</a>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php echo e(App::pagination($page)); ?>

    <?php else: ?>
        App::showError('Новостей еще нет!');
    <?php endif; ?>

    <i class="fa fa-rss"></i> <a href="/news/rss">RSS подписка</a><br>
    <i class="fa fa-comment"></i> <a href="/news/allcomments">Комментарии</a><br>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>