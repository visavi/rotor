<div class="hide" style="text-align: left;"><b>Статистика производительности</b><br />

MySQL запросов: <?= DB::run() -> queryCounter() ?><br />

Потребление ОЗУ: <?= formatsize(memory_get_usage()) ?><br />

<?php if (function_exists('sys_getloadavg')): ?>
	<?php $cpu = sys_getloadavg(); ?>
	Загрузка CPU: <?= round($cpu[0], 2) ?><br />
<?php endif; ?>

<?php if (!empty($config['gzip'])): ?>
	<?php $compression = Compressor::result(); ?>
	<?php if (!empty($compression)) {?> Компрессия: <?= $compression ?>%<br /> <?php }?>
<?php endif; ?>

Загрузка страницы: <?= round(microtime(1) - STARTTIME, 4) ?> сек.<br />
</div>

