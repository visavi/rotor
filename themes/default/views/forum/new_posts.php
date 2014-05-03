<?php foreach ($posts as $data): ?>
	<div class="b">
		<img src="/images/img/forums.gif" alt="image" /> <b><a href="topic.php?act=viewpost&amp;tid=<?=$data['posts_topics_id']?>&amp;id=<?=$data['posts_id']?>"><?=$data['topics_title']?></a></b>
		(<?=$data['topics_posts']?>)
	</div>
	<div>
		<?=bb_code($data['posts_text'])?><br />

		Написал: <?=nickname($data['posts_user'])?> <?=user_online($data['posts_user'])?> <small>(<?=date_fixed($data['posts_time'])?>)</small><br />

		<?php if (is_admin() || empty($config['anonymity'])): ?>
			<span class="data">(<?=$data['posts_brow']?>, <?=$data['posts_ip']?>)</span>
		<?php endif; ?>

	</div>
<?php endforeach; ?>

