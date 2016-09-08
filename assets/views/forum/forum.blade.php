@extends('layout')

@section('title', $forums['forums_title'].' - @parent')

@section('content')

	<h1>{{ $forums['forums_title'] }}</h1>

	<a href="/forum">Форум</a>

	@if (!empty($forums['subparent']))
		/ <a href="/forum/<?=$forums['subparent']['forums_id']?>"><?=$forums['subparent']['forums_title']?></a>
	@endif

	/ {{ $forums['forums_title'] }}

	@if (is_admin())
		/ <a href="/admin/forum.php?act=forum&amp;fid=<?=$fid?>&amp;start=<?=$start?>">Управление</a>
	@endif

	@if (is_user() && empty($forums['forums_closed']))
		<div class="pull-right">
			<a class="btn btn-success" href="/forum/create?fid={{ $fid }}">Создать тему</a>
		</div>
	@endif

	<hr />

	<?php if (count($forums['subforums']) > 0 && $start == 0): ?>
		<div class="act">

		<?php foreach ($forums['subforums'] as $subforum): ?>
			<div class="b"><i class="fa fa-file-text-o fa-lg text-muted"></i>
			<b><a href="/forum/<?=$subforum['forums_id']?>"><?=$subforum['forums_title']?></a></b> (<?=$subforum['forums_topics']?>/<?=$subforum['forums_posts']?>)</div>

			<?php if ($subforum['forums_last_id'] > 0): ?>
				<div>Тема: <a href="/topic/<?=$subforum['forums_last_id']?>?act=end"><?=$subforum['forums_last_themes']?></a><br />
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
					$icon = 'fa-thumb-tack';
				} elseif ($topic['topics_closed']) {
					$icon = 'fa-lock';
				} else {
					$icon = 'fa-folder-open';
				}
				?>

				<i class="fa <?=$icon?> text-muted"></i>
				<b><a href="/topic/<?=$topic['topics_id']?>"><?=$topic['topics_title']?></a></b> (<?=$topic['topics_posts']?>)
			</div>
			<div>
				Страницы: <?=forum_navigation('/topic/'.$topic['topics_id'].'?', $config['forumpost'], $topic['topics_posts'])?>
				Сообщение: <?=nickname($topic['topics_last_user'])?> (<?=date_fixed($topic['topics_last_time'])?>)
			</div>
		<?php endforeach; ?>

		<?php page_strnavigation('/forum/'.$fid.'?', $config['forumtem'], $start, $total); ?>

	<?php elseif ($forums['forums_closed']): ?>
		<?=show_error('В данном разделе запрещено создавать темы!')?>
	<?php else: ?>
		<?=show_error('Тем еще нет, будь первым!')?>
	<?php endif; ?>


	<a href="/pages/rules.php">Правила</a> /
	<a href="top.php?act=themes">Топ тем</a> /
	<a href="search.php?fid=<?=$fid?>">Поиск</a><br />
@stop
