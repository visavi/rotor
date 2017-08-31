<?php $__env->startSection('title'); ?>
    Редактирование статьи - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Редактирование статьи</h1>

    <a href="/blog">Блоги</a> /
    <a href="/blog/search">Поиск</a> /
    <a href="/blog/blog?act=blogs">Все статьи</a><hr>

    <div class="form next">
        <form action="/article/<?php echo e($blog['id']); ?>/edit" method="post">
            <input type="hidden" name="token" value="<?php echo e($_SESSION['token']); ?>">
            Раздел:<br>
            <select name="cid">
                <?php $__currentLoopData = $cats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $selected = ($blog['category_id'] == $key) ? ' selected="selected"' : ''; ?>
                    <option value="<?php echo e($key); ?>"<?php echo e($selected); ?>><?php echo e($value); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select><br>

            Заголовок:<br>
            <input name="title" size="50" maxlength="50" value="<?php echo e($blog['title']); ?>"><br>
            Текст:<br>
            <textarea id="markItUp" cols="25" rows="15" name="text"><?php echo e($blog['text']); ?></textarea><br>
            Метки:<br>
            <input name="tags" size="50" maxlength="100" value="<?php echo e($blog['tags']); ?>"><br>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div><br>

    <a href="/rules">Правила</a> /
    <a href="/smiles">Смайлы</a> /
    <a href="/tags">Теги</a><br><br>
    <?php
    App::view('includes/back', ['link' => '/article/' . $blog->id, 'title' => 'Вернуться']);
    App::view('includes/back', ['link' => '/blog', 'title' => 'К блогам', 'icon' => 'fa-arrow-circle-up']);
    ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>