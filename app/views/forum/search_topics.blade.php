@extends('layout')

@section('title', 'Поиск запроса '.e($find).' - @parent')

@section('content')

	<h3>Поиск запроса <?=$find?></h3>

	<p>Найдено совпадений в темах: <?=$total?></p>

	<?php foreach ($topics as $data): ?>
		<div class="b">

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
			<b><a href="/topic/<?=$data['id']?>"><?=$data['title']?></a></b> (<?=$data['posts']?>)
		</div>
		<div>
			Страницы:
			<?php forum_navigation('/topic/'.$data['id'].'?', $config['forumpost'], $data['posts']); ?>
			Сообщение: <?=nickname($data['last_user'])?> (<?=date_fixed($data['last_time'])?>)
		</div>
	<?php endforeach; ?>

	<?php page_strnavigation('/forum/search?find=' . urlencode($find) . '&amp;type=' . $type . '&amp;where=' . $where . '&amp;period=' . $period . '&amp;section=' . $section . '&amp;', $config['forumtem'], $start, $total); ?>
@stop
