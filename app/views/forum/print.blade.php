@extends('layout_simple')

@section('title', $topic['title'].' - @parent')

@section('content')


	<h2><?=$topic['title']?></h2>

	<?php foreach ($posts as $key => $data): ?>

		<?=($key + 1)?>. <b><?=nickname($data['user'])?></b> (<?=date_fixed($data['time'])?>)<br />
		<?=bb_code($data['text'])?>
		<br /><br />

	<?php endforeach; ?>

	URL: <a href="<?=$config['home']?>/topic/<?=$topic['id']?>"><?=$config['home']?>/topic/<?=$topic['id']?></a>
@stop
