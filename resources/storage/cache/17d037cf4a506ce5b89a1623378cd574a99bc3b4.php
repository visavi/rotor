<div>Страницы:
	<?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
		<?php if(isset($page['separator'])): ?>
			<span><?php echo e($page['name']); ?></span>
		<?php else: ?>
			<a href="<?php echo e($link); ?>?page=<?php echo e($page['page']); ?>"><?php echo e($page['name']); ?></a>
		<?php endif; ?>
	<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
