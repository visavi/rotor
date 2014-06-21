<?php if (is_user()): ?>
	Мои: <a href="active.php?act=themes">темы</a>, <a href="active.php?act=posts">сообщения</a>, <a href="bookmark.php">закладки</a> /
<?php endif; ?>

Новые: <a href="new.php?act=themes">темы</a>, <a href="new.php?act=posts">сообщения</a><hr />

<?php foreach($forums[0] as $key => $data): ?>
	<div class="b">
		<img src="/images/img/forums.gif" alt="image" />
		<b><a href="forum.php?fid=<?=$data['forums_id']?>"><?=$data['forums_title']?></a></b> (<?=$data['forums_topics']?>/<?=$data['forums_posts']?>)

	<?php if (!empty($data['forums_desc'])): ?>
		<br /><small><?=$data['forums_desc']?></small>
	<?php endif; ?>

	</div>

	<div>
	<?php if (isset($forums[$key])): ?>
		<?php foreach($forums[$key] as $datasub): ?>
			<img src="/images/img/topics-small.gif" alt="image" /> <b><a href="forum.php?fid=<?=$datasub['forums_id']?>"><?=$datasub['forums_title']?></a></b> (<?=$datasub['forums_topics']?>/<?=$datasub['forums_posts']?>)<br />
		<?php endforeach; ?>
	<?php endif; ?>

	<?php if ($data['forums_last_id'] > 0): ?>
		Тема: <a href="topic.php?act=end&amp;tid=<?=$data['forums_last_id']?>"><?=$data['forums_last_themes']?></a><br />
		Сообщение: <?=nickname($data['forums_last_user'])?> (<?=date_fixed($data['forums_last_time'])?>)
	<?php else: ?>
		Темы еще не созданы!
	<?php endif; ?>

	</div>
<?php endforeach; ?>

<br /><a href="/pages/rules.php">Правила</a> / <a href="top.php?act=themes">Топ тем</a> / <a href="search.php">Поиск</a><br />
