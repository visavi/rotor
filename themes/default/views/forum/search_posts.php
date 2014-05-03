Поиск запроса <b>&quot;<?=$find?>&quot;</b> в сообщениях<br />

<h3>Найдено совпадений: <?=$total?></h3>

<?php foreach ($posts as $data): ?>

	<div class="b">
		<img src="/images/img/forums.gif" alt="image" /> <b><a href="topic.php?act=viewpost&amp;tid=<?=$data['posts_topics_id']?>&amp;id=<?=$data['posts_id']?>#post_<?=$data['posts_id']?>"><?=$data['topics_title']?></a></b>
	</div>

	<div><?=bb_code($data['posts_text'])?><br />
		Написал: <?=profile($data['posts_user'])?> <?=user_online($data['posts_user'])?> <small>(<?=date_fixed($data['posts_time'])?>)</small><br />
	</div>

<?php endforeach; ?>
