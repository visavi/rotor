@extends('layout')

@section('title', $forums['title'].' - @parent')

@section('content')

	<h1>{{ $forums['title'] }}</h1>

	<a href="/forum">Форум</a>

	@if (!empty($forums['subparent']))
		/ <a href="/forum/<?=$forums['subparent']['id']?>"><?=$forums['subparent']['title']?></a>
	@endif

	/ {{ $forums['title'] }}

	@if (is_admin())
		/ <a href="/admin/forum?act=forum&amp;fid=<?=$fid?>&amp;start=<?=$start?>">Управление</a>
	@endif

	@if (is_user() && empty($forums['closed']))
		<div class="pull-right">
			<a class="btn btn-success" href="/forum/create?fid={{ $fid }}">Создать тему</a>
		</div>
	@endif

	<hr />

	<?php if (count($forums['subforums']) > 0 && $start == 0): ?>
		<div class="act">

		<?php foreach ($forums['subforums'] as $subforum): ?>
			<div class="b"><i class="fa fa-file-text-o fa-lg text-muted"></i>
			<b><a href="/forum/<?=$subforum['id']?>"><?=$subforum['title']?></a></b> (<?=$subforum['topics']?>/<?=$subforum['posts']?>)</div>

			<?php if ($subforum['last_id'] > 0): ?>
				<div>Тема: <a href="/topic/<?=$subforum['last_id']?>/end"><?=$subforum['last_themes']?></a><br />
				Сообщение: <?=nickname($subforum['last_user'])?> (<?=date_fixed($subforum['last_time'])?>)</div>
			<?php else: ?>
				<div>Темы еще не созданы!</div>
			<?php endif; ?>
		<?php endforeach; ?>

		</div>
		<hr />
	<?php endif; ?>

	<?php if ($total > 0): ?>
		<?php foreach ($forums['topics'] as $topic): ?>
			<div class="b" id="topic_<?=$topic['id']?>">

				<?php
				if ($topic['locked']) {
					$icon = 'fa-thumb-tack';
				} elseif ($topic['closed']) {
					$icon = 'fa-lock';
				} else {
					$icon = 'fa-folder-open';
				}
				?>

				<i class="fa <?=$icon?> text-muted"></i>
				<b><a href="/topic/<?=$topic['id']?>"><?=$topic['title']?></a></b> (<?=$topic['posts']?>)
			</div>
			<div>
				Страницы: <?=forum_navigation('/topic/'.$topic['id'].'?', $config['forumpost'], $topic['posts'])?>
				Сообщение: <?=nickname($topic['last_user'])?> (<?=date_fixed($topic['last_time'])?>)
			</div>
		<?php endforeach; ?>

		<?php page_strnavigation('/forum/'.$fid.'?', $config['forumtem'], $start, $total); ?>

	<?php elseif ($forums['closed']): ?>
		<?=show_error('В данном разделе запрещено создавать темы!')?>
	<?php else: ?>
		<?=show_error('Тем еще нет, будь первым!')?>
	<?php endif; ?>


	<a href="/rules">Правила</a> /
	<a href="/forum/top/themes">Топ тем</a> /
	<a href="/forum/search?fid=<?=$fid?>">Поиск</a><br />
@stop
