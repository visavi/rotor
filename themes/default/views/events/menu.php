<?php if ($is_admin): ?>
	<div class="form"><a href="/admin/events.php">Управление событиями</a></div>
<?php endif; ?>

<?php if ($is_user && $act != 'new'): ?>
		<img src="/images/img/open.gif" alt="image" /> <a href="index.php?act=new">Добавить событие</a><br />
<?php endif; ?>
