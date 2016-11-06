@extends('layout')

@section('title', 'Поиск запроса '.e($find).' - @parent')

@section('content')

	<h3>Поиск запроса <?=$find?></h3>

	<p>Найдено совпадений в сообщениях: <?=$total?></p>

	<?php foreach ($posts as $data): ?>

		<div class="b">
			<i class="fa fa-file-text-o"></i> <b><a href="/topic/<?=$data['posts_topics_id']?>/<?=$data['posts_id']?>"><?=$data['title']?></a></b>
		</div>

		<div><?=bb_code($data['posts_text'])?><br />
			Написал: <?=profile($data['posts_user'])?> <?=user_online($data['posts_user'])?> <small>(<?=date_fixed($data['posts_time'])?>)</small><br />
		</div>

	<?php endforeach; ?>

	<?php page_strnavigation('/forum/search?find=' . urlencode($find) . '&amp;type=' . $type . '&amp;where=' . $where . '&amp;period=' . $period . '&amp;section=' . $section . '&amp;', $config['forumpost'], $start, $total); ?>
@stop
