<form action="bookmark.php?act=del&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>" method="post">

	<?php foreach ($topics as $data): ?>
		<div class="b">
			<input type="checkbox" name="del[]" value="<?=$data['book_id']?>" />

			<?php if ($data['topics_locked'] == 1): ?>
				<img src="/images/img/lock.gif" alt="image" />
			<?php elseif ($data['topics_closed'] == 1): ?>
				<img src="/images/img/closed.gif" alt="image" />
			<?php else: ?>
				<img src="/images/img/forums.gif" alt="image" />
			<?php endif; ?>

			<?php $newpost = ($data['topics_posts'] > $data['book_posts']) ? '/<span style="color:#00cc00">+'.($data['topics_posts'] - $data['book_posts']).'</span>' : ''; ?>

			<b><a href="topic.php?tid=<?=$data['topics_id']?>"><?=$data['topics_title']?></a></b> (<?=$data['topics_posts']?><?=$newpost?>)
		</div>

		<div>
			Страницы:
			<?php forum_navigation('topic.php?tid='.$data['topics_id'].'&amp;', $config['forumpost'], $data['topics_posts']); ?>
			Автор: <?=nickname($data['topics_author'])?> / Посл.: <?=nickname($data['topics_last_user'])?> (<?=date_fixed($data['topics_last_time'])?>)
		</div>
	<?php endforeach; ?>

	<br />
	<input type="submit" value="Удалить выбранное" />
</form>
