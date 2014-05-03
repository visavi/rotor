<?php foreach ($posts as $data): ?>
	<div class="b">

		<img src="/images/img/forums.gif" alt="image" /> <b><a href="topic.php?act=viewpost&amp;tid=<?=$data['posts_topics_id']?>&amp;id=<?=$data['posts_id']?>"><?=$data['topics_title']?></a></b>

		<?php if (is_admin()): ?>
			— <a href="active.php?act=del&amp;id=<?=$data['posts_id']?>&amp;uz=<?=$user?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>">Удалить</a>
		<?php endif; ?>

	</div>
	<div>
		<?=bb_code($data['posts_text'])?><br />

		Написал: <?=nickname($data['posts_user'])?> <small>(<?=date_fixed($data['posts_time'])?>)</small><br />

		<?php if (is_admin() || empty($config['anonymity'])): ?>
			<span class="data">(<?=$data['posts_brow']?>, <?=$data['posts_ip']?>)</span>
		<?php endif; ?>

	</div>
<?php endforeach; ?>
