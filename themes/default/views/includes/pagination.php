<?php $pagination['separator'] = '...'; ?>
<?php $pagination['current'] = '<span class="navcurrent">{page}</span>'; ?>
<?php $pagination['link'] = '<a href="{url}?start={start}">{page}</a>'; ?>
<?php $pagination['next'] = '<a href="{url}?start={start}" title="Вперед">&raquo;</a>'; ?>
<?php $pagination['prev'] = '<a href="{url}?start={start}" title="Назад">&laquo;</a>'; ?>

<div class="nav">
	<?php pagination($params, $pagination); ?>
</div>

