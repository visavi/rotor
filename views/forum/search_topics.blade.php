@extends('layout')

@section('title', 'Поиск запроса '.e($find).' - @parent')

@section('content')

	<h3>Поиск запроса <?=$find?></h3>

	<p>Найдено совпадений в темах: <?=$total?></p>

	<?php foreach ($topics as $data): ?>
		<div class="b">

			<?php
			if ($data['topics_locked']) {
				$icon = 'fa-thumb-tack';
			} elseif ($data['topics_closed']) {
				$icon = 'fa-lock';
			} else {
				$icon = 'fa-folder-open';
			}
			?>

			<i class="fa <?=$icon?> text-muted"></i>
			<b><a href="/topic/<?=$data['topics_id']?>"><?=$data['topics_title']?></a></b> (<?=$data['topics_posts']?>)
		</div>
		<div>
			Страницы:
			<?php forum_navigation('/topic/'.$data['topics_id'].'?', $config['forumpost'], $data['topics_posts']); ?>
			Сообщение: <?=nickname($data['topics_last_user'])?> (<?=date_fixed($data['topics_last_time'])?>)
		</div>
	<?php endforeach; ?>

	<?php page_strnavigation('/forum/search?find=' . urlencode($find) . '&amp;type=' . $type . '&amp;where=' . $where . '&amp;period=' . $period . '&amp;section=' . $section . '&amp;', $config['forumtem'], $start, $total); ?>
@stop
