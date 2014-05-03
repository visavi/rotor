<?php foreach ($topics as $data): ?>
	<div class="b">

		<?php if ($data['topics_locked'] == 1): ?>
			<img src="/images/img/lock.gif" alt="image" />
		<?php elseif ($data['topics_closed'] == 1): ?>
			<img src="/images/img/closed.gif" alt="image" />
		<?php else: ?>
			<img src="/images/img/forums.gif" alt="image" />
		<?php endif; ?>

		<b><a href="topic.php?tid=<?=$data['topics_id']?>"><?=$data['topics_title']?></a></b> (<?=$data['topics_posts']?>)
	</div>
	<div>
		Страницы:
		<?php forum_navigation('topic.php?tid='.$data['topics_id'].'&amp;', $config['forumpost'], $data['topics_posts']); ?>
		Автор: <?=$data['topics_author']?><br />
		Сообщение: <?=$data['topics_last_user']?> (<?=date_fixed($data['topics_last_time'])?>)
	</div>
<?php endforeach; ?>
