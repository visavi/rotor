<?php if(isset($_SESSION['flash'])): ?>

    <?php $__currentLoopData = $_SESSION['flash']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $messages): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(is_array($messages)): ?>
            <?php $messages = implode('</div><div>', $messages); ?>
        <?php endif; ?>
        <div class="alert alert-<?php echo e($status); ?> alert-block">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <div><?php echo $messages; ?></div>
        </div>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
