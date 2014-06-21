<a href="index.php">Форум</a> /

<?php if (!empty($forums['subparent'])): ?>
	<a href="forum.php?fid=<?=$forums['subparent']['forums_id']?>"><?=$forums['subparent']['forums_title']?></a> /
<?php endif; ?>

<a href="forum.php?fid=<?=$fid?>&amp;start=<?=$start?>&amp;rand=<?=mt_rand(1000, 9999)?>">Обновить</a>

<?php if (empty($forums['forums_closed'])): ?>
	 / <a href="forum.php?act=addtheme&amp;fid=<?=$fid?>">Создать тему</a>
<?php endif; ?>

<br /><br />

<b><img src="/images/img/themes.gif" alt="image" /> <?=$forums['forums_title']?></b>

<?php if (is_admin()): ?>
	(<a href="/admin/forum.php?act=forum&amp;fid=<?=$fid?>&amp;start=<?=$start?>">Управление</a>)
<?php endif; ?>

<hr />

<?php if (count($forums['subforums']) > 0 && $start == 0): ?>
	<div class="act">

	<?php foreach ($forums['subforums'] as $subforum): ?>
		<div class="b"><img src="/images/img/forums.gif" alt="image" />
		<b><a href="forum.php?fid=<?=$subforum['forums_id']?>"><?=$subforum['forums_title']?></a></b> (<?=$subforum['forums_topics']?>/<?=$subforum['forums_posts']?>)</div>

		<?php if ($subforum['forums_last_id'] > 0): ?>
			<div>Тема: <a href="topic.php?act=end&amp;tid=<?=$subforum['forums_last_id']?>"><?=$subforum['forums_last_themes']?></a><br />
			Сообщение: <?=nickname($subforum['forums_last_user'])?> (<?=date_fixed($subforum['forums_last_time'])?>)</div>
		<?php else: ?>
			<div>Темы еще не созданы!</div>
		<?php endif; ?>
	<?php endforeach; ?>

	</div>
	<hr />
<?php endif; ?>


<?php if ($total > 0): ?>
	<?php foreach ($forums['topics'] as $topic): ?>
		<div class="b" id="topic_<?=$topic['topics_id']?>">

			<?php
			if ($topic['topics_locked']) {
				$icon = 'lock.gif';
			} elseif ($topic['topics_closed']) {
				$icon = 'closed.gif';
			} else {
				$icon = 'topics.gif';
			}
			?>

			<img src="/images/img/<?=$icon?>" alt="image" />
			<b><a href="topic.php?tid=<?=$topic['topics_id']?>"><?=$topic['topics_title']?></a></b> (<?=$topic['topics_posts']?>)
		</div>
		<div>
			Страницы: <?=forum_navigation('topic.php?tid='.$topic['topics_id'].'&amp;', $config['forumpost'], $topic['topics_posts'])?>
			Сообщение: <?=nickname($topic['topics_last_user'])?> (<?=date_fixed($topic['topics_last_time'])?>)
		</div>
	<?php endforeach; ?>

	<?php page_strnavigation('forum.php?fid='.$fid.'&amp;', $config['forumtem'], $start, $total); ?>

<?php elseif ($forums['forums_closed']): ?>
	<?=show_error('В данном разделе запрещено создавать темы!')?>
<?php else: ?>
	<?=show_error('Тем еще нет, будь первым!')?>
<?php endif; ?>


<a href="forum.php?act=addtheme&amp;fid=<?=$fid?>">Создать тему</a> /
<a href="/pages/rules.php">Правила</a> /
<a href="top.php?act=themes">Топ тем</a> /
<a href="search.php?fid=<?=$fid?>">Поиск</a><br />

