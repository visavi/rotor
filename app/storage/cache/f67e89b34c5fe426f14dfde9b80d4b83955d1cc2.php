</div>
<div class="lol" id="down">
    <a href="/"><?php echo e(App::setting('copy')); ?></a><br />
    <?= show_online() ?>
    <?= show_counter() ?>
</div>
<div class="site" style="text-align:center">
    <?= perfomance() ?>
</div>

<?php $__env->startSection('scripts'); ?>
    <?= include_javascript() ?>
<?php echo $__env->yieldSection(); ?>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
