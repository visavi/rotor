<ul class="list-inline hiding text-left">
    <li class="list-inline-item" data-toggle="tooltip" title="MySQL запросы"><i class="fa fa-database"></i> <?php echo e(count($queries)); ?></li>
    <li class="list-inline-item" data-toggle="tooltip" title="Потребление ОЗУ"><i class="fa fa-bolt"></i> <?php echo e(formatsize(memory_get_usage())); ?></li>

    <?php if (function_exists('sys_getloadavg')): ?>
        <?php $cpu = sys_getloadavg(); ?>
        <li class="list-inline-item" data-toggle="tooltip" title="Загрузка CPU"><i class="fa fa-tachometer"></i> <?php echo e(round($cpu[0], 2)); ?></li>
    <?php endif; ?>

    <li class="list-inline-item" data-toggle="tooltip" title="Загрузка страницы"><i class="fa fa-rocket"></i> <?php echo e(round(microtime(1) - STARTTIME, 4)); ?> сек.</li>
</ul>

<?php if($queries): ?>
<pre class="text-left prettyprint linenums">
<?php $__currentLoopData = $queries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=> $query): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php echo e($query['query']); ?> (<?php echo e(number_format($query['time'] /1000, 5)); ?> сек.)
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</pre>
<?php endif; ?>
