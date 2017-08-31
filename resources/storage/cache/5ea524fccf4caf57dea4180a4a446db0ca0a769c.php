<?php $__env->startSection('title'); ?>
    <?php echo e($topic['title']); ?> - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $data['text'] = App::bbCode($data['text']); ?>
        <?php $data['text'] = str_replace('/uploads/smiles', Setting::get('home').'/uploads/smiles', $data['text']); ?>

        <item>
            <title><?php echo e($data['text']); ?></title>
            <link><?php echo e(Setting::get('home')); ?>/topic/<?php echo e($topic['id']); ?>/<?php echo e($data['id']); ?></link>
            <description><?php echo e($topic['title']); ?> </description>
            <author><?php echo e($data->getUser()->login); ?></author>
            <pubDate><?php echo e(date("r", $data['created_at'])); ?></pubDate>
            <category>Сообщения</category>
            <guid><?php echo e(Setting::get('home')); ?>/topic/<?php echo e($topic['id']); ?>/<?php echo e($data['id']); ?></guid>
        </item>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout_rss', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>