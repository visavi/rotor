@extends('layout_simple')

@section('title', $topic['topics_title'].' - @parent')

@section('content')


	<h2><?=$topic['topics_title']?></h2>

	<?php foreach ($posts as $key => $data): ?>

		<?=($key + 1)?>. <b><?=nickname($data['posts_user'])?></b> (<?=date_fixed($data['posts_time'])?>)<br />
		<?=bb_code($data['posts_text'])?>
		<br /><br />

	<?php endforeach; ?>

	URL: <a href="<?=$config['home']?>/topic/<?=$topic['topics_id']?>"><?=$config['home']?>/topic/<?=$topic['topics_id']?></a>
@stop
