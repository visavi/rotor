<nav>
    <ul class="pagination pagination-sm">
        <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(isset($page['separator'])): ?>
                <li class="page-item disabled"><span class="page-link"><?php echo e($page['name']); ?></span></li>
            <?php elseif(isset($page['current'])): ?>
                <li class="page-item active"><span class="page-link"><?php echo e($page['name']); ?></span></li>
            <?php else: ?>
                <li class="page-item"><a class="page-link" href="?page=<?php echo e($page['page']); ?><?php echo e($request); ?>"><?php echo e($page['name']); ?></a></li>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</nav>
