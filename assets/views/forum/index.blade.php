@extends('layout')

@section('title', 'Гостевая книга - @parent')

@section('content')

	<?php if (is_user()): ?>
		Мои: <a href="/forum/active/themes">темы</a>, <a href="/forum/active/posts">сообщения</a>, <a href="/forum/bookmark">закладки</a> /
	<?php endif; ?>

	Новые: <a href="/forum/new/themes">темы</a>, <a href="/forum/new/posts">сообщения</a><hr />

	<?php foreach($forums[0] as $key => $data): ?>
		<div class="b">
			<i class="fa fa-file-text-o fa-lg text-muted"></i>
			<b><a href="/forum/<?=$data['forums_id']?>"><?=$data['forums_title']?></a></b> (<?=$data['forums_topics']?>/<?=$data['forums_posts']?>)

		<?php if (!empty($data['forums_desc'])): ?>
			<br /><small><?=$data['forums_desc']?></small>
		<?php endif; ?>

		</div>

		<div>
		<?php if (isset($forums[$key])): ?>
			<?php foreach($forums[$key] as $datasub): ?>
				<i class="fa fa-files-o text-muted"></i> <b><a href="forum.php?fid=<?=$datasub['forums_id']?>"><?=$datasub['forums_title']?></a></b> (<?=$datasub['forums_topics']?>/<?=$datasub['forums_posts']?>)<br />
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

	<br /><a href="/rules">Правила</a> / <a href="/forum/top/themes">Топ тем</a> / <a href="/forum/search">Поиск</a><br />
@stop
