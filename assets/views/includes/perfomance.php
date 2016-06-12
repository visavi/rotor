<ul class="list-inline hiding text-left">
	<li data-toggle="tooltip" title="MySQL запросы"><i class="fa fa-database"></i> <?= DB::run() -> queryCounter() ?></li>
	<li data-toggle="tooltip" title="Потребление ОЗУ"><i class="fa fa-bolt"></i> <?= formatsize(memory_get_usage()) ?></li>

	<?php if (function_exists('sys_getloadavg')): ?>
		<?php $cpu = sys_getloadavg(); ?>
		<li data-toggle="tooltip" title="Загрузка CPU"><i class="fa fa-tachometer"></i> <?= round($cpu[0], 2) ?></li>
	<?php endif; ?>

	<?php if (!empty($config['gzip'])): ?>
		<?php $compression = Compressor::result(); ?>
		<?php if (!empty($compression)): ?>
			<li data-toggle="tooltip" title="Компрессия"><i class="fa fa-compress"></i> <?= $compression ?>%</li>
		<?php endif; ?>
	<?php endif; ?>

	<li data-toggle="tooltip" title="Загрузка страницы"><i class="fa fa-rocket"></i> <?= round(microtime(1) - STARTTIME, 4) ?> сек.</li>
</ul>
