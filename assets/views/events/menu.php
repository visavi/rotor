<?php if ($is_admin): ?>
	<div class="form"><a href="/admin/events">Управление событиями</a></div>
<?php endif; ?>

<?php if ($is_user && $act != 'new'): ?>
		<img src="/images/img/open.gif" alt="image" /> <a href="/events?act=new">Добавить событие</a><br />
<?php endif; ?>
