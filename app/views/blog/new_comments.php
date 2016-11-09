<?php foreach ($comments as $data): ?>

	<div class="b">
		<i class="fa fa-comment"></i> <b><a href="/blog/blog?act=comments&amp;id=<?=$data['relate_id']?>"><?=$data['title']?></a></b> (<?=$data['comments']?>)
	</div>

	<div>
		<?=bb_code($data['text'])?><br />
		Написал: <?=profile($data['user'])?> <small>(<?=date_fixed($data['time'])?>)</small><br />

		<?php if (is_admin() || empty($config['anonymity'])): ?>
			<span class="data">(<?=$data['brow']?>, <?=$data['ip']?>)</span>
		<?php endif; ?>
	</div>

<?php endforeach; ?>
