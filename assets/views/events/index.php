<?php foreach($events as $data): ?>
	<div class="b">
		<?=$data['event_closed'] == 0 ? '<img src="/images/img/document_plus.gif" alt="image" /> ' : '<img src="/images/img/document_minus.gif" alt="image" />'; ?>

		<b><a href="/events?act=read&amp;id=<?=$data['event_id']?>"><?=$data['event_title']?></a></b><small> (<?=date_fixed($data['event_time'])?>)</small>
	</div>

	<?php if (!empty($data['event_image'])): ?>
		<div class="img">
			<a href="/upload/events/<?=$data['event_image']?>"><?=resize_image('upload/events/', $data['event_image'], 75, array('alt' => $data['event_title']))?></a>
		</div>
	<?php endif; ?>

	<?php if (App::getUsername() == $data['event_author'] && $data['event_time'] + 3600 > SITETIME): ?>
		<div class="right">
			<a href="/events?act=editevent&amp;id=<?=$data['event_id']?>">Редактировать</a>
		</div>
	<?php endif; ?>

	<?php if(stristr($data['event_text'], '[cut]')) {
		$data['event_text'] = current(explode('[cut]', $data['event_text'])).' <a href="/events?act=read&amp;id='.$data['event_id'].'">Читать далее &raquo;</a>';
	} ?>

	<div><?=bb_code($data['event_text'])?></div>

	<div style="clear:both;">Добавлено: <?=profile($data['event_author'])?><br />
		<a href="/events?act=comments&amp;id=<?=$data['event_id']?>">Комментарии</a> (<?=$data['event_comments']?>)
		<a href="/events?act=end&amp;id=<?=$data['event_id']?>">&raquo;</a>
	</div>

<?php endforeach; ?>
