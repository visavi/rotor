@extends('layout')

@section('title', 'Поиск запроса '.e($find).' - @parent')

@section('content')

	<h3>Поиск запроса <?=$find?></h3>

	<p>Найдено совпадений в сообщениях: <?=$total?></p>

	<?php foreach ($posts as $data): ?>

		<div class="b">
			<i class="fa fa-file-text-o"></i> <b><a href="/topic/<?=$data['topic_id']?>/<?=$data['id']?>"><?=$data['title']?></a></b>
		</div>

		<div><?=App::bbCode($data['text'])?><br />
			Написал: <?=profile($data['user'])?> <?=user_online($data['user'])?> <small>(<?=date_fixed($data['time'])?>)</small><br />
		</div>

	<?php endforeach; ?>

	<?php page_strnavigation('/forum/search?find=' . urlencode($find) . '&amp;type=' . $type . '&amp;where=' . $where . '&amp;period=' . $period . '&amp;section=' . $section . '&amp;', $config['forumpost'], $start, $total); ?>
@stop
