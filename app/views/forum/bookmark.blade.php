@extends('layout')

@section('title', 'Мои закладки - @parent')

@section('content')
	<h1>Мои закладки</h1>

	@if ($total > 0)
		<form action="/forum/bookmark/delete?start=<?=$start?>" method="post">
			<input type="hidden" name="token" value="<?= $_SESSION['token'] ?>" />
			<?php foreach ($topics as $data): ?>
				<div class="b">
					<input type="checkbox" name="del[]" value="<?=$data['book_id']?>" />

					<?php
					if ($data['locked']) {
						$icon = 'fa-thumb-tack';
					} elseif ($data['closed']) {
						$icon = 'fa-lock';
					} else {
						$icon = 'fa-folder-open';
					}
					?>

					<i class="fa <?=$icon?> text-muted"></i>

					<?php $newpost = ($data['posts'] > $data['book_posts']) ? '/<span style="color:#00cc00">+'.($data['posts'] - $data['book_posts']).'</span>' : ''; ?>

					<b><a href="/topic/<?=$data['id']?>"><?=$data['title']?></a></b> (<?=$data['posts']?><?=$newpost?>)
				</div>

				<div>
					Страницы:
					<?php forum_navigation('/topic/'.$data['id'].'?', $config['forumpost'], $data['posts']); ?>
					Автор: <?=nickname($data['author'])?> / Посл.: <?=nickname($data['last_user'])?> (<?=date_fixed($data['last_time'])?>)
				</div>
			<?php endforeach; ?>

			<br />
			<input type="submit" value="Удалить выбранное" />
		</form>
	@else
		Закладок еще нет!
	@endif
@stop
